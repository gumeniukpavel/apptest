<?php

namespace App\Http\Requests\Project\Questionnaire;

use App\Db\Entity\ProjectQuestionnaire;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $projectQuestionnaireId
 */
class DeleteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'projectQuestionnaireId' => [
                'required',
                'integer',
                Rule::exists(ProjectQuestionnaire::class, 'id')
            ]
        ];
    }
}
