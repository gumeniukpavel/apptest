<?php

namespace App\Http\Requests\Project;

use App\Db\Entity\Candidate;
use App\Db\Entity\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class RemoveFromFavoriteRequest
 * @package App\Http\Requests\Project
 *
 * @property int $projectId
 * @property int $candidateId
 */
class RemoveFromFavoriteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'projectId' => [
                'required',
                Rule::exists(Project::class, 'id')
            ],
            'candidateId' => [
                'required',
                Rule::exists(Candidate::class, 'id')
            ],
        ];
    }
}
