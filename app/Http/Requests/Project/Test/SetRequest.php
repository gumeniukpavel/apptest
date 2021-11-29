<?php

namespace App\Http\Requests\Project\Test;

use App\Db\Entity\Project;
use App\Db\Entity\Test;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * AddRequest
 *
 * @property int $projectId
 * @property int $testId
 *
 */
class SetRequest extends FormRequest
{
    public function rules()
    {
        return [
            'projectId' => [
                'required',
                Rule::exists(Project::class, 'id')
            ],
            'testId' => [
                'required',
                Rule::exists(Test::class, 'id'),
            ],
        ];
    }
}
