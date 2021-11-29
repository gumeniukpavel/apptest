<?php

namespace App\Http\Controllers\Api\Project;

use App\Db\Entity\Event;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectQuestionnaire;
use App\Db\Entity\ProjectStatus;
use App\Db\Entity\TariffUser;
use App\Db\Entity\Test;
use App\Db\Service\EventDao;
use App\Db\Service\ProjectDao;
use App\Db\Service\TariffUserDao;
use App\Db\Service\UserTariffCategoryDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Project\Questionnaire\DeleteRequest;
use App\Http\Requests\Project\Questionnaire\SetRequest;
use App\Service\AuthService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ProjectQuestionnaireController extends BaseController
{
    protected ProjectDao $projectService;
    protected TariffUserDao $tariffUserDao;
    protected UserTariffCategoryDao $userTariffCategoryDao;
    protected EventDao $eventService;

    public function __construct(
        ProjectDao $projectDao,
        TariffUserDao $tariffUserDao,
        UserTariffCategoryDao $userTariffCategoryDao,
        EventDao $eventService,
        AuthService $authService)
    {
        parent::__construct($authService);
        $this->projectService = $projectDao;
        $this->tariffUserDao = $tariffUserDao;
        $this->userTariffCategoryDao = $userTariffCategoryDao;
        $this->eventService = $eventService;
    }

    public function add(SetRequest $request)
    {
        /** @var Project $project */
        $project = Project::query()->where('id', $request->projectId)->first();
        /** @var Test $questionnaire */
        $questionnaire = Test::query()->where('id', $request->questionnaireId)->first();
        if ($questionnaire->isTest())
        {
            return $this->jsonError();
        }
        if ($project->project_status_id != ProjectStatus::Open)
        {
            return $this->jsonError(trans('projects.notificationErrorProjectClosed'));
        }

        if (
            $this->user()->cannot('update', $project)
            || $this->user()->cannot('view', $questionnaire)
        )
        {
            return $this->responsePermissionsDenied();
        }

        $tariffUser = $this->checkingTariffRestrictions($questionnaire);
        if (!$tariffUser instanceof TariffUser)
        {
            return $tariffUser;
        }

        try
        {
            $project->questionnaires()->save($questionnaire);

            /** @var ProjectQuestionnaire $projectQuestionnaire */
            $projectQuestionnaire = ProjectQuestionnaire::query()->where([
                'project_id' => $project->id,
                'questionnaire_id' => $questionnaire->id
            ])->first();

            if(isset($tariffUser))
            {
                $this->userTariffCategoryDao->createUserTariffCategory($this->user(), $questionnaire->category_id, $tariffUser->tariff);
            }

            $this->eventService->createEvent(
                Event::EVENT_TYPE_PROJECT_QUESTIONNAIRE,
                Event::EVENT_SUB_TYPE_CREATE,
                $this->user()->id,
                $projectQuestionnaire->id,
                $project->id
            );
        }
        catch (QueryException $exception)
        {
            Log::error($exception->getMessage());
            return $this->json(
                $this->projectService->firstWithData($project->id)
            );
        }
        return $this->json(
            $this->projectService->firstWithData($project->id)
        );
    }

    public function delete(DeleteRequest $request)
    {
        /** @var ProjectQuestionnaire $projectQuestionnaire */
        $projectQuestionnaire = ProjectQuestionnaire::byId($request->projectQuestionnaireId);
        if (!$projectQuestionnaire || $projectQuestionnaire->questionnaire->isTest())
        {
            return $this->jsonError();
        }
        if ($projectQuestionnaire->project->project_status_id != ProjectStatus::Open)
        {
            return $this->jsonError(trans('projects.notificationErrorProjectClosed'));
        }
        $project = $projectQuestionnaire->project;
        if (!$project || $this->user()->cannot('delete', $project))
        {
            return $this->responsePermissionsDenied();
        }
        $this->eventService->createEvent(
            Event::EVENT_TYPE_PROJECT_QUESTIONNAIRE,
            Event::EVENT_SUB_TYPE_DELETE,
            $this->user()->id,
            $projectQuestionnaire->id
        );
        $this->projectService->deleteProjectQuestionnaire($projectQuestionnaire);
        return $this->json(
            $this->projectService->firstWithData($project->id)
        );
    }

    private function checkingTariffRestrictions(Test $questionnaire)
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

            if (!$tariffUser->tariff->is_unlimited_categories)
            {
                $userTariffCategories = $this->userTariffCategoryDao->getCategoriesCountByUser($this->user());
                if ($userTariffCategories >= $tariffUser->tariff->categories_count)
                {
                    $tariffCategory = $this->userTariffCategoryDao->getUserTariffCategory($this->user(), $questionnaire->category_id);
                    if (!$tariffCategory)
                    {
                        return $this->jsonError(trans('tariffs.noTariffForCategory'));
                    }
                }
            }
        }
        else
        {
            return $this->jsonError(trans('tariffs.noTariffForTest'));
        }

        return $tariffUser;
    }
}
