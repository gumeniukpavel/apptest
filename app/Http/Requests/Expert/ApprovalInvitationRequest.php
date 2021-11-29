<?php

namespace App\Http\Requests\Expert;

use App\Db\Entity\Candidate;
use App\Db\Entity\Expert;
use App\Db\Entity\TestResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ApprovalInvitationRequest
 *
 * @property int $expertId
 * @property int $candidateId
 * @property int $testResultId
 */
class ApprovalInvitationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'expertId' => [
                Rule::exists(Expert::class, 'id'),
                'required'
            ],
            'candidateId'=> [
                Rule::exists(Candidate::class, 'id'),
                'required'
            ],
            'testResultId' => [
                Rule::exists(TestResult::class, 'id'),
                'required'
            ],
        ];
    }
}
