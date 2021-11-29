<?php

namespace App\Db\Service;

use App\Constant\CandidateType;
use App\Constant\OrderType;
use App\Constant\TestType;
use App\Db\Entity\Candidate;
use App\Db\Entity\CandidateFile;
use App\Db\Entity\Event;
use App\Db\Entity\TestResult;
use App\Db\Entity\User;
use App\Http\Requests\Candidate\AddRequest;
use App\Http\Requests\Candidate\UpdateNotesRequest;
use App\Http\Requests\Candidate\UpdateRequest;
use App\Service\AuthService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CandidateDao
{
    private AuthService $authService;
    private ProjectCandidateDao $projectCandidateDao;
    private TagsDao $tagsDao;
    private StaffLevelDao $staffLevelDao;
    private StaffSpecializationDao $staffSpecializationDao;
    protected EventDao $eventDao;

    public function __construct(
        AuthService $authService,
        ProjectCandidateDao $projectCandidateDao,
        TagsDao $tagsDao,
        StaffLevelDao $staffLevelDao,
        StaffSpecializationDao $staffSpecializationDao,
        EventDao $eventDao
    )
    {
        $this->authService = $authService;
        $this->projectCandidateDao = $projectCandidateDao;
        $this->tagsDao = $tagsDao;
        $this->staffLevelDao = $staffLevelDao;
        $this->staffSpecializationDao = $staffSpecializationDao;
        $this->eventDao = $eventDao;
    }

    public function searchQuery(
        User $user,
        CandidateType $type,
        ?string $searchString,
        ?int $careerStartYearFrom,
        ?int $careerStartYearTo,
        ?int $ageFromTimestamp,
        ?int $ageToTimestamp,
        ?int $isOnlyFavoriteCandidates,
        ?string $orderType,
        ?array $tags,
        ?int $staffLevel,
        ?int $staffSpecialization
    ): Builder
    {
        $builder = Candidate::query()
            ->with(['staffLevel', 'staffSpecialization', 'companyPosition'])
            ->withCount(['testResults', 'questionnaireResults', 'userEvent'])
            ->where('type', $type->getValue())
            ->where('customer_id', $user->id)
            ->when(!empty($searchString), function (Builder $builder) use ($searchString)
            {
                $builder->whereIn('candidates.id', function ($query) use ($searchString)
                {
                    $query->select('candidates.id')
                        ->from('candidates')
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
            ->when(!empty($isOnlyFavoriteCandidates), function (Builder $builder) use ($isOnlyFavoriteCandidates)
            {
                $builder->whereIn('candidates.id', function ($query) use ($isOnlyFavoriteCandidates)
                {
                    $query->select('candidates.id')
                        ->from('candidates')
                        ->join('project_candidates', function ($join)
                        {
                            $join->on('project_candidates.candidate_id', '=', 'candidates.id');
                        })
                        ->where('project_candidates.is_favorite', $isOnlyFavoriteCandidates);
                });
            })
            ->when(!empty($tags), function (Builder $builder) use ($tags)
            {
                $builder->whereIn('candidates.id', function ($query) use ($tags)
                {
                    $query->select('candidate_tags.candidate_id')
                        ->from('candidate_tags')
                        ->whereIn('candidate_tags.tag_id', $tags);
                });
            })
            ->when(!empty($staffLevel), function (Builder $builder) use ($staffLevel)
            {
                $builder->where('staff_level_id', '=', $staffLevel);
            })
            ->when(!empty($staffSpecialization), function (Builder $builder) use ($staffSpecialization)
            {
                $builder->where('staff_specialization_id', '=', $staffSpecialization);
            })
            ->addSelect([
                'isFavorite' => function($query)
                {
                    $query->select('is_favorite')
                        ->from('project_candidates')
                        ->whereColumn('candidate_id', 'candidates.id')
                        ->where([
                            'is_favorite' => true
                        ])->limit(1);
                },
                'isFavoriteProjectNames' => function($query)
                {
                    $query->select(DB::raw('GROUP_CONCAT(projects.name SEPARATOR \', \')'))
                        ->from('project_candidates')
                        ->join('projects', 'projects.id', 'project_candidates.project_id')
                        ->whereColumn('candidate_id', 'candidates.id')
                        ->where([
                            'is_favorite' => true
                        ]);
                }
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
                    $builder->orderBy('surname', 'asc');
                    break;

                case OrderType::$NameDesc->getValue():
                    $builder->orderBy('surname', 'desc');
                    break;
            }
        }
        else
        {
            $builder->orderByDesc('created_at');
        }

        return $builder;
    }

    public function deleteCandidate(Candidate $candidate)
    {
        $projectCandidates = $this->projectCandidateDao->getByCandidate($candidate);
        foreach ($projectCandidates as $projectCandidate)
        {
            $this->projectCandidateDao->deleteProjectCandidate($projectCandidate);
        }
        $this->eventDao->markEventAsDeleted($candidate->id, Event::EVENT_TYPE_CANDIDATE);
        $candidate->delete();
    }

    public function checkCandidateExists(
        User $user,
        string $name = null,
        string $email = null,
        string $phone = null
    ): ?Candidate
    {
        return Candidate::query()
            ->where('customer_id', $user->id)
            ->when(!empty($name), function (Builder $builder) use ($name)
            {
                $builder->whereRaw('UPPER(`name`) LIKE ?', [mb_strtoupper('%' . $name . '%', 'UTF-8')]);
            })
            ->when(!empty($email), function (Builder $builder) use ($email)
            {
                $builder->where('email', $email);
            })
            ->when(!empty($phone), function (Builder $builder) use ($phone)
            {
                $builder->where('phone',  $phone);
            })
            ->first();
    }

    /** @returns Candidate | null */
    public function firstWithData(int $id): ?Model
    {
        return Candidate::query()
            ->with(['image', 'documents', 'staffLevel', 'staffSpecialization', 'companyPosition'])
            ->withCount(['testResults', 'questionnaireResults', 'userEvent'])
            ->where('id', $id)
            ->first();
    }

    public function getCandidateResults(Candidate $candidate, TestType $testType, ?string $orderType)
    {
        if ($testType === TestType::$Test)
        {
            $resultIds = $candidate->testResults()->pluck('test_results.id');
        }
        else
        {
            $resultIds = $candidate->questionnaireResults()->pluck('test_results.id');
        }

        $builder = TestResult::query()
            ->select('test_results.*')
            ->with('test')
            ->whereIn('test_results.id', $resultIds);

        if ($orderType)
        {
            switch ($orderType)
            {
                case OrderType::$CreatedAtAsc->getValue():
                    $builder->orderBy('finished_at', 'asc');
                    break;

                case OrderType::$CreatedAtDesc->getValue():
                    $builder->orderBy('finished_at', 'desc');
                    break;

                case OrderType::$NameAsc->getValue():
                    $builder->join('tests', 'tests.id', 'test_results.test_id')->orderBy('name', 'asc');
                    break;

                case OrderType::$NameDesc->getValue():
                    $builder->join('tests', 'tests.id', 'test_results.test_id')->orderBy('name', 'desc');
                    break;
            }
        }

        return $builder;
    }

    private function createCandidateTags(Candidate $candidate, User $user, ?array $tags)
    {
        if ($tags)
        {
            $this->tagsDao->clearCandidateTags($candidate);
            foreach ($tags as $tagName)
            {
                $tag = $this->tagsDao->searchByNameAndUser($tagName, $user);

                if ($tag)
                {
                    $this->tagsDao->createCandidateTag($candidate, $tag);
                }
                else
                {
                    $tag = $this->tagsDao->createTagByUser($tagName, $user);
                    $this->tagsDao->createCandidateTag($candidate, $tag);
                }
            }
        }
    }

    private function createCandidateStaffLevel(Candidate $candidate, User $user, ?string $staffLevelName)
    {
        if ($staffLevelName)
        {
            $staffLevel = $this->staffLevelDao->searchByNameAndUser($staffLevelName, $user);

            if ($staffLevel)
            {
                $this->staffLevelDao->saveCandidateStaffLevel($candidate, $staffLevel);
            }
            else
            {
                $staffLevel = $this->staffLevelDao->createStaffLevelByUser($staffLevelName, $user);
                $this->staffLevelDao->saveCandidateStaffLevel($candidate, $staffLevel);
            }
        }
    }

    private function createCandidateStaffSpecialization(Candidate $candidate, User $user, ?string $staffSpecializationName)
    {
        if ($staffSpecializationName)
        {
            $staffSpecialization = $this->staffSpecializationDao->searchByNameAndUser($staffSpecializationName, $user);

            if ($staffSpecialization)
            {
                $this->staffSpecializationDao->saveCandidateStaffSpecialization($candidate, $staffSpecialization);
            }
            else
            {
                $staffSpecialization = $this->staffSpecializationDao->createStaffSpecializationByUser($staffSpecializationName, $user);
                $this->staffSpecializationDao->saveCandidateStaffSpecialization($candidate, $staffSpecialization);
            }
        }
    }

    public function addNew(AddRequest $request, ?CandidateFile $candidateImage = null): ?Candidate
    {
        $candidate = new Candidate();
        DB::transaction(function () use ($request, $candidate, $candidateImage)
        {
            $user = $this->authService->getUser();
            $candidate->customer_id = $user->id;
            $candidate->name = $request->name;
            $candidate->type = CandidateType::$Candidate;
            $candidate->middle_name = $request->middleName;
            $candidate->surname = $request->surname;
            $candidate->email = $request->email;
            $candidate->phone = $request->phone;
            $candidate->image_id = $candidateImage ? $candidateImage->id : null;
            $candidate->birth_date = Carbon::createFromTimestamp($request->birthDate)->format('Y-m-d');
            $candidate->career_start_year = $request->careerStartYear;
            $candidate->specialty_work_start_year = $request->specialtyWorkStartYear;
            $candidate->company_position_id = $request->companyPositionId;
            $candidate->save();

            $this->createCandidateStaffLevel($candidate, $user, $request->staffLevel);
            $this->createCandidateStaffSpecialization($candidate, $user, $request->staffSpecialization);
            $this->createCandidateTags($candidate, $user, $request->tags);
        });
        return $candidate;
    }

    public function update(UpdateRequest $request, Candidate $candidate, ?CandidateFile $candidateImage = null): ?Candidate
    {
        DB::transaction(function () use ($request, $candidate, $candidateImage)
        {
            $user = $this->authService->getUser();
            $candidate->name = $request->name;
            $candidate->middle_name = $request->middleName;
            $candidate->surname = $request->surname;
            $candidate->email = $request->email;
            $candidate->phone = $request->phone;
            $candidate->image_id = $candidateImage ? $candidateImage->id : null;
            $candidate->career_start_year = $request->careerStartYear;
            $candidate->specialty_work_start_year = $request->specialtyWorkStartYear;
            $candidate->company_position_id = $request->companyPositionId;
            $candidate->birth_date = Carbon::createFromTimestamp($request->birthDate)->format('Y-m-d');
            $candidate->pdf_data_url = null;
            $candidate->save();

            $this->createCandidateStaffLevel($candidate, $user, $request->staffLevel);
            $this->createCandidateStaffSpecialization($candidate, $user, $request->staffSpecialization);
            $this->createCandidateTags($candidate, $user, $request->tags);
        });
        return $candidate;
    }

    public function updateNote(UpdateNotesRequest $request, Candidate $candidate): ?Candidate
    {
        $candidate->notes = $request->notes;
        $candidate->save();
        return $candidate;
    }
}
