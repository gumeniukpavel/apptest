<?php

namespace App\Http\Requests\Project;

use App\Db\Entity\Level;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * GetFavoritesRequest
 *
 * @property int $page
 * @property int $projectId
 *
 */
class GetFavoritesRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'projectId' => [
                'nullable',
                Rule::exists(Project::class, 'id')
            ],
        ];
    }
}
