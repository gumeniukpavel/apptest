<?php

namespace App\Http\Requests\Candidate;

use App\Constant\OrderType;
use App\Db\Entity\Candidate;
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

class GetTestResultRequest extends FormRequest
{

    public function rules()
    {
        return [
            'id' => [Rule::exists(Candidate::class, 'id'), 'required'],
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
