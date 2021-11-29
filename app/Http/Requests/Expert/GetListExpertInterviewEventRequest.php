<?php

namespace App\Http\Requests\Expert;

use App\Http\Requests\ApiFormRequest;

/**
 * GetListExpertInterviewEventRequest
 *
 * @property int $page
 * @property int $fromDate
 * @property int $toDate
 */
class GetListExpertInterviewEventRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer|nullable',
            'fromDate' => 'nullable|integer',
            'toDate' => 'nullable|integer',
        ];
    }
}
