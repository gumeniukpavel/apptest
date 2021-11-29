<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;

/**
 * GetListManagersRequest
 *
 * @property int $page
 */
class GetListManagersRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer|nullable',
        ];
    }

    public function getPage()
    {
        return $this->page ? $this->page : 1;
    }
}
