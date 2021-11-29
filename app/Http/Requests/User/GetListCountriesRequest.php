<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;

/**
 * GetListRequest
 *
 * @property int $page
 */
class GetListCountriesRequest extends ApiFormRequest
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
