<?php

namespace App\Http\Requests\Test;

use App\Db\Entity\QuestionAnswer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $answerId
 * @property string $customAnswer
 */
class SaveAnswer extends FormRequest
{
    public function rules()
    {
        return [
            'answerId' => [
                Rule::exists(QuestionAnswer::class, 'id')
            ],
            'customAnswer' => [
                Rule::requiredIf(!$this->answerId),
                'string'
            ],
        ];
    }
}
