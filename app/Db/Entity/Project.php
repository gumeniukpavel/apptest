<?php

namespace App\Db\Entity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Project
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $project_status_id
 * @property string $description
 * @property Carbon $finish_date
 * @property Carbon $created_at
 *
 * @property array $tagNames
 *
 * @property User $customer
 * @property ProjectStatus $status
 * @property Candidate[] | Collection $candidates
 * @property Test[] | Collection $tests
 * @property Test[] | Collection $questionnaires
 * @property ProjectTest[] | Collection $projectTests
 * @property ProjectQuestionnaire[] | Collection $projectQuestionnaires
 * @property ProjectCandidate[] | Collection $projectCandidates
 */
class Project extends ExtendedBaseEntity
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_status_id',
        'name',
        'description'
    ];

    protected $appends = [
        'tagNames'
    ];

    protected $visible = [
        'id',
        'name',
        'description',
        'customer',
        'category',
        'status',
        'project_status_id',
        'finish_date',
        'created_at',

        'projectCandidates',
        'testResults',
        'projectTests',
        'projectQuestionnaires',
        'tagNames',

        'testResultsCount',
        'projectCandidatesCount'
    ];

    protected $dates = ['finish_date', 'created_at'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status()
    {
        return $this->hasOne(ProjectStatus::class, 'id', 'project_status_id');
    }

    public function projectStatus()
    {
        return $this->hasOne(ProjectStatus::class, 'id', 'project_status');
    }

    public function candidates()
    {
        return $this->belongsToMany(Candidate::class, 'project_candidates');
    }

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'project_tests')->with('level');
    }

    public function questionnaires()
    {
        return $this->belongsToMany(Test::class, 'project_questionnaires', 'project_id', 'questionnaire_id');
    }

    public function projectTests()
    {
        return $this->hasMany(ProjectTest::class)
            ->with('test')
            ->with('projectCandidates');
    }

    public function projectQuestionnaires()
    {
        return $this->hasMany(ProjectQuestionnaire::class)
            ->with('questionnaire')
            ->with('projectCandidates');
    }

    public function projectCandidates()
    {
        return $this->hasMany(ProjectCandidate::class)
            ->with('candidate');
    }

    public function testResults()
    {
        return $this->hasManyThrough(
            TestResult::class,
            ProjectCandidate::class
        );
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'project_tags')->select(['name']);
    }

    public function getTagNamesAttribute()
    {
        return $this->tags()->pluck('name')->toArray();
    }
}
