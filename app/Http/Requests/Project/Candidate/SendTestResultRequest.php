<?php

namespace App\Http\Requests\Project\Candidate;

use App\Db\Entity\ProjectCandidate;
use App\Db\Entity\Test;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * SendTestResultRequest
 *
 * @property int $projectCandidateId
 * @property int $testId
 * @property boolean $isPositiveAnswer
 *
 */
class SendTestResultRequest extends FormRequest
{
    public function rules()
    {
        return [
            'projectCandidateId' => [
                'required',
                Rule::exists(ProjectCandidate::class, 'id')
            ],
            'testId' => [
                'required',
                Rule::exists(Test::class, 'id')
            ],
            'isPositiveAnswer' => 'required'
        ];
    }
}
