<?php

namespace App\Http\Requests\Project\Candidate;

use App\Db\Entity\Candidate;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectQuestionnaire;
use App\Db\Entity\ProjectTest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * AddRequest
 *
 * @property int $projectId
 * @property int $projectTestId
 * @property int $projectQuestionnaireId
 * @property int[] $candidateIds
 *
 */
class AddRequest extends FormRequest
{
    public function rules()
    {
        return [
            'projectId' => [
                'required',
                'integer',
                Rule::exists(Project::class, 'id')
            ],
            'projectTestId' => [
                Rule::requiredIf(!$this->projectQuestionnaireId),
                'integer',
                Rule::exists(ProjectTest::class, 'id')
            ],
            'projectQuestionnaireId' => [
                Rule::requiredIf(!$this->projectTestId),
                'integer',
                Rule::exists(ProjectQuestionnaire::class, 'id')
            ],
            'candidateIds' => 'required|array|min:1',
            'candidateIds.*' => [
                'integer',
                Rule::exists(Candidate::class, 'id')
            ],
        ];
    }
}
