<?php

namespace App\Http\Controllers\Api\Project;

use App\Db\Entity\Candidate;
use App\Db\Entity\Category;
use App\Db\Entity\Event;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectStatus;
use App\Db\Entity\User;
use App\Db\Service\EventDao;
use App\Db\Service\ProjectDao;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IdRequest;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\DeleteRequest;
use App\Http\Requests\Project\GetFavoritesRequest;
use App\Http\Requests\Project\RemoveFromFavoriteRequest;
use App\Http\Requests\Project\UpdateRequest;
use App\Http\Requests\Project\GetListRequest;
use App\Http\Resources\PaginationResource;
use App\Service\AuthService;
use Carbon\Carbon;

class ProjectController extends BaseController
{
    protected ProjectDao $projectService;
    protected EventDao $eventService;

    public function __construct(
        ProjectDao $projectDao,
        EventDao $eventService,
        AuthService $authService
    )
    {
        parent::__construct($authService);
        $this->projectService = $projectDao;
        $this->eventService = $eventService;
    }

    public function index(GetListRequest $request)
    {
        $searchQuery = $this->projectService->getSearchQuery(
            $this->user(),
            $request->getCreateDateStart(),
            $request->getCreateDateEnd(),
            $request->finishDateStart,
            $request->finishDateEnd,
            $request->statusId,
            $request->levelId,
            $request->searchString,
            $request->orderType,
            $request->tags
        );
        return $this->json(
            new PaginationResource($searchQuery, $request->page)
        );
    }

    public function show($id)
    {
        /** @var Project $project */
        $project = Project::query()
            ->where('id', $id)
            ->first();
        if (!$project || $this->user()->cannot('view', $project)) {
            return $this->responsePermissionsDenied();
        }
        return $this->json(
            $this->projectService->firstWithData($project->id)
        );
    }

    /**
     * Для клиента нам необходимо просто создать новый проект без вопросов
     */
    public function createNew(CreateProjectRequest $request)
    {
        if ($this->user()->cannot('create', Project::class)) {
            return $this->responsePermissionsDenied();
        }
        $project = $this->projectService->createNew($this->user(), $request);

        $this->eventService->createEvent(Event::EVENT_TYPE_PROJECT, Event::EVENT_SUB_TYPE_CREATE, $this->user()->id, $project->id);
        return $this->json(
            $this->projectService->firstWithData($project->id),
            201
        );
    }

    public function update(UpdateRequest $request)
    {
        /** @var Project $project */
        $project = Project::query()->where('id', $request->id)->first();

        if (!$project || $this->user()->cannot('update', $project)) {
            return $this->responsePermissionsDenied();
        }
        $project = $this->projectService->updateEntity($request, $project);
        $project->save();
        $this->eventService->createEvent(Event::EVENT_TYPE_PROJECT, Event::EVENT_SUB_TYPE_UPDATE, $this->user()->id, $project->id);

        return $this->json(
            $this->projectService->firstWithData($project->id)
        );
    }

    public function delete(DeleteRequest $request)
    {
        /** @var Project $project */
        $project = Project::query()->where('id', $request->id)->first();
        if (!$project || $this->user()->cannot('delete', $project)) {
            return $this->responsePermissionsDenied();
        }
        $this->eventService->createEvent(Event::EVENT_TYPE_PROJECT, Event::EVENT_SUB_TYPE_DELETE, $this->user()->id, $project->id);
        $this->projectService->deleteProject($project);
        return $this->json();
    }

    public function closeProject(IdRequest $request)
    {
        /** @var Project $project */
        $project = Project::byId($request->id);
        if (!$project || $this->user()->cannot('update', $project)) {
            return $this->responsePermissionsDenied();
        }
        $project->project_status_id = ProjectStatus::Closed;
        $project->save();
        $this->eventService->createEvent(
            Event::EVENT_TYPE_PROJECT,
            Event::EVENT_SUB_TYPE_CLOSE_PROJECT,
            $this->user()->id,
            $project->id
        );
        return $this->json();
    }

    public function openProject(IdRequest $request)
    {
        /** @var Project $project */
        $project = Project::byId($request->id);
        if (!$project || $this->user()->cannot('update', $project)) {
            return $this->responsePermissionsDenied();
        }
        $project->project_status_id = ProjectStatus::Open;
        $project->save();
        $this->eventService->createEvent(
            Event::EVENT_TYPE_PROJECT,
            Event::EVENT_SUB_TYPE_OPEN_PROJECT,
            $this->user()->id,
            $project->id
        );
        return $this->json();
    }

    public function actionGetFavorites(GetFavoritesRequest $request)
    {
        /** @var Project $project */
        $project = Project::query()
            ->where('id', $request->projectId)
            ->first();
        if (!$project || $this->user()->cannot('view', $project)) {
            return $this->responsePermissionsDenied();
        }

        $query = $this->projectService->getFavoriteProjectCandidateQuery($project);

        return $this->json(
            new PaginationResource($query, $request->page, 100)
        );
    }

    public function actionRemoveFromFavorite(RemoveFromFavoriteRequest $request)
    {
        /** @var Project $project */
        $project = Project::query()
            ->where('id', $request->projectId)
            ->first();
        if (!$project || $this->user()->cannot('update', $project)) {
            return $this->responsePermissionsDenied();
        }
        /** @var Candidate $candidate */
        $candidate = Candidate::query()
            ->where('id', $request->candidateId)
            ->first();

        $this->projectService->removeFromFavorite($project, $candidate);

        return $this->json(
            $project
        );
    }
}
