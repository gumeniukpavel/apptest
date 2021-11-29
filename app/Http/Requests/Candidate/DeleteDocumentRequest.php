<?php

namespace App\Http\Requests\Candidate;

use App\Db\Entity\Candidate;
use App\Db\Entity\CandidateFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * DeleteDocumentRequest
 *
 * @property int $candidateId
 * @property int $documentId
 */
class DeleteDocumentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'candidateId' => [
                'required',
                Rule::exists(Candidate::class, 'id')
            ],
            'documentId' => [
                'required',
                Rule::exists(CandidateFile::class, 'id')
            ],
        ];
    }
}
