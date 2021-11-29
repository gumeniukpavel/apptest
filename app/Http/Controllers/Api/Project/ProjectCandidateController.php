<?php

namespace App\Http\Controllers\Api\Project;

use App\Db\Entity\Candidate;
use App\Db\Entity\Event;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectCandidate;
use App\Db\Entity\ProjectQuestionnaire;
use App\Db\Entity\ProjectStatus;
use App\Db\Entity\ProjectTest;
use App\Db\Service\EventDao;
use App\Db\Service\ProjectCandidateDao;
use App\Db\Service\TestResultDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IdAndBoolRequest;
use App\Http\Requests\IdRequest;
use App\Http\Requests\Project\Candidate\AddRequest;
use App\Http\Requests\Project\Candidate\ListRequest;
use App\Http\Requests\Project\Candidate\SendTestResultRequest;
use App\Http\Resources\PaginationResource;
use App\Notifications\Candidate\NegativeTestResultNotification;
use App\Notifications\Candidate\PositiveTestResultNotification;
use App\Notifications\Candidate\QuestionnaireInvitationNotification;
use App\Notifications\Candidate\TestInvitationNotification;
use App\Service\AuthService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ProjectCandidateController extends BaseController
{
    private TestResultDao $testResultDao;
    protected EventDao $eventService;
    protected ProjectCandidateDao $projectCandidateService;

    public function __construct(
        AuthService $authService,
        EventDao $eventService,
        TestResultDao $testResultDao,
        ProjectCandidateDao $projectCandidateService)
    {
        parent::__construct($authService);
        $this->testResultDao = $testResultDao;
        $this->eventService = $eventService;
        $this->projectCandidateService = $projectCandidateService;
    }

    public function list(ListRequest $request)
    {
        /** @var Project $project */
        $project = Project::query()->where('id', $request->projectId)->first();
        if ($this->user()->cannot('view', $project))
        {
            return $this->responsePermissionsDenied();
        }

        $searchQueryBuilder = $this->projectCandidateService->getSearchQuery($project);

        return $this->json(
            new PaginationResource($searchQueryBuilder, $request->page)
        );
    }

    public function add(AddRequest $request)
    {
        if ($request->projectTestId && $request->projectQuestionnaireId)
        {
            return $this->jsonError();
        }

        /** @var Project $project */
        $project = Project::query()
            ->where('id', $request->projectId)
            ->first();
        if ($this->user()->cannot('view', $project))
        {
            return $this->responsePermissionsDenied();
        }
        if ($project->project_status_id != ProjectStatus::Open)
        {
            return $this->jsonError(trans('projects.notificationErrorProjectClosed'));
        }

        if ($request->projectQuestionnaireId)
        {
            /** @var ProjectQuestionnaire $projectQuestionnaire */
            $projectQuestionnaire = $project->projectQuestionnaires()
                ->where('id', $request->projectQuestionnaireId)
                ->first();
            if (!$projectQuestionnaire)
            {
                return $this->jsonError('У этого проекта нет анкет.');
            }
        }
        elseif ($request->projectTestId)
        {
            /** @var ProjectTest $projectTest */
            $projectTest = $project->projectTests()
                ->where('id', $request->projectTestId)
                ->first();
            if (!$projectTest)
            {
                return $this->jsonError('У этого проекта нет тестов.');
            }
        }
        /** @var Candidate[] $errorCandidates */
        $errorCandidates = [];

        /** @var Candidate[] | Collection $candidates */
        $candidates = Candidate::query()->whereIn('id', $request->candidateIds)->get();
        foreach ($candidates as $candidate)
        {
            if ($this->user()->cannot('view', $candidate))
            {
                return $this->responsePermissionsDenied();
            }
            try
            {
                $projectCandidate = new ProjectCandidate();
                if ($request->projectQuestionnaireId)
                {
                    $projectCandidate->project_questionnaire_id = $projectQuestionnaire->id;
                }
                elseif ($request->projectTestId)
                {
                    $projectCandidate->project_test_id = $projectTest->id;
                }
                $projectCandidate->project_id = $project->id;
                $projectCandidate->candidate_id = $candidate->id;
                $projectCandidate->save();

                $this->eventService->createEvent(
                    Event::EVENT_TYPE_PROJECT_CANDIDATE,
                    Event::EVENT_SUB_TYPE_APPEND,
                    $this->user()->id,
                    $projectCandidate->id,
                    $project->id
                );
            }
            catch (QueryException $e)
            {
                Log::error($e->getMessage(), $e->getTrace());
                $errorCandidates[] = $candidate;
                // Record is exists
                continue;
            }
        }

        if (count($errorCandidates) == 0)
        {
            return $this->json(
                $project->projectCandidates()->get()
            );
        }
        else
        {
            if (count($errorCandidates) == 1)
            {
                $name = $errorCandidates[0]->name;
                $surname = $errorCandidates[0]->surname;
                $fullName = "$name $surname";
                if ($request->projectQuestionnaireId)
                {
                    $errorMessage = trans('questionnaires.candidateAlreadyInQuestionnaire');
                }
                elseif ($request->projectTestId)
                {
                    $errorMessage = trans('tests.candidateAlreadyInTest');
                }
                return $this->jsonError(str_replace('%candidateFullName%', $fullName, $errorMessage));
            }
            else
            {
                $fullNames = "";
                foreach ($errorCandidates as $errorCandidate)
                {
                    $fullNames .= "$errorCandidate->name $errorCandidate->surname; ";
                }
                if ($request->projectQuestionnaireId)
                {
                    $errorMessage = trans('questionnaires.candidatesAlreadyInQuestionnaire');
                }
                elseif ($request->projectTestId)
                {
                    $errorMessage = trans('tests.candidatesAlreadyInTest');
                }
                return $this->jsonError(str_replace('%candidatesFullName%', $fullNames, $errorMessage));
            }
        }
    }

    public function remove(IdRequest $request)
    {
        $projectCandidate = $this->getIfAccessible($request->id);
        if (!$projectCandidate)
        {
            return $this->responsePermissionsDenied();
        }
        if ($projectCandidate->project->project_status_id != ProjectStatus::Open)
        {
            return $this->jsonError(trans('projects.notificationErrorProjectClosed'));
        }
        $this->eventService->createEvent(
            Event::EVENT_TYPE_PROJECT_CANDIDATE,
            Event::EVENT_SUB_TYPE_DELETE,
            $this->user()->id,
            $projectCandidate->id,
            $projectCandidate->project_id
        );
        $project = $projectCandidate->project;
        $this->projectCandidateService->deleteProjectCandidate($projectCandidate);
        return $this->json(
            $project->projectCandidates()->get()
        );
    }

    public function setIsFavorite(IdAndBoolRequest $request)
    {
        $projectCandidate = $this->getIfAccessible($request->id);
        if (!$projectCandidate)
        {
            return $this->responsePermissionsDenied();
        }
        if ($projectCandidate->project->project_status_id != ProjectStatus::Open)
        {
            return $this->jsonError(trans('projects.notificationErrorProjectClosed'));
        }

        $this->projectCandidateService->setIsFavoriteCandidate($projectCandidate, $request->isTrue);

        $this->eventService->createEvent(
            Event::EVENT_TYPE_SET_IS_FAVORITE,
            Event::EVENT_SUB_TYPE_APPEND,
            $this->user()->id,
            $projectCandidate->id,
            $projectCandidate->project_id,
        );
        return $this->json($projectCandidate->project);
    }

    public function setIsShowInReview(IdAndBoolRequest $request)
    {
        $projectCandidate = $this->getIfAccessible($request->id);
        if (!$projectCandidate)
        {
            return $this->responsePermissionsDenied();
        }
        $projectCandidate->is_show_in_review = $request->isTrue;
        $projectCandidate->save();
        $this->eventService->createEvent(
            Event::EVENT_TYPE_PROJECT_CANDIDATE,
            Event::EVENT_SUB_TYPE_SET_IS_SHOW_IN_REVIEW,
            $this->user()->id,
            $projectCandidate->id
        );
        return $this->json();
    }

    public function actionSendTestInvitation(IdRequest $request)
    {
        $projectCandidate = $this->getIfAccessible($request->id);
        if (!$projectCandidate)
        {
            return $this->responsePermissionsDenied();
        }

        if ($projectCandidate->project->project_status_id != ProjectStatus::Open)
        {
            return $this->jsonError(trans('projects.notificationErrorProjectClosed'));
        }

        if ($projectCandidate->cantSendNotification)
        {
            return $this->jsonError();
        }

        if ($projectCandidate->test)
        {
            if (count($projectCandidate->test->questions) == 0)
            {
                return $this->jsonError(trans('tests.questionsNotFound'));
            }
            $testToBuild = $projectCandidate->test;
        }
        else
        {
            if (count($projectCandidate->questionnaire->questions) == 0)
            {
                return $this->jsonError(trans('questionnaire.questionsNotFound'));
            }
            $testToBuild = $projectCandidate->questionnaire;
        }

        try {
            $userProfile = $this->user()->profile;

            $preparedTestResult = $this->testResultDao->buildTestResultsPackForUser(
                $projectCandidate,
                $testToBuild
            );
            if (!$preparedTestResult)
            {
                return $this->jsonError();
            }

            if ($testToBuild->isTest())
            {
                /** @var LetterTemplate $template */
                $template = LetterTemplate::query()
                    ->where([
                        'user_id' => $this->user()->id,
                        'type_id' => LetterTemplateType::TestInvitation,
                        'is_active' => true
                    ])->first();

                $projectCandidate->candidate->notify(
                    new TestInvitationNotification($preparedTestResult, $userProfile, $template)
                );
            }
            else
            {
                /** @var LetterTemplate $template */
                $template = LetterTemplate::query()
                    ->where([
                        'user_id' => $this->user()->id,
                        'type_id' => LetterTemplateType::QuestionnaireInvitation,
                        'is_active' => true
                    ])->first();

                $projectCandidate->candidate->notify(
                    new QuestionnaireInvitationNotification($preparedTestResult, $userProfile, $template)
                );
            }

            $projectCandidate->is_invitation_sent = true;
            $projectCandidate->last_notification_at = Carbon::now()->timestamp;
            $projectCandidate->is_last_send_error = false;
            $projectCandidate->last_send_error_at = null;

            if ($testToBuild->isTest())
            {
                $this->eventService->createEvent(
                    Event::EVENT_TYPE_SEND_TEST_INVITATION,
                    Event::EVENT_SUB_TYPE_INVITATION,
                    $this->user()->id,
                    $preparedTestResult->test_id,
                    $projectCandidate->id
                );
            }
            else
            {
                $this->eventService->createEvent(
                    Event::EVENT_TYPE_SEND_QUESTIONNAIRE_INVITATION,
                    Event::EVENT_SUB_TYPE_INVITATION,
                    $this->user()->id,
                    $preparedTestResult->test_id,
                    $projectCandidate->id
                );
            }
        }
        catch (\Exception $e)
        {
            $projectCandidate->is_last_send_error = true;
            $projectCandidate->last_send_error_at = Carbon::now()->timestamp;
            Log::error($e->getMessage());

            return $this->jsonError(trans('candidates.notificationError'));
        }
        $projectCandidate->save();

        return $this->json();
    }

    public function actionSendTestResult(SendTestResultRequest $request)
    {
        $projectCandidate = $this->getIfAccessible($request->projectCandidateId);
        if (!$projectCandidate)
        {
            return $this->responsePermissionsDenied();
        }

        if ($projectCandidate->test)
        {
            $testToBuild = $projectCandidate->test;
        }
        else
        {
            $testToBuild = $projectCandidate->questionnaire;
        }

        $preparedTestResult = $this->testResultDao->buildTestResultsPackForUser(
            $projectCandidate,
            $testToBuild
        );
        if (!$preparedTestResult)
        {
            return $this->jsonError();
        }

        if ($request->isPositiveAnswer == true)
        {
            /** @var LetterTemplate $template */
            $template = LetterTemplate::query()
                ->where([
                    'user_id' => $this->user()->id,
                    'type_id' => LetterTemplateType::PositiveAnswer,
                    'is_active' => true
                ])->first();

            $projectCandidate->candidate->notify(
                new PositiveTestResultNotification(
                    $preparedTestResult,
                    $this->user()->fullName,
                    $template
                )
            );
        }
        else
        {
            /** @var LetterTemplate $template */
            $template = LetterTemplate::query()
                ->where([
                    'user_id' => $this->user()->id,
                    'type_id' => LetterTemplateType::NegativeAnswer,
                    'is_active' => true
                ])->first();

            $projectCandidate->candidate->notify(
                new NegativeTestResultNotification(
                    $preparedTestResult,
                    $this->user()->fullName,
                    $template
                )
            );
        }
        return $this->json();
    }

    private function getIfAccessible(int $id): ?ProjectCandidate
    {
        /** @var ProjectCandidate $projectCandidate */
        $projectCandidate = ProjectCandidate::query()->where('id', $id)->first();
        if (!$projectCandidate)
        {
            return null;
        }
        if ($this->user()->cannot('update', $projectCandidate->project))
        {
            return null;
        }
        return $projectCandidate;
    }
}
