<?php

namespace App\Db\Entity\PublicEntity;

use App\Db\Entity\QuestionAnswer;

/**
 */
class PublicQuestionAnswer extends QuestionAnswer
{
    protected $visible = [
        'id',
        'answer',
    ];
}
