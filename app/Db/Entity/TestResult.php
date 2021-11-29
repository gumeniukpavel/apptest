<?php

namespace App\Db\Entity;

use App\Constant\ApprovalRequestStatus;
use App\Constant\TestResultStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * TestResult
 *
 * @property int $id
 * @property int $test_id
 * @property int $project_candidate_id
 * @property string $access_token
 * @property Carbon $access_token_created_at
 * @property int $estimation_time
 * @property Carbon $started_at
 * @property Carbon $finished_at
 * @property bool $is_passed
 * @property string $pdf_url
 * @property Carbon $updated_at
 * @property Carbon $created_at
 *
 * @property bool $timeIsOver
 * @property bool $isQuestionnaireVerified
 * @property bool $isTestVerified
 * @property int | null $testTime
 * @property Carbon $finishTime
 * @property float $percentageOfCorrectAnswers
 * @property int $maximumTestResultValue
 * @property int $questionsCount
 * @property int $correctAnswersCount
 * @property int $wrongAnswersCount
 * @property string $status
 *
 * @property Test $test
 * @property ProjectCandidate $projectCandidate
 * @property Candidate $candidate
 * @property TestResultAnswer[] | Collection $answers
 * @property TestResultAnswer[] | Collection $answeredAnswers
 * @property TestApprovalRequest[] | Collection $testApprovalRequests
 * @property QuestionnaireApprovalRequest[] | Collection $questionnaireApprovalRequests
 *
 * @property int $questionTotalValue
 */
class TestResult extends BaseEntity
{
    protected $dates = [
        'started_at',
        'finished_at',
        'updated_at',
        'created_at'
    ];

    protected $appends = [
        'timeIsOver',
        'testTime',
        'finishTime',
        'questionTotalValue',
        'percentageOfCorrectAnswers',
        'maximumTestResultValue',
        'questionsCount',
        'correctAnswersCount',
        'wrongAnswersCount',
        'status',
        'isQuestionnaireVerified',
        'isTestVerified'
    ];

    protected $fillable = [
        'test_id',
        'project_candidate_id',
        'access_token',
        'estimation_time',
        'started_at',
        'is_passed',
        'pdf_url'
    ];

    protected $visible = [
        'id',
        'estimation_time',
        'started_at',
        'finished_at',
        'is_passed',
        'pdf_url',

        'finishTime',
        'testTime',
        'timeIsOver',
        'questionTotalValue',
        'percentageOfCorrectAnswers',

        'test',
        'candidate',
        'projectCandidate',
        'answers',
        'questionTotalValue',
        'maximumTestResultValue',
        'questionsCount',
        'correctAnswersCount',
        'wrongAnswersCount',
        'status',
        'isQuestionnaireVerified',
        'isTestVerified'
    ];

    public function test()
    {
        return $this->belongsTo(Test::class)->with('level')->withCount('questions');
    }

    public function getStatusAttribute()
    {
        if (!$this->started_at)
        {
            return TestResultStatus::$StatusIgnored->toString();
        }
        else
        {
            if ($this->is_passed)
            {
                return TestResultStatus::$StatusSuccess->toString();
            }
            elseif ($this->finished_at)
            {
                return TestResultStatus::$StatusDidNotPassOnPoints->toString();
            }
            else
            {
                if ($this->test->isTest())
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

    public function projectCandidate()
    {
        return $this->belongsTo(ProjectCandidate::class, 'project_candidate_id', 'id');
    }

    public function candidate()
    {
        return $this->hasOneThrough(Candidate::class, ProjectCandidate::class, 'id', 'id', 'project_candidate_id', 'candidate_id');
    }

    public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TestResultAnswer::class, 'test_result_id');
    }

    public function answeredAnswers()
    {
        return $this->answers()->where(function ($query) {
            $query->whereNotNull('question_answer_id')->orWhereNotNull('custom_answer');
        });
    }

    public function testApprovalRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TestApprovalRequest::class, 'test_result_id');
    }

    public function questionnaireApprovalRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuestionnaireApprovalRequest::class, 'questionnaire_result_id');
    }

    public function questionTotalValue(): int
    {
        return $this->answers()->sum('result_value');
    }

    public function getQuestionTotalValueAttribute(): int
    {
        return $this->answers()->sum('result_value');
    }

    public function getPercentageOfCorrectAnswersAttribute(): int
    {
        $rightAnswerCount = $this->correctAnswersCount;
        $answerCount = $this->answers()->count();

        if ($answerCount == 0)
        {
            return 0;
        }

        return $rightAnswerCount / $answerCount * 100;
    }

    public function getTimeIsOverAttribute(): bool
    {
        return $this->started_at && $this->finishTime->lessThan(Carbon::now());
    }

    public function getTestTimeAttribute(): ?int
    {
        if (!$this->started_at || !$this->finished_at)
        {
            return null;
        }
        return $this->finished_at->diffInSeconds($this->started_at);
    }

    public function getFinishTimeAttribute(): Carbon
    {
        if (!$this->started_at)
        {
            return Carbon::now();
        }
        return $this->started_at->addMinutes($this->test->time_limit);
    }

    public function getMaximumTestResultValueAttribute()
    {
        return $this->test->questions->sum('score');
    }

    public function getQuestionsCountAttribute(): int
    {
        return $this->test->questions->count();
    }

    public function getCorrectAnswersCountAttribute(): int
    {
        return $this->answers()->where('result_value', '>', '0')->count();
    }

    public function getWrongAnswersCountAttribute(): int
    {
        return $this->answers()->where('result_value', '0')->count();
    }

    public function getIsTestVerifiedAttribute(): bool
    {
        return $this->testApprovalRequests()
            ->where('status', ApprovalRequestStatus::$Approved->getValue())
            ->exists();
    }

    public function getIsQuestionnaireVerifiedAttribute(): bool
    {
        return $this->questionnaireApprovalRequests()
            ->where('status', ApprovalRequestStatus::$Approved->getValue())
            ->exists();
    }
}
