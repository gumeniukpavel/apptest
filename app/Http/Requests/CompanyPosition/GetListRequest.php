<?php

namespace App\Http\Requests\CompanyPosition;

use App\Constant\OrderType;
use App\Http\Requests\ApiFormRequest;
use App\Rules\IsEnumValueRule;

/**
 * GetListRequest
 *
 * @property int $page
 * @property string $searchString
 * @property string | null $orderType
 *
 */
class GetListRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'searchString' => 'nullable|string|max:255',
            'orderType' => [
                'nullable',
                new IsEnumValueRule(OrderType::class)
            ]
        ];
    }
}
