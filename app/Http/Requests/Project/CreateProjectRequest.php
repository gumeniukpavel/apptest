<?php

namespace App\Http\Requests\Project;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateProjectRequest
 * @package App\Http\Requests\Project
 *
 * @property string $name
 * @property integer $finishDate
 * @property array $tags
 * @property string $description
 */
class CreateProjectRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|max: 255',
            'finishDate' => 'integer',
            'description' => 'nullable|string|max: 255',
            'tags' => 'array|nullable',
            'tags.*' => ['string', 'max:255']
        ];
    }
}
