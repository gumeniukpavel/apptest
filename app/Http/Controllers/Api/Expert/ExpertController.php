<?php

namespace App\Http\Controllers\Api\Expert;


use App\Constant\ApprovalRequestStatus;
use App\Constant\ResultOfChecking;
use App\Constant\TestType;
use App\Db\Entity\Candidate;
use App\Db\Entity\Event;
use App\Db\Entity\Expert;
use App\Db\Entity\ExpertFile;
use App\Db\Entity\ExpertInterviewEvent;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\QuestionnaireApprovalRequest;
use App\Db\Entity\TestApprovalRequest;
use App\Db\Entity\TestResult;
use App\Db\Entity\UserEvent;
use App\Db\Service\EventDao;
use App\Db\Service\ExpertDao;
use App\Db\Service\ExpertFileDao;
use App\Db\Service\ExpertImageDao;
use App\Db\Service\ExpertInterviewEventDao;
use App\Db\Service\UserEventDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Candidate\ExportCandidateDataRequest;
use App\Http\Requests\Expert\AddRequest;
use App\Http\Requests\Expert\ApprovalInvitationRequest;
use App\Http\Requests\Expert\DeleteDocumentRequest;
use App\Http\Requests\Expert\ExportExpertDataRequest;
use App\Http\Requests\Expert\GetApprovalRequestListRequest;
use App\Http\Requests\Expert\GetApprovalResultRequest;
use App\Http\Requests\Expert\GetListUserEventRequest;
use App\Http\Requests\Expert\InterviewInvitationRequest;
use App\Http\Requests\Expert\GetListRequest;
use App\Http\Requests\Expert\SaveCheckResultRequest;
use App\Http\Requests\Expert\ApprovalResultRequest;
use App\Http\Requests\Expert\UpdateNotesRequest;
use App\Http\Requests\Expert\UpdateRequest;
use App\Http\Requests\Expert\UploadDocumentRequest;
use App\Http\Requests\Expert\UploadImageRequest;
use App\Http\Resources\PaginationResource;
use App\Notifications\Expert\InterviewAgreeNotification;
use App\Notifications\Expert\InterviewCancelNotification;
use App\Notifications\Expert\InterviewInvitationNotification;
use App\Notifications\Expert\QuestionnaireApprovalNotification;
use App\Notifications\Expert\TestApprovalNotification;
use App\Notifications\User\ExpertAcceptedQuestionnaireApprovalRequestsNotification;
use App\Notifications\User\ExpertAcceptedTestApprovalRequestsNotification;
use App\Notifications\User\ExpertAgreeInterviewNotification;
use App\Notifications\User\ExpertCancelInterviewNotification;
use App\Notifications\User\ExpertQuestionnaireApprovedNotification;
use App\Notifications\User\ExpertQuestionnaireNonApprovedNotification;
use App\Notifications\User\ExpertTestApprovedNotification;
use App\Notifications\User\ExpertTestNonApprovedNotification;
use App\Service\AuthService;
use App\Service\StorageService;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpertController extends BaseController
{
    protected ExpertDao $expertDao;
    protected ExpertFileDao $expertFileDao;
    protected ExpertImageDao $expertImageDao;
    protected StorageService $storageService;
    protected EventDao $eventDao;
    protected UserEventDao $userEventDao;
    protected ExpertInterviewEventDao $expertInterviewEventDao;

    public function __construct(
        ExpertDao $expertDao,
        ExpertFileDao $expertFileDao,
        ExpertImageDao $expertImageDao,
        StorageService $storageService,
        EventDao $eventDao,
        UserEventDao $userEventDao,
        ExpertInterviewEventDao $expertInterviewEventDao,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->expertDao = $expertDao;
        $this->expertFileDao = $expertFileDao;
        $this->expertImageDao = $expertImageDao;
        $this->storageService = $storageService;
        $this->eventDao = $eventDao;
        $this->userEventDao = $userEventDao;
        $this->expertInterviewEventDao = $expertInterviewEventDao;
    }

    public function actionGetList(GetListRequest $request)
    {
        $expertQuery = $this->expertDao->searchQuery(
            $this->user(),
            $request->searchString,
            $request->careerStartYearFrom,
            $request->careerStartYearTo,
            $request->ageFromTimestamp,
            $request->ageToTimestamp,
            $request->orderType,
            $request->tags,
            $request->staffLevel,
            $request->staffSpecialization,
        );
        return $this->json(
            new PaginationResource($expertQuery, $request->page)
        );
    }

    public function actionGetOne(int $id)
    {
        /** @var Expert $expert */
        $expert = Expert::query()->where('id', $id)->first();
        if ($this->user()->cannot('view', $expert))
        {
            return $this->responsePermissionsDenied();
        }
        return $this->json(
            $this->expertDao->firstWithData($id)
        );
    }

    public function addNew(AddRequest $request)
    {
        /** @var ExpertFile $expertImage */
        $expertImage = ExpertFile::query()->where('id', $request->imageId)->first();
        if ($this->user()->cannot('create', Expert::class)
            || ($expertImage && $this->user()->cannot('view', $expertImage))
        )
        {
            return $this->responsePermissionsDenied();
        }

        $expert = $this->expertDao->addNew($request, $expertImage);
        if (!$expert)
        {
            return $this->jsonError();
        }

        $this->eventDao->createEvent(
            Event::EVENT_TYPE_EXPERT,
            Event::EVENT_SUB_TYPE_CREATE,
            $this->user()->id,
            $expert->id
        );
        return $this->json(
            $this->expertDao->firstWithData($expert->id)
        );
    }

    public function actionUpdate(UpdateRequest $request)
    {
        /** @var Expert $expert */
        $expert = Expert::byId($request->id);
        /** @var ExpertFile $expertImage */
        $expertImage = ExpertFile::query()->where('id', $request->imageId)->first();
        if ((!$expert || $this->user()->cannot('update', $expert))
            || ($expertImage && $this->user()->cannot('view', $expertImage)))
        {
            return $this->responsePermissionsDenied();
        }

        $this->expertDao->update($request, $expert, $expertImage);
        $this->eventDao->createEvent(
            Event::EVENT_TYPE_EXPERT,
            Event::EVENT_SUB_TYPE_UPDATE,
            $this->user()->id,
            $expert->id
        );
        return $this->json(
            $this->expertDao->firstWithData($expert->id)
        );
    }

    public function actionUpdateNotes(UpdateNotesRequest $request)
    {
        /** @var Expert $expert */
        $expert = Expert::query()->where('id', $request->id)->first();
        if (!$expert || $this->user()->cannot('update', $expert))
        {
            return $this->responsePermissionsDenied();
        }

        $this->expertDao->updateNote($request, $expert);
        return $this->json(
            $this->expertDao->firstWithData($expert->id)
        );
    }

    public function actionUploadDocument(UploadDocumentRequest $request)
    {
        /** @var Expert $expert */
        $expert = Expert::query()->where('id', $request->expertId)->first();
        if (!$expert || $this->user()->cannot('update', $expert))
        {
            return $this->responsePermissionsDenied();
        }

        if ($expert->documents()->count() >= 15)
        {
            return $this->jsonError(trans('experts.documentsError'));
        }

        $uploadedFile = $request->document;
        $responseFileEntity = $this->expertFileDao->createNew(
            $uploadedFile,
            $this->user(),
            ExpertFile::TYPE_DOCUMENT
        );
        $fileName = $this->storageService->saveExpertDocumentFile($uploadedFile);
        $responseFileEntity->expert_id = $expert->id;
        $responseFileEntity->path = $fileName;
        $responseFileEntity->save();

        return $this->json(
            $this->expertDao->firstWithData($expert->id)
        );
    }

    public function actionDeleteDocument(DeleteDocumentRequest $request)
    {
        /** @var Expert $expert */
        $expert = Expert::query()->where('id', $request->expertId)->first();
        /** @var ExpertFile $expertFile */
        $expertFile = ExpertFile::query()->where('id', $request->documentId)->first();
        if ($this->user()->cannot('update', $expert)
            || $this->user()->cannot('delete', $expertFile)
            || $expertFile->type != ExpertFile::TYPE_DOCUMENT)
        {
            return $this->responsePermissionsDenied();
        }

        $this->storageService->deleteExpertDocumentFile($expertFile);

        return $this->json(
            $this->expertDao->firstWithData($expert->id)
        );
    }

    public function actionExportToPDFExpertData(ExportExpertDataRequest $request)
    {
        /** @var Expert $expert */
        $expert = Expert::query()->where('id', $request->expertId)->first();
        if ($this->user()->cannot('view', $expert))
        {
            return $this->responsePermissionsDenied();
        }

        /** @var Expert $expert */
        $expert = $this->expertDao->firstWithData($request->expertId);


        $pdf = PDF::loadView('pdf.expertData', [
            'expert' => $expert,
            'birthDay' => $expert->birth_date->day,
            'birthMonth',
            'birthYear',
        ]);
        $folder = 'expert/documents';
        $name = 'expertData_';

        if ($expert->pdf_data_url)
        {
            return $expert->pdf_data_url;
        }
        else
        {
            $path = $this->storageService->saveExportedPDF($pdf, $folder, $name);
            $expert->pdf_data_url = $path;
            $expert->save();

            return $path;
        }
    }

    public function actionDelete(int $id)
    {
        /** @var Expert $expert */
        $expert = Expert::byId($id);
        if (!$expert || $this->user()->cannot('delete', $expert))
        {
            return $this->responsePermissionsDenied();
        }

        $this->eventDao->createEvent(
            Event::EVENT_TYPE_EXPERT,
            Event::EVENT_SUB_TYPE_DELETE,
            $this->user()->id,
            $expert->id
        );
        $this->expertDao->deleteExpert($expert);

        return $this->json([], 204);
    }

    public function actionGetUserEventsByDate(GetListUserEventRequest $request)
    {
        $date = Carbon::createFromTimestamp($request->date);
        $userEvents = $this->userEventDao->getUserEventsByDate($this->user(), $date);

        return $this->json(
            $userEvents
        );
    }

    public function actionSendInterviewInvitation(InterviewInvitationRequest $request)
    {
        $expert = $this->getIfAccessible($request->expertId);
        if (!$expert)
        {
            return $this->responsePermissionsDenied();
        }

        $userEvent = $this->getIfAccessibleUserEvent($request->userEventId);
        if (!$userEvent)
        {
            return $this->responsePermissionsDenied();
        }

        if ($expert->cantSendNotification)
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

            $createUserEvent = $this->expertInterviewEventDao->createExpertInterviewEvent(
                $request,
                $expert
            );

            /** @var LetterTemplate $template */
            $template = LetterTemplate::query()
                ->where([
                    'user_id' => $this->user()->id,
                    'type_id' => LetterTemplateType::ExpertInterviewInvitation,
                    'is_active' => true
                ])->first();

            $expert->notify(
                new InterviewInvitationNotification($createUserEvent, $userProfile ,$template)
            );
            $expert->is_invitation_sent = true;
            $expert->last_notification_at = Carbon::now()->timestamp;
            $expert->is_last_send_error = false;
            $expert->last_send_error_at = null;
            $expert->save();
        }
        catch (\Exception $e)
        {
            $expert->is_last_send_error = true;
            $expert->last_send_error_at = Carbon::now()->timestamp;
            $expert->save();
            Log::error($e->getMessage());

            return $this->jsonError(trans('candidates.notificationError'));
        }

        $this->eventDao->createEvent(
            Event::EVENT_TYPE_EXPERT_INTERVIEW_INVITATION,
            Event::EVENT_SUB_TYPE_SENT,
            $this->user()->id,
            $expert->id
        );

        return $this->json();
    }

    public function expertAgreeToAnInterview(string $token)
    {
        $expertInterviewEvent = $this->expertInterviewEventDao->getExpertEventByAgreedToken($token);

        if ($expertInterviewEvent)
        {
            $expertInterviewEvent->status = ExpertInterviewEvent::EXPERT_EVENT_STATUS_APPROVED;
            $expertInterviewEvent->expert->notify(new InterviewAgreeNotification($expertInterviewEvent));
            $expertInterviewEvent->user->notify(new ExpertAgreeInterviewNotification($expertInterviewEvent));
            $expertInterviewEvent->save();

            /** @var Event $event */
            $event = $this->eventDao->createEvent(
                Event::EVENT_TYPE_EXPERT_ACCEPTED_INVITATION,
                Event::EVENT_SUB_TYPE_EXPERT,
                $expertInterviewEvent->user_id,
                $expertInterviewEvent->expert_id
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

    public function expertCancelToAnInterview(string $token)
    {
        $expertInterviewEvent = $this->expertInterviewEventDao->getExpertEventByCancelToken($token);

        if ($expertInterviewEvent)
        {
            $expertInterviewEvent->status = ExpertInterviewEvent::EXPERT_EVENT_STATUS_CANCELED;
            $expertInterviewEvent->expert->notify(new InterviewCancelNotification($expertInterviewEvent));
            $expertInterviewEvent->user->notify(new ExpertCancelInterviewNotification($expertInterviewEvent));
            $expertInterviewEvent->save();

            /** @var Event $event */
            $event = $this->eventDao->createEvent(
                Event::EVENT_TYPE_EXPERT_CANCEL_INVITATION,
                Event::EVENT_SUB_TYPE_EXPERT,
                $expertInterviewEvent->user_id,
                $expertInterviewEvent->expert_id
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

    public function actionGetTestApprovalRequestsListByExpert(GetApprovalResultRequest $request)
    {
        /** @var Expert $expert */
        $expert = Expert::query()->where('id', $request->id)->first();
        if ($this->user()->cannot('view', $expert))
        {
            return $this->responsePermissionsDenied();
        }

        $testApprovalResultsQuery = $this->expertDao->getExpertApprovalResults(
            $expert,
            TestType::$Test,
            $request->orderType
        );

        return $this->json(
            new PaginationResource($testApprovalResultsQuery, $request->getPage())
        );
    }

    public function actionGetQuestionnaireApprovalRequestsListByExpert(GetApprovalResultRequest $request)
    {
        /** @var Expert $expert */
        $expert = Expert::query()->where('id', $request->id)->first();
        if ($this->user()->cannot('view', $expert))
        {
            return $this->responsePermissionsDenied();
        }

        $questionnaireApprovalResultsQuery = $this->expertDao->getExpertApprovalResults(
            $expert,
            TestType::$Questionnaire,
            $request->orderType
        );

        return $this->json(
            new PaginationResource($questionnaireApprovalResultsQuery, $request->getPage())
        );
    }


        public function actionGetApprovalRequestsListWithStatusApproved(GetApprovalRequestListRequest $request)
    {
        $approvalRequest = $this->expertDao->getListApprovalRequest($request);

        return $this->json(
            new PaginationResource($approvalRequest, $request->page)
        );
    }

    public function actionSendTestApprovalRequests(ApprovalInvitationRequest $request)
    {
        $expert = $this->getIfAccessible($request->expertId);
        if (!$expert)
        {
            return $this->responsePermissionsDenied();
        }

        $testResult = $this->getIfAccessibleTestResult($request->testResultId);
        if (!$testResult)
        {
            return $this->responsePermissionsDenied();
        }

        if (!$testResult->test->isTest() && !$testResult->test->isQuestionnaire())
        {
            return $this->jsonError();
        }

        try {
            $approvalRequest = $this->expertDao->createApprovalRequest(
                $request,
                $testResult
            );
            if ($testResult->test->isTest())
            {
                $expert->notify(
                    new TestApprovalNotification($approvalRequest)
                );
            }
            else
            {
                $expert->notify(
                    new QuestionnaireApprovalNotification($approvalRequest)
                );
            }
        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage());
            return $this->jsonError(trans('experts.notificationError'));
        }
        if ($testResult->test->isTest())
        {
            $this->eventDao->createEvent(
                Event::EVENT_TYPE_TEST_APPROVAL_REQUESTS,
                Event::EVENT_SUB_TYPE_RESULT,
                $this->user()->id,
                $testResult->id,
                $expert->id
            );
        }
        else
        {
            $this->eventDao->createEvent(
                Event::EVENT_TYPE_QUESTIONNAIRE_APPROVAL_REQUESTS,
                Event::EVENT_SUB_TYPE_ANSWERS,
                $this->user()->id,
                $testResult->id,
                $expert->id
            );
        }

        return $this->json();
    }

    public function actionAcceptedTestApprovalRequests(ApprovalResultRequest $request)
    {
        $testApprovalRequests = $this->expertDao->getTestApprovalRequestByToken($request);
        if ($testApprovalRequests)
        {
            if ($testApprovalRequests->status == ApprovalRequestStatus::$Approved->getValue())
            {
                return $this->json($testApprovalRequests);
            }
            else
            {
                if ($request->status == ApprovalRequestStatus::$Approved->getValue())
                {
                    $testApprovalRequests->status = ApprovalRequestStatus::$Approved->getValue();
                    $testApprovalRequests->save();

                    $testApprovalRequests->user->notify(new ExpertAcceptedTestApprovalRequestsNotification($testApprovalRequests));

                    /** @var Event $event */
                    $event = $this->eventDao->createEvent(
                        Event::EVENT_TYPE_AGREED_CHECK_TEST_RESULT,
                        Event::EVENT_SUB_TYPE_EXPERT,
                        $testApprovalRequests->user_id,
                        $testApprovalRequests->id
                    );

                    $event->is_popup_notification = true;
                    $event->save();

                    return $this->json($testApprovalRequests);
                }
                else
                {
                    $testApprovalRequests->status = ApprovalRequestStatus::$Canceled->getValue();
                    $testApprovalRequests->access_token_verify = null;
                    $testApprovalRequests->access_token_cancel = null;
                    $testApprovalRequests->save();

                    $testApprovalRequests->user->notify(new ExpertAcceptedTestApprovalRequestsNotification($testApprovalRequests));

                    /** @var Event $event */
                    $event = $this->eventDao->createEvent(
                        Event::EVENT_TYPE_CANCEL_CHECK_TEST_RESULT,
                        Event::EVENT_SUB_TYPE_EXPERT,
                        $testApprovalRequests->user_id,
                        $testApprovalRequests->id
                    );

                    $event->is_popup_notification = true;
                    $event->save();
                    return $this->json(trans('experts.reviewRequestCanceled'));
                }
            }
        }
        else
        {
            return $this->jsonError(trans('candidates.invalidToken'));
        }
    }

    public function actionAcceptedQuestionnaireApprovalRequests(ApprovalResultRequest $request)
    {
        $questionnaireApprovalRequests = $this->expertDao->getQuestionnaireApprovalRequestByToken($request);

        if ($questionnaireApprovalRequests)
        {
            if ($questionnaireApprovalRequests->status == ApprovalRequestStatus::$Approved->getValue())
            {
                return $this->json($questionnaireApprovalRequests);
            }
            else
            {
                if ($request->status == ApprovalRequestStatus::$Approved->getValue())
                {
                    $questionnaireApprovalRequests->status = ApprovalRequestStatus::$Approved->getValue();
                    $questionnaireApprovalRequests->save();

                    $questionnaireApprovalRequests->user->notify(new ExpertAcceptedQuestionnaireApprovalRequestsNotification($questionnaireApprovalRequests));

                    /** @var Event $event */
                    $event = $this->eventDao->createEvent(
                        Event::EVENT_TYPE_AGREED_CHECK_QUESTIONNAIRE_RESULT,
                        Event::EVENT_SUB_TYPE_EXPERT,
                        $questionnaireApprovalRequests->user_id,
                        $questionnaireApprovalRequests->id
                    );

                    $event->is_popup_notification = true;
                    $event->save();

                    return $this->json($questionnaireApprovalRequests);
                }
                else
                {
                    $questionnaireApprovalRequests->status = ApprovalRequestStatus::$Canceled->getValue();
                    $questionnaireApprovalRequests->access_token_verify = null;
                    $questionnaireApprovalRequests->access_token_cancel = null;
                    $questionnaireApprovalRequests->save();

                    $questionnaireApprovalRequests->user->notify(new ExpertAcceptedQuestionnaireApprovalRequestsNotification($questionnaireApprovalRequests));

                    /** @var Event $event */
                    $event = $this->eventDao->createEvent(
                        Event::EVENT_TYPE_CANCEL_CHECK_QUESTIONNAIRE_RESULT,
                        Event::EVENT_SUB_TYPE_EXPERT,
                        $questionnaireApprovalRequests->user_id,
                        $questionnaireApprovalRequests->id
                    );

                    $event->is_popup_notification = true;
                    $event->save();

                    return $this->json(trans('experts.reviewRequestCanceled'));
                }
            }
        }
        else
        {
            return $this->jsonError(trans('candidates.invalidToken'));
        }
    }

    public function actionTestApprovalResponseType(SaveCheckResultRequest $request)
    {
        /** @var TestApprovalRequest $testApprovalRequests */
        $testApprovalRequests = TestApprovalRequest::byId($request->id);
        if ($testApprovalRequests->status != ApprovalRequestStatus::$Approved->getValue())
        {
            return $this->jsonError();
        }

        if ($testApprovalRequests)
        {
            if ($request->resultOfChecking == ResultOfChecking::$Approved->getValue())
            {
                $testApprovalRequests->result_of_checking = ResultOfChecking::$Approved->getValue();

                $testApprovalRequests->user->notify(
                    new ExpertTestApprovedNotification($testApprovalRequests)
                );

                /** @var Event $event */
                $event = $this->eventDao->createEvent(
                    Event::EVENT_TYPE_APPROVED_TEST_RESULT,
                    Event::EVENT_SUB_TYPE_EXPERT,
                    $testApprovalRequests->user_id,
                    $testApprovalRequests->id
                );
                $event->is_popup_notification = true;
                $event->save();
            }
            else
            {
                $testApprovalRequests->result_of_checking = ResultOfChecking::$NonApproved->getValue();

                $testApprovalRequests->user->notify(
                    new ExpertTestNonApprovedNotification($testApprovalRequests)
                );

                /** @var Event $event */
                $event = $this->eventDao->createEvent(
                    Event::EVENT_TYPE_NON_APPROVED_TEST_RESULT,
                    Event::EVENT_SUB_TYPE_EXPERT,
                    $testApprovalRequests->user_id,
                    $testApprovalRequests->id
                );
                $event->is_popup_notification = true;
                $event->save();
            }
            $testApprovalRequests->comment = $request->comment;
            $testApprovalRequests->access_token_verify = null;
            $testApprovalRequests->access_token_cancel = null;
            $testApprovalRequests->save();

            return $testApprovalRequests;
        }
        else
        {
            return $this->jsonError(trans('candidates.invalidToken'));
        }
    }

    public function actionQuestionnaireApprovalResponseType(SaveCheckResultRequest $request)
    {
        /** @var QuestionnaireApprovalRequest $questionnaireApprovalRequests */
        $questionnaireApprovalRequests = QuestionnaireApprovalRequest::byId($request->id);
        if ($questionnaireApprovalRequests->status != ResultOfChecking::$Approved->getValue())
        {
            return $this->jsonError();
        }

        if ($questionnaireApprovalRequests)
        {
            if ($request->resultOfChecking == ResultOfChecking::$Approved->getValue())
            {
                $questionnaireApprovalRequests->result_of_checking = ResultOfChecking::$Approved->getValue();

                $questionnaireApprovalRequests->user->notify(
                    new ExpertQuestionnaireApprovedNotification($questionnaireApprovalRequests)
                );

                /** @var Event $event */
                $event = $this->eventDao->createEvent(
                    Event::EVENT_TYPE_APPROVED_QUESTIONNAIRE_RESULT,
                    Event::EVENT_SUB_TYPE_EXPERT,
                    $questionnaireApprovalRequests->user_id,
                    $questionnaireApprovalRequests->id
                );
                $event->is_popup_notification = true;
                $event->save();
            }
            else
            {
                $questionnaireApprovalRequests->result_of_checking = ResultOfChecking::$NonApproved->getValue();

                $questionnaireApprovalRequests->user->notify(
                    new ExpertQuestionnaireNonApprovedNotification($questionnaireApprovalRequests)
                );

                /** @var Event $event */
                $event = $this->eventDao->createEvent(
                    Event::EVENT_TYPE_NON_APPROVED_QUESTIONNAIRE_RESULT,
                    Event::EVENT_SUB_TYPE_EXPERT,
                    $questionnaireApprovalRequests->user_id,
                    $questionnaireApprovalRequests->id
                );
                $event->is_popup_notification = true;
                $event->save();
            }
            $questionnaireApprovalRequests->comment = $request->comment;
            $questionnaireApprovalRequests->access_token_verify = null;
            $questionnaireApprovalRequests->access_token_cancel = null;
            $questionnaireApprovalRequests->save();
            return $questionnaireApprovalRequests;
        }
        else
        {
            return $this->jsonError(trans('candidates.invalidToken'));
        }
    }

    public function actionUploadImage(UploadImageRequest $request)
    {
        $uploadedFile = $request->image;
        $responseFileEntity = $this->expertImageDao->createNew(
            $uploadedFile,
            $this->user()
        );
        $image = $this->storageService->saveExpertImageFile($uploadedFile);
        $responseFileEntity->path = $image->path;
        $responseFileEntity->cropped_path = $image->pathCropped;
        $responseFileEntity->save();

        return $this->json(
            $responseFileEntity
        );
    }

    private function getIfAccessibleTestResult(int $id): ?TestResult
    {
        /** @var TestResult $testResult */
        $testResult = TestResult::query()
            ->where('id', $id)
            ->first();
        if (!$testResult)
        {
            return null;
        }
        return $testResult;
    }

    private function getIfAccessibleUserEvent(int $id): ?UserEvent
    {
        /** @var UserEvent $userEvent*/
        $userEvent = UserEvent::query()->where('id', $id)->first();
        if (!$userEvent)
        {
            return null;
        }
        return $userEvent;
    }

    private function getIfAccessible(int $id): ?Expert
    {
        /** @var Expert $expert */
        $expert = Expert::query()->where('id', $id)->first();
        if (!$expert)
        {
            return null;
        }
        if ($this->user()->cannot('update', $expert))
        {
            return null;
        }
        return $expert;
    }
}
