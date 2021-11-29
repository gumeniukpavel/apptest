<?php

namespace App\Http\Requests\Expert;

use App\Db\Entity\Expert;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * UploadDocumentRequest
 *
 * @property int $expertId
 * @property UploadedFile $document
 */
class UploadDocumentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'expertId' => [
                'required',
                Rule::exists(Expert::class, 'id')
            ],
            'document' => [
                'required',
                'max: 20000',
                'mimes:doc,docx,pdf'
            ]
        ];
    }
}
