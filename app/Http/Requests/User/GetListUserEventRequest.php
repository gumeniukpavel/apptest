<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;
use Carbon\Carbon;

/**
 * GetListUserEventRequest
 *
 * @property int $page
 * @property int $fromDate
 * @property int $toDate
 * @property string $status
 */
class GetListUserEventRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer|nullable',
            'fromDate' => 'nullable|integer',
            'toDate' => 'nullable|integer',
            'status' => 'nullable|string'
        ];
    }

    public function fromDate()
    {
        if (!$this->fromDate)
        {
            return null;
        }
        $date = Carbon::createFromTimestamp($this->fromDate)->startOfDay();
        return $date->timestamp;
    }

    public function toDate()
    {
        if (!$this->toDate)
        {
            return null;
        }
        $date = Carbon::createFromTimestamp($this->toDate)->endOfDay();
        return $date->timestamp;
    }
}
