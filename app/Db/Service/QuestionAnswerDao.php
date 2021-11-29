<?php

namespace App\Db\Service;

use App\Db\Entity\Question;
use App\Db\Entity\QuestionAnswer;

class QuestionAnswerDao
{
    public function getOne(int $questionId, int $answerId) : ?QuestionAnswer
    {
        /** @var QuestionAnswer $record */
        $record = QuestionAnswer::query()->where([
            'question_id' => $questionId,
            'id' => $answerId,
        ])->first();
        return $record;
    }
}
