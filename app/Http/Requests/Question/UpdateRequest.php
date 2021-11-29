<?php

namespace App\Http\Requests\Question;

use App\Db\Entity\Question;
use App\Http\Requests\ApiFormRequest;
use App\Rules\IsOneTypeMediaFiles;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

/**
 * UpdateRequest
 *
 * @property int $id
 * @property int $score
 * @property boolean $isFreeEntry
 * @property array $mediaFileIds
 * @property string $description
 * @property AddQuestionAnswerRequest[] $answers
 *
 */
class UpdateRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(Question::class, 'id')
            ],
            'description' => 'required|string|max:3000',
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
            'answers' => 'nullable',
            'answers.*.id' => 'nullable|integer',
            'answers.*.answer' => 'required|string|max:3000',
            'answers.*.isRight' => 'required|boolean',
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
        /** @var Question $question */
        $question = Question::query()
            ->where('id', $this->id)
            ->first();

        if ($question->test)
        {
            return $question->test->isTest();
        }
        else
        {
            return false;
        }
    }
}
