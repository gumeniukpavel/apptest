<?php

namespace App\Http\Requests\Event;

use App\Constant\OrderType;
use App\Http\Requests\ApiFormRequest;
use App\Rules\IsEnumValueRule;
use Carbon\Carbon;

/**
 * GetListRequest
 *
 * @property int | null $page
 * @property string | null $eventType
 * @property int | null $fromDate
 * @property int | null $toDate
 * @property string | null $searchString
 * @property string | null $orderType
 */
class GetListRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'eventType' => 'nullable|string',
            'fromDate' => 'nullable|integer',
            'toDate' => 'nullable|integer',
            'searchString' => 'nullable|string',
            'orderType' => [
                'nullable',
                new IsEnumValueRule(OrderType::class)
            ]
        ];
    }

    public function getFromDate()
    {
        return $this->fromDate ? Carbon::createFromTimestamp($this->fromDate)->hours(0)->minutes(0) : null;
    }

    public function getToDate()
    {
        return $this->toDate ? Carbon::createFromTimestamp($this->toDate)->hours(24)->minutes(0) : null;
    }
}
