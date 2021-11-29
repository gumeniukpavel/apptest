<?php

namespace App\Db\Service;

use App\Constant\ApprovalRequestStatus;
use App\Constant\OrderType;
use App\Constant\TestType;
use App\Db\Entity\Event;
use App\Db\Entity\Expert;
use App\Db\Entity\ExpertFile;
use App\Db\Entity\ExpertInterviewEvent;
use App\Db\Entity\QuestionnaireApprovalRequest;
use App\Db\Entity\TestApprovalRequest;
use App\Db\Entity\TestResult;
use App\Db\Entity\User;
use App\Http\Requests\Expert\AddRequest;
use App\Http\Requests\Expert\ApprovalInvitationRequest;
use App\Http\Requests\Expert\GetApprovalRequestListRequest;
use App\Http\Requests\Expert\ApprovalResultRequest;
use App\Http\Requests\Expert\UpdateNotesRequest;
use App\Http\Requests\Expert\UpdateRequest;
use App\Service\AuthService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpertDao
{
    private AuthService $authService;
    private TagsDao $tagsDao;
    private StaffLevelDao $staffLevelDao;
    private StaffSpecializationDao $staffSpecializationDao;
    protected EventDao $eventDao;

    public function __construct(
        AuthService $authService,
        TagsDao $tagsDao,
        StaffLevelDao $staffLevelDao,
        StaffSpecializationDao $staffSpecializationDao,

        EventDao $eventDao
    )
    {
        $this->authService = $authService;
        $this->tagsDao = $tagsDao;
        $this->eventDao = $eventDao;
        $this->staffLevelDao = $staffLevelDao;
        $this->staffSpecializationDao = $staffSpecializationDao;
    }

    public function searchQuery(
        User $user,
        ?string $searchString,
        ?int $careerStartYearFrom,
        ?int $careerStartYearTo,
        ?int $ageFromTimestamp,
        ?int $ageToTimestamp,
        ?string $orderType,
        ?array $tags,
        ?int $staffLevel,
        ?int $staffSpecialization
    ): Builder
    {
        $builder = Expert::query()
            ->with(['staffLevel', 'staffSpecialization'])
            ->withCount('ExpertInterviewEvent')
            ->where('customer_id', $user->id)
            ->when(!empty($searchString), function (Builder $builder) use ($searchString)
            {
                $builder->whereIn('experts.id', function ($query) use ($searchString)
                {
                    $query->select('experts.id')
                        ->from('experts')
                        ->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $searchString . '%', 'UTF-8')])
                        ->orWhereRaw('UPPER(`surname`) LIKE ?', [mb_strtoupper('%' . $searchString . '%', 'UTF-8')])
                        ->orWhereRaw('UPPER(`middle_name`) LIKE ?', [mb_strtoupper('%' . $searchString . '%', 'UTF-8')]);
                });
            })
            ->when(!empty($careerStartYearFrom), function (Builder $builder) use ($careerStartYearFrom)
            {
                $builder->where('career_start_year', '>=', $careerStartYearFrom);
            })
            ->when(!empty($careerStartYearTo), function (Builder $builder) use ($careerStartYearTo)
            {
                $builder->where('career_start_year', '<=', $careerStartYearTo);
            })
            ->when(!empty($ageFromTimestamp), function (Builder $builder) use ($ageFromTimestamp)
            {
                $builder->where('birth_date', '>=', Carbon::createFromTimestamp($ageFromTimestamp));
            })
            ->when(!empty($ageToTimestamp), function (Builder $builder) use ($ageToTimestamp)
            {
                $builder->where('birth_date', '<=', Carbon::createFromTimestamp($ageToTimestamp));
            })
            ->when(!empty($tags), function (Builder $builder) use ($tags)
            {
                $builder->whereIn('id', function ($query) use ($tags)
                {
                    $query->select('expert_tags.expert_id')
                        ->from('expert_tags')
                        ->whereIn('expert_tags.tag_id', $tags);
                });
            })
            ->when(!empty($staffLevel), function (Builder $builder) use ($staffLevel)
            {
                $builder->where('staff_level_id', '=', $staffLevel);
            })
            ->when(!empty($staffSpecialization), function (Builder $builder) use ($staffSpecialization)
            {
                $builder->where('staff_specialization_id', '=', $staffSpecialization);
            });

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

    public function deleteExpert(Expert $expert)
    {
        $testApprovalRequestsByExpert = $this->getTestApprovalRequestsByExpert($expert);
        foreach ($testApprovalRequestsByExpert as $testApprovalRequestByExpert)
        {
            $this->eventDao->markEventAsDeleted($testApprovalRequestByExpert->id, Event::EVENT_TYPE_TEST_APPROVAL_REQUESTS);
            $testApprovalRequestByExpert->delete();
        }
        $questionnaireApprovalRequestsByExpert = $this->getQuestionnaireApprovalRequestsByExpert($expert);
        foreach ($questionnaireApprovalRequestsByExpert as $questionnaireApprovalRequestByExpert)
        {
            $this->eventDao->markEventAsDeleted($questionnaireApprovalRequestByExpert->id, Event::EVENT_TYPE_QUESTIONNAIRE_APPROVAL_REQUESTS);
            $questionnaireApprovalRequestByExpert->delete();
        }
        $expertInterviewEvents = $this->getExpertInterviewEvent($expert);
        foreach ($expertInterviewEvents as $expertInterviewEvent)
        {
            $this->eventDao->markEventAsDeleted($expertInterviewEvent->id, Event::EVENT_TYPE_EXPERT_INTERVIEW_INVITATION);
            $expertInterviewEvent->delete();
        }
        $this->eventDao->markEventAsDeleted($expert->id, Event::EVENT_TYPE_EXPERT);
        $this->tagsDao->clearExpertTags($expert);
        $expert->delete();
    }

    /** @returns Expert | null */
    public function firstWithData(int $id): ?Expert
    {
        /** @var Expert $expert */
        $expert = Expert::query()
            ->with('image')
            ->with(['documents', 'staffLevel', 'staffSpecialization'])
            ->where('id', $id)
            ->first();

        return $expert;
    }

    public function createExpertTags(Expert $expert, User $user, ?array $tags)
    {
        if ($tags)
        {
            $this->tagsDao->clearExpertTags($expert);
            foreach ($tags as $tagName)
            {
                $tag = $this->tagsDao->searchByNameAndUser($tagName, $user);

                if ($tag)
                {
                    $this->tagsDao->createExpertTag($expert, $tag);
                }
                else
                {
                    $tag = $this->tagsDao->createTagByUser($tagName, $user);
                    $this->tagsDao->createExpertTag($expert, $tag);
                }
            }
        }
    }

    private function createExpertStaffLevel(Expert $expert, User $user, ?string $staffLevelName)
    {
        if ($staffLevelName)
        {
            $staffLevel = $this->staffLevelDao->searchByNameAndUser($staffLevelName, $user);

            if ($staffLevel)
            {
                $this->staffLevelDao->saveExpertStaffLevel($expert, $staffLevel);
            }
            else
            {
                $staffLevel = $this->staffLevelDao->createStaffLevelByUser($staffLevelName, $user);
                $this->staffLevelDao->saveExpertStaffLevel($expert, $staffLevel);
            }
        }
    }

    private function createExpertStaffSpecialization(Expert $expert, User $user, ?string $staffSpecializationName)
    {
        if ($staffSpecializationName)
        {
            $staffSpecialization = $this->staffSpecializationDao->searchByNameAndUser($staffSpecializationName, $user);

            if ($staffSpecialization)
            {
                $this->staffSpecializationDao->saveExpertStaffSpecialization($expert, $staffSpecialization);
            }
            else
            {
                $staffSpecialization = $this->staffSpecializationDao->createStaffSpecializationByUser($staffSpecializationName, $user);
                $this->staffSpecializationDao->saveExpertStaffSpecialization($expert, $staffSpecialization);
            }
        }
    }

    public function addNew(AddRequest $request, ?ExpertFile $expertImage): ?Expert
    {
        $expert = new Expert();
        DB::transaction(function () use ($request, $expert, $expertImage)
        {
            $user = $this->authService->getUser();
            $expert->customer_id = $user->id;
            $expert->image_id = $expertImage ? $expertImage->id : null;
            $expert = $request->updateEntity($expert);
            $expert->save();

            $this->createExpertStaffLevel($expert, $user, $request->staffLevel);
            $this->createExpertStaffSpecialization($expert, $user, $request->staffSpecialization);
            $this->createExpertTags($expert, $user, $request->tags);
        });
        return $expert;
    }

    public function update(UpdateRequest $request, Expert $expert, ?ExpertFile $expertImage): ?Expert
    {
        DB::transaction(function () use ($request, $expert, $expertImage)
        {
            $user = $this->authService->getUser();
            $expert->image_id = $expertImage ? $expertImage->id : null;
            $expert = $request->updateEntity($expert);
            $expert->pdf_data_url = null;
            $expert->save();

            $this->createExpertStaffLevel($expert, $user, $request->staffLevel);
            $this->createExpertStaffSpecialization($expert, $user, $request->staffSpecialization);
            $this->createExpertTags($expert, $user, $request->tags);
        });
        return $expert;
    }

    public function createApprovalRequest(ApprovalInvitationRequest $request, TestResult $testResult)
    {
        /** @var User $user */
        $user = $this->authService->user();
        if ($testResult->test->isTest())
        {
            $entity = new TestApprovalRequest();
            $entity->test_result_id = $testResult->id;
        }
        else
        {
            $entity = new QuestionnaireApprovalRequest();
            $entity->questionnaire_result_id = $testResult->id;
        }
        $entity->status = ApprovalRequestStatus::$Pending->getValue();
        $entity->user_id = $user->id;
        $entity->expert_id = $request->expertId;
        $entity->candidate_id = $testResult->candidate->id;
        $entity->access_token_verify = Str::uuid()->toString();
        $entity->access_token_cancel = Str::uuid()->toString();
        $entity->save();

        return $entity;
    }

    public function getListApprovalRequest(GetApprovalRequestListRequest $request): Builder
    {
        /** @var TestResult $testResult */
        $testResult = TestResult::byId($request->testResultId);
        if ($testResult->test->isTest())
        {
            $approvalRequests = TestApprovalRequest::query()
                ->where('test_result_id', $request->testResultId)
                ->where('status',ApprovalRequestStatus::$Approved->getValue())
                ->with(['candidate', 'expert']);
        }
        else
        {
            $approvalRequests = QuestionnaireApprovalRequest::query()
                ->where('questionnaire_result_id', $request->testResultId)
                ->where('status', ApprovalRequestStatus::$Approved->getValue())
                ->with(['candidate', 'expert']);
        }

        return $approvalRequests;
    }

    public function getExpertApprovalResults(Expert $expert, TestType $testType, ?string $orderType)
    {
        if ($testType === TestType::$Test)
        {
            $builder = TestApprovalRequest::query()
                ->with(['candidate', 'testResult'])
                ->where('expert_id', $expert->id);
        }
        else
        {
            $builder = QuestionnaireApprovalRequest::query()
                ->with(['candidate', 'questionnaireResult'])
                ->where('expert_id', $expert->id);
        }


        if ($orderType)
        {
            switch ($orderType)
            {
                case OrderType::$CreatedAtAsc->getValue():
                    $builder->orderBy('updated_at', 'asc');
                    break;

                case OrderType::$CreatedAtDesc->getValue():
                    $builder->orderBy('updated_at', 'desc');
                    break;
            }
        }
        else
        {
            $builder->orderByDesc('created_at');
        }

        return $builder;
    }

    public function getTestApprovalRequestByToken(ApprovalResultRequest $request): ?TestApprovalRequest
    {
        if ($request->status == ApprovalRequestStatus::$Approved->getValue())
        {
            /** @var TestApprovalRequest $testApprovalRequests */
            $testApprovalRequests = TestApprovalRequest::query()
                ->with('testResult', function (BelongsTo $builder) {
                    $builder->with('answers', function (HasMany $builder) {
                        $builder->with('answer');
                        $builder->with('question');
                    });
                })
                ->with('candidate', function (BelongsTo $builder) {
                    $builder->select('id', 'name', 'surname', 'middle_name');
                })
                ->where('access_token_verify', $request->token)
                ->whereIn('status', [
                    ApprovalRequestStatus::$Pending->getValue(),
                    ApprovalRequestStatus::$Approved->getValue(),
                ])
                ->first();
        }
        else
        {
            /** @var TestApprovalRequest $testApprovalRequests */
            $testApprovalRequests = TestApprovalRequest::query()
                ->with('testResult', function (BelongsTo $builder) {
                    $builder->with('answers', function (HasMany $builder) {
                        $builder->with('answer');
                        $builder->with('question');
                    });
                })
                ->where('access_token_cancel', $request->token)
                ->where('status', ApprovalRequestStatus::$Pending->getValue())
                ->first();
        }

        return $testApprovalRequests;
    }

    public function getQuestionnaireApprovalRequestByToken(ApprovalResultRequest $request): ?QuestionnaireApprovalRequest
    {
        if ($request->status == ApprovalRequestStatus::$Approved->getValue())
        {
            /** @var QuestionnaireApprovalRequest $questionnaireApprovalRequests */
            $questionnaireApprovalRequests = QuestionnaireApprovalRequest::query()
                ->with('questionnaireResult', function (BelongsTo $builder) {
                    $builder->with('answers', function (HasMany $builder) {
                        $builder->with('answer');
                        $builder->with('question');
                    });
                })
                ->with('candidate', function (BelongsTo $builder) {
                    $builder->select('id', 'name', 'surname', 'middle_name');
                })
                ->where('access_token_verify', $request->token)
                ->whereIn('status', [
                    ApprovalRequestStatus::$Pending->getValue(),
                    ApprovalRequestStatus::$Approved->getValue(),
                ])
                ->first();
        }
        else
        {
            /** @var QuestionnaireApprovalRequest $questionnaireApprovalRequests */
            $questionnaireApprovalRequests = QuestionnaireApprovalRequest::query()
                ->with('questionnaireResult', function (BelongsTo $builder) {
                    $builder->with('answers', function (HasMany $builder) {
                        $builder->with('answer');
                        $builder->with('question');
                    });
                })
                ->where('access_token_cancel', $request->token)
                ->where('status', ApprovalRequestStatus::$Pending->getValue())
                ->first();
        }

        return $questionnaireApprovalRequests;
    }

    public function getTestApprovalRequestsByExpert(Expert $expert)
    {
        return TestApprovalRequest::query()
            ->where('expert_id', $expert->id)
            ->get();
    }

    public function getQuestionnaireApprovalRequestsByExpert(Expert $expert)
    {
        return QuestionnaireApprovalRequest::query()
            ->where('expert_id', $expert->id)
            ->get();
    }

    public function updateNote(UpdateNotesRequest $request, Expert $expert): ?Expert
    {
        $expert->notes = $request->notes;
        $expert->save();
        return $expert;
    }

    public function getExpertInterviewEvent(Expert $expert)
    {
        return ExpertInterviewEvent::query()
            ->where('expert_id', $expert->id)
            ->get();
    }
}
