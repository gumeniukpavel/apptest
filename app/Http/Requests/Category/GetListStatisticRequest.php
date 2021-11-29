<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

/**
 * GetListRequest
 *
 * @property int $page
 *
 */
class GetListStatisticRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer',
        ];
    }

    public function getPage()
    {
        return $this->page ? $this->page : 1;
    }
}
