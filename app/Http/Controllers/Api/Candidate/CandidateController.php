<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Constant\CandidateType;
use App\Constant\TestType;
use App\Db\Entity\Candidate;
use App\Db\Entity\CandidateFile;
use App\Db\Entity\Event;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\TariffUser;
use App\Db\Entity\UserEvent;
use App\Db\Service\CandidateDao;
use App\Db\Service\CandidateFileDao;
use App\Db\Service\EventDao;
use App\Db\Service\TariffUserDao;
use App\Db\Service\TestResultDao;
use App\Db\Service\UserEventDao;
use App\Db\Service\UserTariffCandidateDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Candidate\AddRequest;
use App\Http\Requests\Candidate\ChangeTypeRequest;
use App\Http\Requests\Candidate\DeleteDocumentRequest;
use App\Http\Requests\Candidate\ExportCandidateDataRequest;
use App\Http\Requests\Candidate\ExportResultRequest;
use App\Http\Requests\Candidate\ExportTestResultRequest;
use App\Http\Requests\Candidate\GetListRequest;
use App\Http\Requests\Candidate\GetTestResultRequest;
use App\Http\Requests\Candidate\InterviewInvitationRequest;
use App\Http\Requests\Candidate\UpdateNotesRequest;
use App\Http\Requests\Candidate\UpdateRequest;
use App\Http\Requests\Candidate\UploadDocumentRequest;
use App\Http\Requests\Candidate\UploadImageRequest;
use App\Http\Requests\IdRequest;
use App\Http\Resources\PaginationResource;
use App\Notifications\Candidate\InterviewAgreeNotification;
use App\Notifications\Candidate\InterviewCancelNotification;
use App\Notifications\Candidate\InterviewInvitationNotification;
use App\Notifications\User\CandidateAgreeInterviewNotification;
use App\Notifications\User\CandidateCancelInterviewNotification;
use App\Service\AuthService;
use App\Service\StorageService;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CandidateController extends BaseController
{
    protected CandidateDao $candidateService;
    protected TariffUserDao $tariffUserDao;
    protected UserTariffCandidateDao $userTariffCandidateDao;
    protected EventDao $eventService;
    protected StorageService $storageService;
    protected CandidateFileDao $candidateFileDao;
    protected TestResultDao $testResultService;
    protected UserEventDao $userEventDao;

    public function __construct(
        CandidateDao $candidateService,
        EventDao $eventService,
        TestResultDao $testResultService,
        AuthService $authService,
        TariffUserDao $tariffUserDao,
        UserTariffCandidateDao $userTariffCandidateDao,
        StorageService $storageService,
        CandidateFileDao $candidateFileDao,
        UserEventDao $userEventDao)
    {
        parent::__construct($authService);
        $this->candidateService = $candidateService;
        $this->eventService = $eventService;
        $this->testResultService = $testResultService;
        $this->tariffUserDao = $tariffUserDao;
        $this->storageService = $storageService;
        $this->candidateFileDao = $candidateFileDao;
        $this->userTariffCandidateDao = $userTariffCandidateDao;
        $this->userEventDao = $userEventDao;
    }

    public function index(GetListRequest $request)
    {
        $candidatesQuery = $this->candidateService->searchQuery(
            $this->user(),
            CandidateType::$Candidate,
            $request->searchString,
            $request->careerStartYearFrom,
            $request->careerStartYearTo,
            $request->ageFromTimestamp,
            $request->ageToTimestamp,
            $request->isOnlyFavoriteCandidates,
            $request->orderType,
            $request->tags,
            $request->staffLevel,
            $request->staffSpecialization,
        );
        return $this->json(
            new PaginationResource($candidatesQuery, $request->page)
        );
    }

    public function actionGetEmployeeList(GetListRequest $request)
    {
        $employeeQuery = $this->candidateService->searchQuery(
            $this->user(),
            CandidateType::$Employee,
            $request->searchString,
            $request->careerStartYearFrom,
            $request->careerStartYearTo,
            $request->ageFromTimestamp,
            $request->ageToTimestamp,
            $request->isOnlyFavoriteCandidates,
            $request->orderType,
            $request->tags,
            $request->staffLevel,
            $request->staffSpecialization,
        );
        return $this->json(
            new PaginationResource($employeeQuery, $request->page)
        );
    }

    public function actionGetOne(int $id)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $id)->first();
        if ($this->user()->cannot('view', $candidate))
        {
            return $this->responsePermissionsDenied();
        }
        return $this->json(
            $this->candidateService->firstWithData($id)
        );
    }

    public function actionGetTestResultsByCandidate(GetTestResultRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->id)->first();
        if ($this->user()->cannot('view', $candidate))
        {
            return $this->responsePermissionsDenied();
        }

        $testResultsQuery = $this->candidateService->getCandidateResults(
            $candidate,
            TestType::$Test,
            $request->orderType
        );

        return $this->json(
            new PaginationResource($testResultsQuery, $request->getPage())
        );
    }

    public function actionGetQuestionnaireResultsByCandidate(GetTestResultRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->id)->first();
        if ($this->user()->cannot('view', $candidate))
        {
            return $this->responsePermissionsDenied();
        }

        $questionnaireResultsQuery = $this->candidateService->getCandidateResults(
            $candidate,
            TestType::$Questionnaire,
            $request->orderType
        );

        return $this->json(
            new PaginationResource($questionnaireResultsQuery, $request->getPage())
        );
    }

    public function actionExportToPDFResults(ExportResultRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->candidateId)->first();
        if ($this->user()->cannot('view', $candidate))
        {
            return $this->responsePermissionsDenied();
        }
        /** @var Candidate $candidate */
        $candidate = $this->candidateService->firstWithData($request->candidateId);
        $result = $this->testResultService->getOneById($request->resultId);

        if ($result->projectCandidate->candidate_id != $candidate->id)
        {
            return $this->jsonError(trans('candidates.resultError'));
        }

        $pdf = PDF::loadView('pdf.results', [
            'candidate' => $candidate,
            'resultAnswers' => $result->answers,
            'testResult' => $result
        ]);
        $folder = 'candidate/documents';
        $name = 'results_';

        if ($result->pdf_url)
        {
            return $result->pdf_url;
        }
        else
        {
            $path = $this->storageService->saveExportedPDF($pdf, $folder, $name);
            $result->pdf_url = $path;
            $result->save();

            return $path;
        }
    }

    public function actionExportToPDFCandidateData(ExportCandidateDataRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->candidateId)->first();
        if ($this->user()->cannot('view', $candidate))
        {
            return $this->responsePermissionsDenied();
        }
        /** @var Candidate $candidate */
        $candidate = $this->candidateService->firstWithData($request->candidateId);

        $pdf = PDF::loadView('pdf.candidateData', [
            'candidate' => $candidate,
            'birthDay' => $candidate->birth_date->day,
            'birthMonth',
            'birthYear',
        ]);
        $folder = 'candidate/documents';
        $name = 'candidateData_';

        if ($candidate->pdf_data_url)
        {
            return $candidate->pdf_data_url;
        }
        else
        {
            $path = $this->storageService->saveExportedPDF($pdf, $folder, $name);
            $candidate->pdf_data_url = $path;
            $candidate->save();

            return $path;
        }
    }

    public function addNew(AddRequest $request)
    {
        /** @var CandidateFile $candidateImage */
        $candidateImage = CandidateFile::query()->where('id', $request->imageId)->first();
        if ($this->user()->cannot('create', Candidate::class)
            || ($candidateImage &&
                ($this->user()->cannot('view', $candidateImage) || $candidateImage->type != CandidateFile::TYPE_IMAGE)))
        {
            return $this->responsePermissionsDenied();
        }

        $tariffUser = $this->checkingTariffRestrictions();
        if (!$tariffUser instanceof TariffUser)
        {
            return $tariffUser;
        }

        $candidate = $this->candidateService->addNew($request, $candidateImage);

        if (!$candidate)
        {
            return $this->jsonError();
        }

        if ($tariffUser)
        {
            $this->userTariffCandidateDao->createUserTariffCandidate($this->user(), $candidate, $tariffUser->tariff);
        }

        $this->eventService->createEvent(Event::EVENT_TYPE_CANDIDATE, Event::EVENT_SUB_TYPE_CREATE, $this->user()->id, $candidate->id);
        return $this->json(
            $this->candidateService->firstWithData($candidate->id)
        );
    }

    public function actionUpdate(UpdateRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->id)->first();
        /** @var CandidateFile $candidateImage */
        $candidateImage = CandidateFile::query()->where('id', $request->imageId)->first();
        if (!$candidate || $this->user()->cannot('update', $candidate)
            || ($candidateImage &&
                ($this->user()->cannot('view', $candidateImage) || $candidateImage->type != CandidateFile::TYPE_IMAGE)))
        {
            return $this->responsePermissionsDenied();
        }

        $this->candidateService->update($request, $candidate, $candidateImage);

        if ($candidate->type->getValue() == CandidateType::$Candidate)
        {
            $this->eventService->createEvent(
                Event::EVENT_TYPE_CANDIDATE,
                Event::EVENT_SUB_TYPE_UPDATE,
                $this->user()->id, $candidate->id
            );
        }
        else
        {
            $this->eventService->createEvent(
                Event::EVENT_TYPE_EMPLOYEE,
                Event::EVENT_SUB_TYPE_UPDATE,
                $this->user()->id, $candidate->id
            );
        }
        return $this->json(
            $this->candidateService->firstWithData($candidate->id)
        );
    }

    public function actionChangeType(ChangeTypeRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->id)->first();
        if (!$candidate || $this->user()->cannot('update', $candidate))
        {
            return $this->responsePermissionsDenied();
        }

        $candidate->type = CandidateType::getEnumObject($request->type);

        if ($candidate->type->getValue() == CandidateType::$Employee)
        {
            $candidate->fired_at = null;
            $candidate->hired_at = Carbon::now()->timestamp;
        }
        else
        {
            $candidate->fired_at = Carbon::now()->timestamp;
        }

        $candidate->save();

        $this->eventService->createEvent(
            Event::EVENT_TYPE_CHANGE_CANDIDATE_TYPE,
            Event::EVENT_SUB_TYPE_CANDIDATE,
            $this->user()->id, $candidate->id
        );

        return $this->json(
            $this->candidateService->firstWithData($candidate->id)
        );
    }

    public function actionUpdateNotes(UpdateNotesRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->id)->first();
        if (!$candidate || $this->user()->cannot('update', $candidate))
        {
            return $this->responsePermissionsDenied();
        }

        $this->candidateService->updateNote($request, $candidate);
        if ($candidate->type->getValue() == CandidateType::$Candidate)
        {
            $this->eventService->createEvent(
                Event::EVENT_TYPE_CANDIDATE,
                Event::EVENT_SUB_TYPE_UPDATE,
                $this->user()->id,
                $candidate->id
            );
        }
        else
        {
            $this->eventService->createEvent(
                Event::EVENT_TYPE_EMPLOYEE,
                Event::EVENT_SUB_TYPE_UPDATE,
                $this->user()->id,
                $candidate->id
            );
        }
        return $this->json(
            $this->candidateService->firstWithData($candidate->id)
        );
    }

    public function actionSendInterviewInvitation(InterviewInvitationRequest $request)
    {
        $candidate = $this->getIfAccessible($request->candidateId);
        if (!$candidate)
        {
            return $this->responsePermissionsDenied();
        }

        if ($candidate->cantSendNotification)
        {
            return $this->jsonError(trans('candidates.timeLimitOnInvitation'));
        }

        try {
            $userProfile = $this->user()->profile;

            if (!$userProfile || !$userProfile->address || !$userProfile->city
                || !$userProfile->apartment_number || !$userProfile->house_number)
            {
                return $this->jsonError(trans('candidates.noDataUserProfile'));
            }

            $createUserEvent = $this->userEventDao->createInterviewInvitationEvent(
                $request,
                $candidate
            );

            /** @var LetterTemplate $template */
            $template = LetterTemplate::query()
                ->where([
                    'user_id' => $this->user()->id,
                    'type_id' => LetterTemplateType::InterviewInvitation,
                    'is_active' => true
                ])->first();

            $candidate->notify(
                new InterviewInvitationNotification($createUserEvent, $userProfile, $template)
            );

            $candidate->is_invitation_sent = true;
            $candidate->last_notification_at = Carbon::now()->timestamp;
            $candidate->is_last_send_error = false;
            $candidate->last_send_error_at = null;
            $candidate->save();
        }
        catch (\Exception $e)
        {
            $candidate->is_last_send_error = true;
            $candidate->last_send_error_at = Carbon::now()->timestamp;
            $candidate->save();
            Log::error($e->getMessage());

            return $this->jsonError(trans('candidates.notificationError'));
        }

        $this->eventService->createEvent(
            Event::EVENT_TYPE_INTERVIEW_INVITATION,
            Event::EVENT_SUB_TYPE_SENT,
            $this->user()->id,
            $candidate->id
        );

        return $this->json();
    }

    public function candidateAgreeToAnInterview(string $token)
    {
        $userEvent = $this->userEventDao->getEventByAgreedToken($token);

        if ($userEvent)
        {
            $userEvent->status = UserEvent::USER_EVENT_STATUS_APPROVED;
            $userEvent->candidate->notify(new InterviewAgreeNotification($userEvent));
            $userEvent->user->notify(new CandidateAgreeInterviewNotification($userEvent));
            $userEvent->save();

            /** @var Event $event */
            $event = $this->eventService->createEvent(
                Event::EVENT_TYPE_ACCEPTED_INVITATION,
                Event::EVENT_SUB_TYPE_CANDIDATE,
                $userEvent->user_id,
                $userEvent->candidate_id
            );
            $event->is_popup_notification = true;
            $event->save();

            return $this->json(trans('candidates.positiveResponse'));
        }
        else
        {
            return $this->jsonError(trans('candidates.invalidToken'));
        }
    }

    public function candidateCancelToAnInterview(string $token)
    {
        $userEvent = $this->userEventDao->getEventByCancelToken($token);

        if ($userEvent)
        {
            $userEvent->status = UserEvent::USER_EVENT_STATUS_CANCELED;
            $userEvent->candidate->notify(new InterviewCancelNotification($userEvent));
            $userEvent->user->notify(new CandidateCancelInterviewNotification($userEvent));
            $userEvent->save();

            /** @var Event $event */
            $event = $this->eventService->createEvent(
                Event::EVENT_TYPE_CANCEL_INVITATION,
                Event::EVENT_SUB_TYPE_CANDIDATE,
                $userEvent->user_id,
                $userEvent->candidate_id
            );
            $event->is_popup_notification = true;
            $event->save();

            return $this->json(trans('candidates.positiveResponse'));
        }
        else
        {
            return $this->jsonError(trans('candidates.invalidToken'));
        }
    }

    public function actionDelete(IdRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->id)->first();
        if (!$candidate || $this->user()->cannot('delete', $candidate))
        {
            return $this->responsePermissionsDenied();
        }

        if ($candidate->type->getValue() == CandidateType::$Candidate)
        {
            $this->eventService->createEvent(
                Event::EVENT_TYPE_CANDIDATE,
                Event::EVENT_SUB_TYPE_DELETE,
                $this->user()->id,
                $candidate->id
            );
        }
        else
        {
            $this->eventService->createEvent(
                Event::EVENT_TYPE_EMPLOYEE,
                Event::EVENT_SUB_TYPE_DELETE,
                $this->user()->id,
                $candidate->id
            );
        }

        $this->candidateService->deleteCandidate($candidate);

        return $this->json([], 204);
    }

    public function readCSV($csvFile, $array)
    {
        $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 0, $array['delimiter']);
        }
        fclose($file_handle);
        return $line_of_text;
    }

    // import csv файла с данными кандидатов
    public function import(Request $request)
    {
        if (isset($request->candidatesFile))
        {
            $candidatesFile = $request->file('candidatesFile');
            $extension = $candidatesFile->getClientOriginalExtension();
            if ($extension != 'csv')
            {
                return response()->json(['message' => "Ошибка. Тип файла для импорта должен быть CSV,
                разделитель - запятая."], 400);
            }

            $load = $this->readCSV($candidatesFile, array('delimiter' => ','));
            $count = count($load);

            $tariffUser = $this->checkingTariffRestrictions($count);
            if (!$tariffUser instanceof TariffUser)
            {
                return $tariffUser;
            }

            $succeed = 0;
            $errors = [];
            for ($i = 1; $i < $count - 1; $i++)
            {
                $data = $load[$i];

                $fullName = explode(" ", $data[0]);

                $fields = [
                    'surname' => count($fullName) >= 1 ? $fullName[ 0 ] : '',
                    'name' => count($fullName) >= 2 ? $fullName[ 1 ] : '',
                    'middleName' => count($fullName) >= 3 ? $fullName[ 2 ] : '',
                    'careerStartYear' => $data[ 1 ],
                    'birthDate' => $data[ 2 ],
                    'email' => $data[ 3 ],
                    'phone' => $data[ 4 ],
                    'tags' => [
                        $data[ 5 ]
                    ],
                ];


                $request = new AddRequest($fields);

                try
                {
                    $validate = $request->validate($request->rules());

                    if ($validate)
                    {
                        $search = $this->candidateService->checkCandidateExists($this->user(), $validate[ 'name' ], $validate[ 'email' ], $validate[ 'phone' ]);

                        if ($search)
                        {
                            if (count($errors) < 50)
                            {
                                $errors[] = array(
                                    'line' => $i,
                                    'error' => 'Candidate already exists'
                                );
                            }
                            continue;
                        }
                        else
                        {
                            $candidate = $this->candidateService->addNew($request);

                            if ($tariffUser)
                            {
                                $this->userTariffCandidateDao->createUserTariffCandidate($this->user(), $candidate, $tariffUser->tariff);
                            }
                            if ($candidate)
                            {
                                $succeed++;
                            };
                        }
                    }
                }
                catch (ValidationException $e)
                {
                    if (count($errors) < 50)
                    {
                        $errors[] = array(
                            'line' => $i,
                            'error' => $e->errors()
                        );
                    }
                }
            }

            if (!empty($succeed))
            {
                return response()->json([
                    'message' => "В базу данных системы успешно добавлены кандидаты",
                    'candidates' => $succeed,
                    'errors' => $errors
                ], 201);
            }
            else
            {
                return response()->json([
                    'message' => "В базу данных системы не добавлено ни одного кандидата",
                    'errors' => $errors
                ], 200);
            }
        }
        else return response()->json(['message' => "Ошибка. Отсутствует файл для импорта."], 404);

    }

    // export csv файла с данными кандидатов
    public function export()
    {
        $customer = Customer::where('user_id', $this->user()->id)->first();

        $now = Carbon::Now('Europe/Kiev')->format("Y_m_d-H_i_s.u_");

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . $now . "candidate_export.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        //$reviews = Reviews::getReviewExport($this->hw->healthwatchID)->get();
        $candidates = Candidate::with('user')->with('customer')->where('customer_id', $customer->id)->get();

        $columns = array(
            'id',
            'role',
            'name',
            'email',
            'phone',
            'avatar',
            'created_at',
            'updated_at',
            'customer_id',
            'customer_role',
            'customer_name',
            'customer_email',
            'customer_phone',
            'customer_avatar'
        );

        $callback = function() use ($candidates, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($candidates as $candidate)
            {
                fputcsv($file, array(
                    $candidate->id,
                    $candidate->user->role->name,
                    $candidate->user->name,
                    $candidate->user->email,
                    $candidate->user->phone,
                    $candidate->user->avatar,
                    $candidate->created_at,
                    $candidate->updated_at,
                    $candidate->customer_id,
                    $candidate->customer->user->role->name,
                    $candidate->customer->user->name,
                    $candidate->customer->user->email,
                    $candidate->customer->user->phone,
                    $candidate->customer->user->avatar
                ));
            }
            fclose($file);
        };
        return Response::stream($callback, 200, $headers);
    }

    private function getIfAccessible(int $id): ?Candidate
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $id)->first();
        if (!$candidate)
        {
            return null;
        }
        if ($this->user()->cannot('update', $candidate))
        {
            return null;
        }
        return $candidate;
    }

    private function checkingTariffRestrictions($count = false)
    {
        $tariffUser = $this->tariffUserDao->getActiveByUser($this->user());

        if ($tariffUser)
        {
            if ($tariffUser->tariffIsOver)
            {
                $tariffUser->is_active = false;
                $tariffUser->save();
                return $this->jsonError();
            }

            if (!$tariffUser->tariff->is_unlimited_candidates)
            {
                $userTariffCandidateCount = $this->userTariffCandidateDao->getCandidatesCountByUser($this->user());

                if ($count)
                {
                    $difference = $tariffUser->tariff->candidates_count - $userTariffCandidateCount;

                    if ($difference < $count - 2)
                    {
                        return $this->jsonError(trans('tariffs.noTariffForCandidateImport'));
                    }
                }
                else
                {
                    if ($userTariffCandidateCount >= $tariffUser->tariff->candidates_count)
                    {
                        return $this->jsonError(trans('tariffs.noTariffForCandidate'));
                    }
                }
            }
        }
        else
        {
            return $this->jsonError(trans('tariffs.noTariffForCandidate'));
        }

        return $tariffUser;
    }

    public function actionUploadImage(UploadImageRequest $request)
    {
        $uploadedFile = $request->image;
        $responseFileEntity = $this->candidateFileDao->createNew(
            $uploadedFile,
            $this->user(),
            CandidateFile::TYPE_IMAGE
        );
        $image = $this->storageService->saveCandidateImageFile($uploadedFile);
        $responseFileEntity->path = $image->path;
        $responseFileEntity->cropped_path = $image->pathCropped;
        $responseFileEntity->save();

        return $this->json(
            $responseFileEntity
        );
    }

    public function actionUploadDocument(UploadDocumentRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->candidateId)->first();
        if (!$candidate || $this->user()->cannot('update', $candidate))
        {
            return $this->responsePermissionsDenied();
        }

        if ($candidate->documents()->count() >= 15)
        {
            return $this->jsonError(trans('candidates.documentsError'));
        }

        $uploadedFile = $request->document;
        $responseFileEntity = $this->candidateFileDao->createNew(
            $uploadedFile,
            $this->user(),
            CandidateFile::TYPE_DOCUMENT
        );
        $fileName = $this->storageService->saveCandidateDocumentFile($uploadedFile);
        $responseFileEntity->candidate_id = $candidate->id;
        $responseFileEntity->path = $fileName;
        $responseFileEntity->save();

        return $this->json(
            $this->candidateService->firstWithData($candidate->id)
        );
    }

    public function actionDeleteDocument(DeleteDocumentRequest $request)
    {
        /** @var Candidate $candidate */
        $candidate = Candidate::query()->where('id', $request->candidateId)->first();
        /** @var CandidateFile $candidateFile */
        $candidateFile = CandidateFile::query()->where('id', $request->documentId)->first();
        if ($this->user()->cannot('update', $candidate)
            || $this->user()->cannot('delete', $candidateFile)
            || $candidateFile->type != CandidateFile::TYPE_DOCUMENT)
        {
            return $this->responsePermissionsDenied();
        }

        $this->storageService->deleteCandidateDocumentFile($candidateFile);

        return $this->json(
            $this->candidateService->firstWithData($candidate->id)
        );
    }
}
