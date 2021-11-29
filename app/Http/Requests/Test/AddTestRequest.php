<?php

namespace App\Http\Requests\Test;

use App\Db\Entity\Category;
use App\Db\Entity\Test;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * AddTestRequest
 *
 * @property string $name
 * @property string $description
 * @property int $levelId
 * @property int $categoryId
 * @property int $timeLimit
 * @property int $passPointValue
 * @property int $maxAllowedQuestions
 */
class AddTestRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1024',
            'categoryId' => [
                'integer',
                'required',
                Rule::exists(Category::class, 'id')
            ],
            'levelId' => 'required|integer',
            'timeLimit' =>  'required|integer',
            'passPointValue' => 'required|integer',
            'maxAllowedQuestions' => 'nullable|integer'
        ];
    }

    public function getEntity() : Test
    {
        $test = new Test();
        $test->name = $this->name;
        $test->description = $this->description;
        $test->level_id = $this->levelId;
        $test->category_id = $this->categoryId;
        $test->time_limit = $this->timeLimit;
        $test->pass_point_value = $this->passPointValue;
        $test->max_allowed_questions = $this->maxAllowedQuestions;
        return $test;
    }
}
