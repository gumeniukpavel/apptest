<?php

namespace App\Http\Requests\LetterTemplate;

use Illuminate\Foundation\Http\FormRequest;

/**
 * GetListRequest
 *
 * @property int $page
 *
 */
class GetListRequest extends FormRequest
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
