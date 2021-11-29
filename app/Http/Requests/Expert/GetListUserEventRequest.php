<?php

namespace App\Http\Requests\Expert;

use App\Http\Requests\ApiFormRequest;

/**
 * GetListUserEventRequest
 *
 * @property int $date
 */
class GetListUserEventRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'date' => 'required|integer'
        ];
    }
}
