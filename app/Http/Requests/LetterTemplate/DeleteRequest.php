<?php

namespace App\Http\Requests\LetterTemplate;

use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * DeleteRequest
 *
 * @property int $id
 */
class DeleteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(LetterTemplate::class)
            ],
        ];
    }
}
