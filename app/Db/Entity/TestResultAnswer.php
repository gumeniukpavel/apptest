<?php

namespace App\Db\Entity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * TestCandidateQuestion
 *
 * @property int $id
 * @property int $test_candidate_id
 * @property int $question_id
 * @property int $question_answer_id
 * @property string $custom_answer
 * @property int $result_value
 * @property Carbon $started_at
 * @property int $time_for
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property TestResult $testResult
 * @property Question $question
 * @property QuestionAnswer $answer
 */
class TestResultAnswer extends BaseEntity
{
    protected $fillable = [
        'test_result_id',
        'question_id',
        'question_answer_id',
        'result_value',
        'time_for',
        'started_at',
        'position',
        'custom_answer'
    ];

    protected $visible = [
        'id',
        'result_value',
        'started_at',
        'time_for',
        'position',
        'testResult',
        'question',
        'answer',
        'custom_answer'
    ];

    protected $dates = [
        'started_at'
    ];

    public function testResult()
    {
        return $this->belongsTo(TestResult::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(QuestionAnswer::class, 'question_answer_id');
    }
}
