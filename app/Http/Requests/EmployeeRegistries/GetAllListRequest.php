<?php

namespace App\Http\Requests\EmployeeRegistries;

use App\Constant\OrderType;
use App\Rules\IsEnumValueRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * GetListRequest
 *
 * @property int $page
 * @property string | null $searchString
 * @property string | null $orderType
 * @property int | null $fromDate
 * @property int | null $toDate
 */
class GetAllListRequest extends FormRequest
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
            'fromDate' => 'integer|nullable',
            'toDate' => 'integer|nullable',
        ];
    }
}
