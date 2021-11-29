<?php

namespace App\Db\Entity\PublicEntity;

use App\Db\Entity\QuestionAnswer;
use App\Db\Entity\TestResultAnswer;

class PublicTestResultAnswer extends TestResultAnswer
{
    protected $table = 'test_result_answers';

    protected $fillable = [
        'test_result_id',
        'question_id',
        'question_answer_id',
        'result_value',
        'time_for',
        'started_at',
        'position',
    ];

    protected $visible = [
        'id',
        'result_value',
        'started_at',
        'time_for',
        'position',
        'testResult',
        'question',
        'questionAnswer'
    ];

    protected $dates = [
        'started_at'
    ];

    public function testResult()
    {
        return $this->belongsTo(PublicTestResult::class);
    }

    public function question()
    {
        return $this->belongsTo(PublicQuestion::class);
    }

    public function answer()
    {
        return $this->belongsTo(QuestionAnswer::class);
    }
}
