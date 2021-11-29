<?php

namespace App\Db\Service;

use App\Db\Entity\PublicEntity\PublicTestResultAnswer;
use App\Db\Entity\TestResultAnswer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResultAnswerDao
{
    public function getNextQuestionWithData(int $testResultId): ?PublicTestResultAnswer
    {
        /** @var PublicTestResultAnswer $testResultAnswer */
        $testResultAnswer = PublicTestResultAnswer::query()
            ->with('question', function (BelongsTo $belongsTo) {
                $belongsTo->with('answers');
                $belongsTo->with('images');
                $belongsTo->with('audios');
                $belongsTo->with('videos');
                $belongsTo->withCount('answers');
            })
            ->where([
                'test_result_id' => $testResultId,
                'question_answer_id' => null,
                'custom_answer' => null
            ])
            ->orderBy('position')
            ->first();
        return $testResultAnswer;
    }

    public function getCurrentQuestion(int $testResultId, int $testQuestionId): ?TestResultAnswer
    {
        /** @var TestResultAnswer $testResultAnswer */
        $testResultAnswer = TestResultAnswer::query()
            ->where([
                'test_result_id' => $testResultId,
                'test_question_id' => $testQuestionId,
            ])
            ->first();
        return $testResultAnswer;
    }
}
