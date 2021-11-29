<?php

namespace App\Http\Requests\Test;

use App\Db\Entity\Category;
use App\Db\Entity\Level;
use App\Db\Entity\Test;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

/**
 * AddTestRequest
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $levelId
 * @property int $categoryId
 * @property int $timeLimit
 * @property int $passPointValue
 * @property int $maxAllowedQuestions
 * @property array $tags
 */
class UpdateTestRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(Test::class, 'id')
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1024',
            'categoryId' => [
                new RequiredIf($this->checkTestType()),
                Rule::exists(Category::class, 'id')
            ],
            'levelId' => [
                new RequiredIf($this->checkTestType()),
                Rule::exists(Level::class, 'id')
            ],
            'timeLimit' => [
                new RequiredIf($this->checkTestType()),
                'integer',
            ],
            'passPointValue' => [
                new RequiredIf($this->checkTestType()),
                'integer',
            ],
            'maxAllowedQuestions' => 'nullable|integer',
            'tags' => 'array|nullable',
            'tags.*' => ['string', 'max:255']
        ];
    }

    public function updateEntity(Test $test) : Test
    {
        $test->name = $this->name;
        $test->description = $this->description;
        if ($this->checkTestType())
        {
            $test->level_id = $this->levelId;
            $test->category_id = $this->categoryId;
            $test->time_limit = $this->timeLimit;
            $test->pass_point_value = $this->passPointValue;
            $test->max_allowed_questions = $this->maxAllowedQuestions;
        }
        return $test;
    }

    private function checkTestType()
    {
        /** @var Test $test */
        $test = Test::query()
            ->where('id', $this->id)
            ->first();

        return $test->isTest();
    }
}
