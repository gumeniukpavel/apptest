<?php

namespace App\Http\Requests\Payment;

use App\Constant\OrderType;
use App\Db\Entity\Payment;
use App\Http\Requests\ApiFormRequest;
use App\Rules\IsEnumValueRule;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * GetListRequest
 *
 * @property int | null $page
 * @property string | null $status
 * @property Carbon | null $fromDate
 * @property Carbon | null $toDate
 * @property string | null $searchString
 * @property string | null $orderType
 */
class GetListRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'status' => [
                'nullable',
                Rule::in(Payment::PAYMENT_STATUSES)
            ],
            'fromDate' => 'nullable|date',
            'toDate' => 'nullable|date',
            'searchString' => 'nullable|string',
            'orderType' => [
                'nullable',
                new IsEnumValueRule(OrderType::class)
            ]
        ];
    }

    public function getFromDate()
    {
        return $this->fromDate ? Carbon::make($this->fromDate)->hours(0)->minutes(0) : false;
    }

    public function getToDate()
    {
        return $this->toDate ? Carbon::make($this->toDate)->hours(24)->minutes(0) : false;
    }
}
