<?php

namespace App\Http\Requests\Test;

use App\Constant\OrderType;
use App\Db\Entity\Category;
use App\Db\Entity\Level;
use App\Db\Entity\Project;
use App\Http\Requests\ApiFormRequest;
use App\Rules\IsEnumValueRule;
use Illuminate\Validation\Rule;

/**
 * GetListRequest
 *
 * @property int $page
 * @property int | null $categoryId
 * @property int | null $levelId
 * @property int | null $projectId
 * @property string $searchString
 * @property boolean $isTestsFromTariff
 * @property string | null $orderType
 */
class GetListRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'categoryId' => [
                'nullable',
                Rule::exists(Category::class, 'id')
            ],
            'levelId' => [
                'nullable',
                Rule::exists(Level::class, 'id')
            ],
            'projectId' => [
                'nullable',
                Rule::exists(Project::class, 'id')
            ],
            'searchString' => 'string|max:255|nullable',
            'isTestsFromTariff' => 'boolean|nullable',
            'orderType' => [
                'nullable',
                new IsEnumValueRule(OrderType::class)
            ]
        ];
    }
}
