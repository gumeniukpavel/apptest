<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * IdRequest
 *
 * @property int $id
 */
class IdRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => 'required|integer'
        ];
    }
}
