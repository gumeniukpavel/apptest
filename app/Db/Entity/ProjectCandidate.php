<?php

namespace App\Db\Entity;

use App\Constant\TestResultStatus;
use App\Constant\TestType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ProjectCandidate
 *
 * @property int $id
 * @property int $project_id
 * @property int $project_test_id
 * @property int $project_questionnaire_id
 * @property int $candidate_id
 * @property bool $is_favorite
 * @property bool $is_show_in_review
 * @property bool $is_invitation_sent
 * @property bool $is_last_send_error
 * @property integer $last_notification_at
 * @property integer $last_send_error_at
 *
 * @property bool $cantSendNotification
 * @property Carbon|null $lastNotificationTime
 *
 * @property Project $project
 * @property Test $test
 * @property Test $questionnaire
 * @property TestResult[] $testResults
 * @property TestResult[] $questionnaireResults
 * @property Candidate $candidate
 */
class ProjectCandidate extends BaseEntity
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id'
    ];

    protected $visible = [
        'projectCandidateId',
        'is_favorite',
        'is_show_in_review',
        'is_invitation_sent',
        'is_last_send_error',
        'last_notification_at',
        'last_send_error_at',
        'testResultStatus',

        'lastNotificationTime',
        'cantSendNotification',

        'project',
        'test',
        'questionnaire',
        'testResults',
        'questionnaireResults',
        'candidate'
    ];

    public $timestamps = false;

    protected $attributes = [
        'is_favorite' => false,
        'is_show_in_review' => false
    ];

    protected $appends = [
        'projectCandidateId',
        'testResultStatus',
        'lastNotificationTime',
        'cantSendNotification'
    ];

    public function getTestResultStatusAttribute(): string
    {
        if ($this->test)
        {
            /** @var TestResult | Collection $result */
            $result = $this->testResults;
        }
        else
        {
            /** @var TestResult | Collection $result */
            $result = $this->questionnaireResults;
        }

        /** @var TestResult $lastResult */
        $lastResult = $result->last();

        if ($lastResult)
        {
            if (!$lastResult->started_at)
            {
                return TestResultStatus::$StatusIgnored->toString();
            }
            else
            {
                if ($lastResult->is_passed)
                {
                    return TestResultStatus::$StatusSuccess->toString();
                }
                elseif ($lastResult->finished_at)
                {
                    return TestResultStatus::$StatusDidNotPassOnPoints->toString();
                }
                else
                {
                    if ($lastResult->test->isTest())
                    {
                        return TestResultStatus::$StatusDidNotPassInTime->toString();
                    }
                    else
                    {
                        return TestResultStatus::$StatusNotCompleted->toString();
                    }
                }
            }
        }
        else
        {
            return false;
        }
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function testResults()
    {
        return $this->hasMany(TestResult::class)
            ->whereHas('test', function($test){
                $test->where('type', TestType::$Test->getValue());
            });
    }

    public function questionnaireResults()
    {
        return $this->hasMany(TestResult::class)
            ->whereHas('test', function($test){
                $test->where('type', TestType::$Questionnaire->getValue());
            });
    }

    public function test()
    {
        return $this->hasOneThrough(
            Test::class,
            ProjectTest::class,
            'id',
            'id',
            'project_test_id',
            'test_id'
        );
    }

    public function questionnaire()
    {
        return $this->hasOneThrough(
            Test::class,
            ProjectQuestionnaire::class,
            'id',
            'id',
            'project_questionnaire_id',
            'questionnaire_id'
        );
    }

    public function getProjectCandidateIdAttribute()
    {
        return $this->id;
    }

    public function getCantSendNotificationAttribute(): bool
    {
        return $this->lastNotificationTime
            && $this->lastNotificationTime->greaterThan(Carbon::now()->subMinutes(5));
    }

    public function getLastNotificationTimeAttribute(): ?Carbon
    {
        if (!$this->last_notification_at)
        {
            return null;
        }
        return Carbon::parse($this->last_notification_at);
    }
}
