<?php

namespace App\Http\Requests\Candidate;

use App\Db\Entity\Candidate;
use App\Db\Entity\CandidateFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * SaveMediaFileRequest
 *
 * @property int $candidateId
 * @property int $imageId
 */
class SaveImageRequest extends FormRequest
{
    public function rules()
    {
        return [
            'candidateId' => [
                'required',
                Rule::exists(Candidate::class, 'id')
            ],
            'imageId' => [
                'required',
                Rule::exists(CandidateFile::class, 'id')
            ],
        ];
    }
}
