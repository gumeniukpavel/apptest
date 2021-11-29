<?php

namespace App\Db\Service;

use App\Constant\AccountType;
use App\Constant\AppMode;
use App\Constant\OrderType;
use App\Constant\TestType;
use App\Db\Entity\Event;
use App\Db\Entity\Tariff;
use App\Db\Entity\TariffUser;
use App\Db\Entity\Test;
use App\Db\Entity\User;
use App\Http\Requests\Test\GetListStatisticRequest;
use App\Service\AuthService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class TestDao
{
    private string $mode;

    protected AuthService $authService;
    protected TariffUserDao $tariffUserDao;
    protected UserTariffTestDao $userTariffTestDao;
    protected ProjectCandidateDao $projectCandidateService;
    private TagsDao $tagsDao;
    protected EventDao $eventDao;

    public function __construct(
        AuthService $authService,
        UserTariffTestDao $userTariffTestDao,
        TariffUserDao $tariffUserDao,
        ProjectCandidateDao $projectCandidateService,
        TagsDao $tagsDao,
        EventDao $eventDao
    )
    {
        $this->authService = $authService;
        $this->userTariffTestDao = $userTariffTestDao;
        $this->tariffUserDao = $tariffUserDao;
        $this->projectCandidateService = $projectCandidateService;
        $this->eventDao = $eventDao;
        $this->tagsDao = $tagsDao;
        $this->mode = config('app.mode');
    }


    public function getOne(int $id): ?Test
    {
        /** @var Test $test */
        $test = Test::query()->withCount('questions')->where('id', $id)->first();
        return $test;
    }

    public function firstWithData(int $id): ?Test
    {
        $user = $this->authService->getUser();
        $testBuilder = Test::with('level')
            ->with('category')
            ->with('tags')
            ->withCount('questions')
            ->where('id', $id);

        if (!$user->is_verified)
        {
            /** @var Test $test */
            $test = $testBuilder->withCount('questions')->with('questions', function (HasMany $builder) {
                $builder->limit(10);
            })->first();
        }
        else
        {
            /** @var Test $test */
            $test = $testBuilder->withCount('questions')->with('questions')->first();
        }

        if (!$test->user_id)
        {
            /** @var TariffUser $tariffUser */
            $tariffUser = $this->tariffUserDao->getActiveByUser($user);

            if ($tariffUser)
            {
                $test->makeVisibleIf(
                    $user->account_type == AccountType::$LegalEntity->getValue() &&
                    $tariffUser->tariff_id != Tariff::EconomyTariff,
                    'questions'
                );
            }
        }
        else
        {
            if ($test->user->id == $user->id)
            {
                /** @var Test $test */
                $test = $testBuilder->withCount('questions')->with('questions')->first();
            }
            $test->makeVisible('questions');
        }

        return $test;
    }

    public function firstQuestionnaireWithData(int $id): ?Test
    {
        /** @var Test $questionnaire */
        $questionnaire = Test::with('tags')
            ->withCount('questions')
            ->where('id', $id)
            ->first();

        return $questionnaire;
    }

    public function getSearchQuery(
        User $user, ?int $categoryId,
        ?int $levelId,
        ?int $projectId,
        ?string $searchString,
        ?string $orderType
    ): Builder
    {
        $builder = $this->getFilteredListQuery($categoryId, $levelId, $projectId, $searchString);
        $builder
            ->withCount('questions')
            ->where([
                'user_id' => $user->id,
                'type' => TestType::$Test->getValue()
            ]);

        if ($orderType)
        {
            $this->getOrderType($orderType, $builder);
        }
        else
        {
            $builder->orderByDesc('created_at');
        }

        return $builder;
    }

    public function getAllSearchQuery(
        User $user,
        ?int $categoryId,
        ?int $levelId,
        ?int $projectId,
        ?string $searchString,
        ?string $orderType
    ): Builder
    {
        $builder = $this->getFilteredListQuery($categoryId, $levelId, $projectId, $searchString);

        $builder
            ->withCount('questions')
            ->where([
            'type' => TestType::$Test->getValue()
        ]);

        if ($orderType)
        {
            $this->getOrderType($orderType, $builder);
        }
        else
        {
            $builder->orderByDesc('created_at');
        }

        return $builder;
    }

    public function getTestsForTariff(
        ?int $categoryId,
        ?int $levelId,
        ?int $projectId,
        ?string $searchString,
        ?string $orderType
    ): Builder
    {
        // TODO: Some tariff validation
        $builder = $this->getFilteredListQuery($categoryId, $levelId, $projectId, $searchString);
        $builder
            ->withCount('questions')
            ->where([
                'user_id' => null,
                'type' => TestType::$Test->getValue()
            ]);

        if ($orderType)
        {
            $this->getOrderType($orderType, $builder);
        }
        else
        {
            $builder->orderByDesc('created_at');
        }

        return $builder;
    }

    public function getQuestionnaireList(
        User $user,
        ?string $searchString,
        ?int $projectId,
        ?string $orderType,
        ?array $tags
    ): Builder
    {
        $builder = $this->getFilteredListQueryForQuestionnaire($searchString, $projectId, $tags);
        $builder
            ->withCount('questions')
            ->where([
                'user_id' => $user->id,
                'type' => TestType::$Questionnaire->getValue()
            ]);

        if ($orderType)
        {
            $this->getOrderType($orderType, $builder);
        }
        else
        {
            $builder->orderByDesc('created_at');
        }

        return $builder;
    }

    private function getFilteredListQuery(?int $categoryId, ?int $levelId, ?int $projectId, ?string $searchString): Builder
    {
        $query = Test::query()
            ->select([
                'tests.*',
            ])
            ->with('level')
            ->with('category')
            ->withCount('questions')
            ->when(!empty($categoryId), function(Builder $builder) use ($categoryId) {
                $builder->where('category_id', $categoryId);
            })
            ->when(!empty($levelId), function(Builder $builder) use ($levelId) {
                $builder->where('level_id', $levelId);
            })
            ->when(!empty($projectId), function(Builder $builder) use ($projectId) {
                $builder->whereNotIn('id', function ($builder) use ($projectId) {
                    $builder->select('project_tests.test_id')
                        ->from('project_tests')
                        ->where('project_tests.project_id', $projectId);
                });
            })
            ->when(!empty($searchString), function(Builder $builder) use ($searchString) {
                $builder->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $searchString . '%', 'UTF-8')]);
            });

        $user = $this->authService->getUser();
        $tariffUser = $this->tariffUserDao->getActiveByUser($user);
        if ($tariffUser)
        {
            $userTariffTestsCount = $this->userTariffTestDao->getTestsCountByUser($user);
            if ($userTariffTestsCount >= $tariffUser->tariff->tariff_tests_count)
            {
                $query->addSelect(
                    DB::raw(
                        'if
                            (tests.user_id is NULL, if (
                                (select id
                                from user_tariff_tests
                                where test_id=tests.id
                                and user_id=?
                                and tariff_id=? limit 1) is NULL,
                                0,
                                1
                                ),
                                1)
                            as isActiveForTariff'
                    )
                )
                ->setBindings([$user->id, $tariffUser->tariff->id], 'select');
            }
            else
            {
                $query->addSelect(DB::raw('1 as isActiveForTariff'));
            }
        }
        else
        {
            $query->addSelect(DB::raw('1 as isActiveForTariff'));
        }

        return $query;
    }

    public function getTestsStatisticQuery(
        User $user,
        GetListStatisticRequest $request): \Illuminate\Database\Query\Builder
    {
        return DB::table(Test::tableName() . ' AS t')
            ->select([
                't.id',
                't.name',
                't.description',
                't.pass_point_value as passPointValue',
                't.category_id as categoryId',
                't.level_id as levelId',
                't.time_limit as timeLimit',
            ])
            ->addSelect([
                'successParticipants' => function($query) use ($request, $user)
                {
                    $query->select(DB::raw('count(test_results.project_candidate_id)'))
                        ->from('test_results')
                        ->join('project_candidates', 'project_candidates.id', 'test_results.project_candidate_id')
                        ->join('candidates', 'candidates.id', 'project_candidates.candidate_id')
                        ->whereColumn('test_id', 't.id')
                        ->where([
                            'is_passed' => true,
                            'candidates.customer_id' => $user->id
                        ])
                        ->when(isset($request) && isset($request->fromDate), function (\Illuminate\Database\Query\Builder $builder) use ($request)
                        {
                            $builder->where('test_results.updated_at', '>=', Carbon::createFromTimestamp($request->fromDate));
                        })
                        ->when(isset($request) && isset($request->toDate), function (\Illuminate\Database\Query\Builder $builder) use ($request)
                        {
                            $builder->where('test_results.updated_at', '<=', Carbon::createFromTimestamp($request->toDate));
                        })
                        ->groupBy('t.id');
                },
                'allParticipants' => function($query) use ($request, $user)
                {
                    $query->select(DB::raw('count(test_results.project_candidate_id)'))
                        ->from('test_results')
                        ->join('project_candidates', 'project_candidates.id', 'test_results.project_candidate_id')
                        ->join('candidates', 'candidates.id', 'project_candidates.candidate_id')
                        ->whereColumn('test_id', 't.id')
                        ->where([
                            'candidates.customer_id' => $user->id
                        ])
                        ->when(isset($request) && isset($request->fromDate), function (\Illuminate\Database\Query\Builder $builder) use ($request)
                        {
                            $builder->where('test_results.updated_at', '>=', Carbon::createFromTimestamp($request->fromDate));
                        })
                        ->when(isset($request) && isset($request->toDate), function (\Illuminate\Database\Query\Builder $builder) use ($request)
                        {
                            $builder->where('test_results.updated_at', '<=', Carbon::createFromTimestamp($request->toDate));
                        })
                        ->groupBy('t.id');
                }
            ])
            ->when(isset($request) && !empty($request->searchString), function (\Illuminate\Database\Query\Builder $builder) use ($request)
            {
                $builder->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $request->searchString . '%', 'UTF-8')]);
            })
            ->where('type', TestType::$Test->getValue())
            ->where('user_id', $user->id)
            ->orWhere('user_id', null);
    }

    private function getFilteredListQueryForQuestionnaire(?string $searchString, ?int $projectId, ?array $tags): Builder
    {
        return Test::query()
            ->select([
                'tests.*',
            ])
            ->withCount('questions')
            ->with('tags')
            ->when(!empty($tags), function (Builder $builder) use ($tags)
            {
                $builder->whereIn('tests.id', function ($query) use ($tags)
                {
                    $query->select('test_tags.test_id')
                        ->from('test_tags')
                        ->whereIn('test_tags.tag_id', $tags);
                });
            })
            ->when(!empty($projectId), function(Builder $builder) use ($projectId) {
                $builder->whereNotIn('id', function ($builder) use ($projectId) {
                    $builder->select('project_questionnaires.questionnaire_id')
                        ->from('project_questionnaires')
                        ->where('project_questionnaires.project_id', $projectId);
                });
            })
            ->when(!empty($searchString), function(Builder $builder) use ($searchString) {
                $builder->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $searchString . '%', 'UTF-8')]);
            });
    }

    public function deleteTest(Test $test)
    {
        $projectTests = $test->projectTests;
        foreach ($projectTests as $projectTest)
        {
            $projectCandidates = $projectTest->projectCandidates;
            foreach ($projectCandidates as $projectCandidate)
            {
                $this->projectCandidateService->deleteProjectCandidate($projectCandidate);
            }
            $this->eventDao->markEventAsDeleted($projectTest->id, Event::EVENT_TYPE_PROJECT_TEST);
            $projectTest->delete();
        }
        $test->questions()->delete();
        $this->eventDao->markEventAsDeleted($test->id, Event::EVENT_TYPE_TEST);
        $test->delete();
    }

    private function getOrderType(?string $orderType, Builder $builder)
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

    public function createQuestionnaireTags(Test $questionnaire, User $user, ?array $tags)
    {
        if ($tags)
        {
            $this->tagsDao->clearTestTags($questionnaire);
            foreach ($tags as $tagName)
            {
                $tag = $this->tagsDao->searchByNameAndUser($tagName, $user);

                if ($tag)
                {
                    $this->tagsDao->createTestTag($questionnaire, $tag);
                }
                else
                {
                    $tag = $this->tagsDao->createTagByUser($tagName, $user);
                    $this->tagsDao->createTestTag($questionnaire, $tag);
                }
            }
        }
    }
}
