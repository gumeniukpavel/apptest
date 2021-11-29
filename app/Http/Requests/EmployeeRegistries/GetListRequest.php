<?php

namespace App\Http\Requests\EmployeeRegistries;

use App\Constant\OrderType;
use App\Db\Entity\Candidate;
use App\Rules\IsEnumValueRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * GetListRequest
 *
 * @property int $employeeId
 * @property int $page
 * @property string | null $searchString
 * @property string | null $orderType
 * @property int | null $fromDate
 * @property int | null $toDate
 */
class GetListRequest extends FormRequest
{
    public function rules()
    {
        return [
            'employeeId' => [
                Rule::exists(Candidate::class, 'id'),
                'required'
            ],
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
