<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * IdRequest
 *
 * @property int $page
 */
class PaginationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => 'required|integer'
        ];
    }
}
