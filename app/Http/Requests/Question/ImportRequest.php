<?php

namespace App\Http\Requests\Question;

use App\Db\Entity\Test;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property $questionFile
 * @property int $testId
 */
class ImportRequest extends FormRequest
{
    public function rules()
    {
        return [
            'questionFile' => [
                'required',
                'mimes:csv,txt'
            ],
            'testId' => [
                'required',
                Rule::exists(Test::class, 'id')
            ]
        ];
    }
}
