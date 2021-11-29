<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * GetListStatisticRequest
 *
 * @property int $page
 * @property int $fromDate
 * @property int $toDate
 *
 */
class GetListStatisticRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'fromDate' => 'nullable|integer',
            'toDate' => 'nullable|integer'
        ];
    }

    public function getPage()
    {
        return $this->page ? $this->page : 1;
    }
}
