<?php

namespace App\Http\Requests\Expert;

use App\Db\Entity\Expert;
use App\Db\Entity\ExpertFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * DeleteDocumentRequest
 *
 * @property int $expertId
 * @property int $documentId
 */
class DeleteDocumentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'expertId' => [
                'required',
                Rule::exists(Expert::class, 'id')
            ],
            'documentId' => [
                'required',
                Rule::exists(ExpertFile::class, 'id')
            ],
        ];
    }
}
