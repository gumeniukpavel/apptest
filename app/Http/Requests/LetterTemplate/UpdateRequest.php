<?php

namespace App\Http\Requests\LetterTemplate;

use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateRequest
 *
 * @property int $id
 * @property int $typeId
 * @property string $subject
 * @property string $body
 */
class UpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(LetterTemplate::class)
            ],
            'typeId' => [
                'integer',
                'required',
                Rule::exists(LetterTemplateType::class, 'id')
            ],
            'subject' => 'required|string|max: 255',
            'body' => 'required|string'
        ];
    }

    public function updateEntity(LetterTemplate $letterTemplate)
    {
        $letterTemplate->type_id = $this->typeId;
        $letterTemplate->subject = $this->subject;
        $letterTemplate->body = $this->body;

        return $letterTemplate;
    }
}
