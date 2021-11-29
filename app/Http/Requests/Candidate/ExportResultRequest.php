<?php

namespace App\Http\Requests\Candidate;

use App\Db\Entity\Candidate;
use App\Db\Entity\TestResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ExportResultRequest
 *
 * @property int $candidateId
 * @property int $resultId
 */

class ExportResultRequest extends FormRequest
{

    public function rules()
    {
        return [
            'candidateId' => [
                'required',
                Rule::exists(Candidate::class, 'id')
            ],
            'resultId' => [
                'required',
                Rule::exists(TestResult::class, 'id')
            ],
        ];
    }
}
