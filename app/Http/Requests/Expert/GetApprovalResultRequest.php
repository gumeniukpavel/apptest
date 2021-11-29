<?php

namespace App\Http\Requests\Expert;

use App\Constant\OrderType;
use App\Db\Entity\Expert;
use App\Rules\IsEnumValueRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * GetTestResultRequest
 *
 * @property int $id
 * @property int $page
 * @property string|null $orderType
 */

class GetApprovalResultRequest extends FormRequest
{

    public function rules()
    {
        return [
            'id' => [Rule::exists(Expert::class, 'id'), 'required'],
            'page' => 'integer',
            'orderType' => [
                'nullable',
                new IsEnumValueRule(OrderType::class)
            ]
        ];
    }

    public function getPage()
    {
        return $this->page ? $this->page : 1;
    }
}
