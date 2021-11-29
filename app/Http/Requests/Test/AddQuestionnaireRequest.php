<?php

namespace App\Http\Requests\Test;

use App\Constant\TestType;
use App\Db\Entity\Category;
use App\Db\Entity\Level;
use App\Db\Entity\Test;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * AddQuestionnaireRequest
 *
 * @property string $name
 * @property string $description
 * @property array $tags
 */
class AddQuestionnaireRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1024',
            'tags' => 'array|nullable',
            'tags.*' => ['string', 'max:255']
        ];
    }

    public function getEntity() : Test
    {
        $questionnaire = new Test();
        $questionnaire->name = $this->name;
        $questionnaire->description = $this->description;
        $questionnaire->type = TestType::$Questionnaire;
        return $questionnaire;
    }
}
