<?php

namespace App\Http\Requests\Question;

/**
 * AddQuestionAnswerRequest
 *
 * @property int | null $id
 * @property string $answer
 * @property boolean $isRight
 *
 */
class AddQuestionAnswerRequest
{
    public function __construct($array)
    {
        $this->id = isset($array['id']) ? $array['id'] : null;
        $this->answer = isset($array['answer']) ? $array['answer'] : '';
        $this->isRight = isset($array['isRight']) ? boolval($array['isRight']) : false;
    }
}
