<?php

namespace App\Http\Requests\Project\Test;

use App\Db\Entity\ProjectTest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $projectTestId
 */
class DeleteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'projectTestId' => [
                'required',
                'integer',
                Rule::exists(ProjectTest::class, 'id')
            ]
        ];
    }
}
