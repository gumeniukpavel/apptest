<?php

namespace App\Http\Requests\LetterTemplate;

use App\Db\Entity\LetterTemplateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class CreateLetterTemplateRequest
 * @package App\Http\Requests\LetterTemplate
 *
 * @property int $typeId
 * @property string $subject
 * @property string $body
 */
class CreateLetterTemplateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'typeId' => [
                'integer',
                'required',
                Rule::exists(LetterTemplateType::class, 'id')
            ],
            'subject' => 'required|string|max: 255',
            'body' => 'required|string'
        ];
    }
}
