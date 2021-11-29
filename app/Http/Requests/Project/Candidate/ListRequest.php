<?php

namespace App\Http\Requests\Project\Candidate;

use App\Db\Entity\Level;
use App\Db\Entity\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ListRequest
 *
 * @property int $projectId
 * @property int $page
 *
 */
class ListRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'projectId' => [
                'required',
                Rule::exists(Project::class, 'id')
            ],
        ];
    }
}
