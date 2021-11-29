<?php

namespace App\Http\Requests\LetterTemplate;

use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * SendDemoTemplateRequest
 *
 * @property int $id
 */
class SendDemoTemplateRequest extends FormRequest
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
