<?php

namespace App\Http\Requests\Candidate;

use App\Db\Entity\Candidate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ExportCandidateDataRequest
 *
 * @property int $candidateId
 */

class ExportCandidateDataRequest extends FormRequest
{

    public function rules()
    {
        return [
            'candidateId' => [
                'required',
                Rule::exists(Candidate::class, 'id')
            ]
        ];
    }
}
