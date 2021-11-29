<?php

namespace App\Http\Controllers\Api\Project;

use App\Constant\TestType;
use App\Db\Entity\Event;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectStatus;
use App\Db\Entity\ProjectTest;
use App\Db\Entity\TariffUser;
use App\Db\Entity\Test;
use App\Db\Service\EventDao;
use App\Db\Service\ProjectDao;
use App\Db\Service\TariffUserDao;
use App\Db\Service\UserTariffCategoryDao;
use App\Db\Service\UserTariffTestDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Project\Test\DeleteRequest;
use App\Http\Requests\Project\Test\SetRequest;
use App\Service\AuthService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ProjectTestController extends BaseController
{
    protected ProjectDao $projectService;
    protected TariffUserDao $tariffUserDao;
    protected UserTariffTestDao $userTariffTestDao;
    protected UserTariffCategoryDao $userTariffCategoryDao;
    protected EventDao $eventService;

    public function __construct(
        ProjectDao $projectDao,
        TariffUserDao $tariffUserDao,
        UserTariffTestDao $userTariffTestDao,
        UserTariffCategoryDao $userTariffCategoryDao,
        EventDao $eventService,
        AuthService $authService)
    {
        parent::__construct($authService);
        $this->projectService = $projectDao;
        $this->tariffUserDao = $tariffUserDao;
        $this->userTariffTestDao = $userTariffTestDao;
        $this->userTariffCategoryDao = $userTariffCategoryDao;
        $this->eventService = $eventService;
    }

    public function add(SetRequest $request)
    {
        /** @var Project $project */
        $project = Project::query()->where('id', $request->projectId)->first();
        /** @var Test $test */
        $test = Test::query()->where('id', $request->testId)->first();
        if (
            $this->user()->cannot('update', $project)
            || $this->user()->cannot('view', $test)
            || $test->isQuestionnaire()
        )
        {
            return $this->responsePermissionsDenied();
        }
        if ($project->project_status_id != ProjectStatus::Open)
        {
            return $this->jsonError(trans('projects.notificationErrorProjectClosed'));
        }
        if (!$test->user_id)
        {
            $testUser = $this->userTariffTestDao->getUserTariffTest($this->user(), $test);
            if (!$testUser)
            {
                $tariffUser = $this->checkingTariffRestrictions($test);
                if (!$tariffUser instanceof TariffUser)
                {
                    return $tariffUser;
                }
            }
        }
        try
        {
            $project->tests()->save($test);

            /** @var ProjectTest $projectTest */
            $projectTest = ProjectTest::query()->where([
                'project_id' => $project->id,
                'test_id' => $test->id
            ])->first();

            if(!$test->user_id && isset($tariffUser) && !$testUser)
            {
                $this->userTariffTestDao->createUserTariffTest($this->user(), $test, $tariffUser->tariff);
            }

            if(isset($tariffUser) && !$testUser)
            {
                $this->userTariffCategoryDao->createUserTariffCategory($this->user(), $test->category_id, $tariffUser->tariff);
            }

            $this->eventService->createEvent(
                Event::EVENT_TYPE_PROJECT_TEST,
                Event::EVENT_SUB_TYPE_APPEND,
                $this->user()->id,
                $projectTest->test_id,
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
        /** @var ProjectTest $projectTest */
        $projectTest = ProjectTest::byId($request->projectTestId);
        if ($projectTest->project->project_status_id != ProjectStatus::Open)
        {
            return $this->jsonError(trans('projects.notificationErrorProjectClosed'));
        }
        if (!$projectTest || $projectTest->test->isQuestionnaire())
        {
            return $this->jsonError();
        }
        $project = $projectTest->project;
        if (!$project || $this->user()->cannot('delete', $project))
        {
            return $this->responsePermissionsDenied();
        }
        $this->eventService->createEvent(
            Event::EVENT_TYPE_PROJECT_TEST,
            Event::EVENT_SUB_TYPE_DELETE,
            $this->user()->id,
            $projectTest->id
        );
        $this->projectService->deleteProjectTest($projectTest);
        return $this->json(
            $this->projectService->firstWithData($project->id)
        );
    }


    private function checkingTariffRestrictions(Test $test)
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

            if (!$tariffUser->tariff->is_unlimited_tariff_tests)
            {
                $userTariffTestsCount = $this->userTariffTestDao->getTestsCountByUser($this->user());
                if ($userTariffTestsCount >= $tariffUser->tariff->tariff_tests_count)
                {
                    return $this->jsonError(trans('tariffs.noTariffForTest'));
                }
            }

            if (!$tariffUser->tariff->is_unlimited_categories)
            {
                $userTariffCategories = $this->userTariffCategoryDao->getCategoriesCountByUser($this->user());
                if ($userTariffCategories >= $tariffUser->tariff->categories_count)
                {
                    $tariffCategory = $this->userTariffCategoryDao->getUserTariffCategory($this->user(), $test->category_id);
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
