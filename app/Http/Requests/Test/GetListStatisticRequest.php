<?php

namespace App\Http\Requests\Test;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * GetListRequest
 *
 * @property int $page
 * @property int $fromDate
 * @property int $toDate
 * @property string $searchString
 *
 */
class GetListStatisticRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
            'fromDate' => 'nullable|integer',
            'toDate' => 'nullable|integer',
            'searchString' => 'nullable|string'
        ];
    }

    public function getPage()
    {
        return $this->page ? $this->page : 1;
    }
}
