<?php

namespace App\Http\Requests\Question;

use App\Db\Entity\Test;
use App\Http\Requests\ApiFormRequest;
use App\Rules\IsOneTypeMediaFiles;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

/**
 * AddRequest
 *
 * @property int $testId
 * @property int $score
 * @property boolean $isFreeEntry
 * @property array $mediaFileIds
 * @property string $description
 * @property AddQuestionAnswerRequest[] $answers
 *
 */
class AddRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'testId' => [
                'required',
                Rule::exists(Test::class, 'id')
            ],
            'score' => [
                new RequiredIf($this->checkTestType()),
                'integer',
                'between:1,10'
            ],
            'isFreeEntry' => 'boolean',
            'mediaFileIds' => [
                'array',
                new IsOneTypeMediaFiles()
            ],
            'description' => 'required|string|max:3000',
            'answers' => 'nullable',
            'answers.*.answer' => 'required|string|max:3000',
            'answers.*isRight' => 'required|boolean',
        ];
    }

    protected function getValidatorInstance()
    {
        return parent::getValidatorInstance()->after(function ($validator) {

            $this->answers = (new Collection($this->answers))->map(function ($answerItem) {
                return new AddQuestionAnswerRequest($answerItem);
            });
        });
    }

    private function checkTestType()
    {
        /** @var Test $test */
        $test = Test::query()
            ->where('id', $this->testId)
            ->first();

        return $test->isTest();
    }
}
