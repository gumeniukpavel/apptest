<?php

namespace App\Http\Requests\Candidate;

use App\Constant\OrderType;
use App\Db\Entity\Tag;
use App\Rules\IsEnumValueRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * GetListRequest
 *
 * @property int $page
 * @property string | null $searchString
 * @property int | null $careerStartYearFrom
 * @property int | null $careerStartYearTo
 * @property int | null $ageFromTimestamp
 * @property int | null $ageToTimestamp
 * @property boolean | null $isOnlyFavoriteCandidates
 * @property int | null $staffLevel
 * @property int | null $staffSpecialization
 * @property string | null $orderType
 * @property array | null $tags
 */
class GetListRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'searchString' => 'string|max:255|nullable',
            'careerStartYearFrom' => 'integer|nullable',
            'careerStartYearTo' => 'integer|nullable',
            'ageFromTimestamp' => 'integer|nullable',
            'ageToTimestamp' => 'integer|nullable',
            'isOnlyFavoriteCandidates' => 'boolean|nullable',
            'staffLevel' => 'integer|nullable',
            'staffSpecialization' => 'integer|nullable',
            'orderType' => [
                'nullable',
                new IsEnumValueRule(OrderType::class)
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

