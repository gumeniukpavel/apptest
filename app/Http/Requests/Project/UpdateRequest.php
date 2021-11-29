<?php

namespace App\Http\Requests\Project;

use App\Db\Entity\Category;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateRequest
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property array $tags
 * @property int $statusId
 * @property int $finishDate
 *
 */
class UpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:3000',
            'statusId' =>
                ['integer',
                'required',
                    Rule::exists(ProjectStatus::class, 'id')
                ],
            'tags' => 'array|nullable',
            'tags.*' => ['string', 'max:255'],
            'finishDate' => 'required|integer',
        ];
    }
}
