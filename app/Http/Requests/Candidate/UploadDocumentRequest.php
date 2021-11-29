<?php

namespace App\Http\Requests\Candidate;

use App\Db\Entity\Candidate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * UploadDocumentRequest
 *
 * @property int $candidateId
 * @property UploadedFile $document
 */
class UploadDocumentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'candidateId' => [
                'required',
                Rule::exists(Candidate::class, 'id')
            ],
            'document' => [
                'required',
                'max: 20000',
                'mimes:doc,docx,pdf'
            ]
        ];
    }
}
