<?php

namespace App\Http\Requests\Project;

use App\Db\Entity\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class DeleteRequest
 * @package App\Http\Requests\Project
 *
 * @property int $id
 */
class DeleteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(Project::class, 'id')
            ],
        ];
    }
}
