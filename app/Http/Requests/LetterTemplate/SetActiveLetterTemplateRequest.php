<?php

namespace App\Http\Requests\LetterTemplate;

use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class CreateLetterTemplateRequest
 * @package App\Http\Requests\LetterTemplate
 *
 * @property int $id
 * @property boolean $isActive
 */
class SetActiveLetterTemplateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(LetterTemplate::class, 'id')
            ],
            'isActive' => 'required|boolean'
        ];
    }
}
