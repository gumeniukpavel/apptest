<?php

namespace App\Db\Service;

use App\Constant\OrderType;
use App\Db\Entity\Candidate;
use App\Db\Entity\Category;
use App\Db\Entity\Event;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectCandidate;
use App\Db\Entity\ProjectQuestionnaire;
use App\Db\Entity\ProjectStatus;
use App\Db\Entity\ProjectTest;
use App\Db\Entity\User;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateRequest;
use App\Service\AuthService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class ProjectDao
{
    private AuthService $authService;
    protected ProjectCandidateDao $projectCandidateService;
    protected TestDao $testService;
    protected EventDao $eventDao;
    private TagsDao $tagsDao;

    public function __construct(
        AuthService $authService,
        ProjectCandidateDao $projectCandidateService,
        TestDao $testDao,
        EventDao $eventDao,
        TagsDao $tagsDao
    )
    {
        $this->authService = $authService;
        $this->projectCandidateService = $projectCandidateService;
        $this->testService = $testDao;
        $this->eventDao = $eventDao;
        $this->tagsDao = $tagsDao;
    }
    public function createNew(User $user, CreateProjectRequest $request): Project
    {
        $emptyProject = new Project();
        $emptyProject->name = $request->name;
        $emptyProject->finish_date = $request->finishDate;
        $emptyProject->created_at = Carbon::now();
        $emptyProject->description = $request->description;
        $emptyProject->project_status_id = ProjectStatus::Open;
        $emptyProject->user_id = $user->id;
        $emptyProject->save();

        $this->createProjectTags($emptyProject, $user, $request->tags);
        return $emptyProject;
    }

    /** @returns Project | null */
    public function firstWithData(int $id) : ?Model
    {
        return Project::query()
            ->with('projectCandidates', function (HasMany $builder) {
                $builder->with('testResults');
                $builder->with('questionnaireResults');
            })
            ->with('projectTests')
            ->with('projectQuestionnaires')
            ->where('id', $id)
            ->first();
    }

    public function updateEntity(UpdateRequest $request, Project $project) : Project
    {
        $user = $this->authService->getUser();
        $project->name = $request->name;
        $project->description = $request->description;
        $project->project_status_id = $request->statusId;
        $project->finish_date = Carbon::createFromTimestamp($request->finishDate);

        $this->createProjectTags($project, $user, $request->tags);
        return $project;
    }

    private function createProjectTags(Project $project, User $user, ?array $tags)
    {
        if ($tags)
        {
            $this->tagsDao->clearProjectTags($project);
            foreach ($tags as $tagName)
            {
                $tag = $this->tagsDao->searchByNameAndUser($tagName, $user);

                if ($tag)
                {
                    $this->tagsDao->createProjectTag($project, $tag);
                }
                else
                {
                    $tag = $this->tagsDao->createTagByUser($tagName, $user);
                    $this->tagsDao->createProjectTag($project, $tag);
                }
            }
        }
    }

    /** @returns Category | null */
    public function getCategoryById(int $id) : ?Model
    {
        return Category::query()
            ->where([
                'id' => $id
            ])
            ->first();
    }

    public function getSearchQuery(
        User $user,
        $createDateStart,
        $createDateEnd,
        $finishDateStart,
        $finishDateEnd,
        $statusId,
        $levelId,
        $searchString,
        $orderType,
        ?array $tags
    ): Builder
    {
        $builder = $this->getFilteredListQuery(
            $user,
            $createDateStart,
            $createDateEnd,
            $finishDateStart,
            $finishDateEnd,
            $statusId,
            $levelId,
            $searchString,
            $tags
        );

        $builder->where([
            'user_id' => $user->id
        ]);

        if ($orderType)
        {
            switch ($orderType)
            {
                case OrderType::$CreatedAtAsc->getValue():
                    $builder->orderBy('created_at', 'asc');
                    break;

                case OrderType::$CreatedAtDesc->getValue():
                    $builder->orderBy('created_at', 'desc');
                    break;

                case OrderType::$NameAsc->getValue():
                    $builder->orderBy('name', 'asc');
                    break;

                case OrderType::$NameDesc->getValue():
                    $builder->orderBy('name', 'desc');
                    break;
            }
        }
        else
        {
            $builder->orderByDesc('created_at');
        }

        return $builder;
    }

    public function getFilteredListQuery(
        User $user,
        ?Carbon $getCreateDateStart,
        ?Carbon $getCreateDateEnd,
        ?int $finishDateStart,
        ?int $finishDateEnd,
        ?int $statusId,
        ?int $levelId,
        ?string $searchString,
        ?array $tags
    ) : Builder
    {
        return Project::query()
            ->with('customer')
            ->with('tests')
            ->with('status')
            ->withCount([
                'testResults as testResultsCount',
                'projectCandidates as projectCandidatesCount'
            ])
            ->when(!empty($getCreateDateStart), function (Builder $builder) use ($getCreateDateStart) {
                $builder->where('created_at', '>=', $getCreateDateStart);
            })
            ->when(!empty($getCreateDateEnd), function (Builder $builder) use ($getCreateDateEnd) {
                $builder->where('created_at', '<=', $getCreateDateEnd);
            })
            ->when(!empty($finishDateStart), function (Builder $builder) use ($finishDateStart) {
                $builder->where('finish_date', '>=', Carbon::createFromTimestamp($finishDateStart));
            })
            ->when(!empty($finishDateEnd), function (Builder $builder) use ($finishDateEnd) {
                $builder->where('finish_date', '<=', Carbon::createFromTimestamp($finishDateEnd));
            })
            ->when(!empty($statusId), function (Builder $builder) use ($statusId) {
                $builder->where('project_status_id', $statusId);
            })
            ->when(!empty($searchString), function (Builder $builder) use ($searchString) {
                $builder->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $searchString . '%', 'UTF-8')]);
            })
            ->when(!empty($tags), function (Builder $builder) use ($tags)
            {
                $builder->whereIn('projects.id', function ($query) use ($tags)
                {
                    $query->select('project_tags.project_id')
                        ->from('project_tags')
                        ->whereIn('project_tags.tag_id', $tags);
                });
            });
    }

    public function deleteProject(Project $project)
    {
        foreach ($project->projectCandidates as $projectCandidate)
        {
            $this->projectCandidateService->deleteProjectCandidate($projectCandidate);
        }
        foreach ($project->projectTests as $projectTest)
        {
            $this->eventDao->markEventAsDeleted($projectTest->id, Event::EVENT_TYPE_PROJECT_TEST);
            $projectTest->delete();
        }
        $this->eventDao->markEventAsDeleted($project->id, Event::EVENT_TYPE_PROJECT);
        $project->delete();
    }

    public function deleteProjectTest(ProjectTest $projectTest)
    {
        $projectCandidates = $projectTest->projectCandidates;
        foreach ($projectCandidates as $projectCandidate)
        {
            $this->eventDao->markEventAsDeleted($projectCandidate->id, Event::EVENT_TYPE_PROJECT_CANDIDATE);
            $this->projectCandidateService->deleteProjectCandidate($projectCandidate);
        }
        $this->eventDao->markEventAsDeleted($projectTest->id, Event::EVENT_TYPE_PROJECT_TEST);
        $projectTest->delete();
    }

    public function deleteProjectQuestionnaire(ProjectQuestionnaire $projectQuestionnaire)
    {
        $projectCandidates = $projectQuestionnaire->projectCandidates;
        foreach ($projectCandidates as $projectCandidate)
        {
            $this->eventDao->markEventAsDeleted($projectCandidate->id, Event::EVENT_TYPE_PROJECT_CANDIDATE);
            $this->projectCandidateService->deleteProjectCandidate($projectCandidate);
        }
        $this->eventDao->markEventAsDeleted($projectQuestionnaire->id, Event::EVENT_TYPE_PROJECT_QUESTIONNAIRE);
        $projectQuestionnaire->delete();
    }

    public function getFavoriteProjectCandidateQuery(Project $project): Builder
    {
        $candidatesId = $project->candidates()
            ->where('project_candidates.is_favorite', true)
            ->select('candidates.id')
            ->pluck('candidates.id', 'candidates.id');

        $builder = Candidate::query()
            ->whereIn('id', $candidatesId)
            ->with('testResults', function (HasManyThrough $query) use ($project){
                $query->where('project_candidates.project_id', $project->id);
            })
            ->with('questionnaireResults', function (HasManyThrough $query) use ($project){
                $query->where('project_candidates.project_id', $project->id);
            });

        return $builder;
    }

    public function removeFromFavorite(Project $project, Candidate $candidate)
    {
        ProjectCandidate::query()->where([
            'project_id' => $project->id,
            'candidate_id' => $candidate->id
        ])->update([
            'is_favorite' => false
        ]);
    }
}
