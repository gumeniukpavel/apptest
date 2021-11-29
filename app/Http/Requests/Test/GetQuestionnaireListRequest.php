<?php

namespace App\Http\Requests\Test;

use App\Constant\OrderType;
use App\Db\Entity\Category;
use App\Db\Entity\Level;
use App\Db\Entity\Project;
use App\Db\Entity\Tag;
use App\Http\Requests\ApiFormRequest;
use App\Rules\IsEnumValueRule;
use Illuminate\Validation\Rule;

/**
 * GetQuestionnaireListRequest
 *
 * @property int $page
 * @property string $searchString
 * @property int | null $projectId
 * @property string | null $orderType
 * @property array | null $tags
 */
class GetQuestionnaireListRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'searchString' => 'string|max:255|nullable',
            'orderType' => [
                'nullable',
                new IsEnumValueRule(OrderType::class)
            ],
            'projectId' => [
                'nullable',
                Rule::exists(Project::class, 'id')
            ],
            'tags' => [
                'nullable',
                'array'
            ],
            'tags.*' => [
                Rule::exists(Tag::class, 'id')
            ]
        ];
    }
}
