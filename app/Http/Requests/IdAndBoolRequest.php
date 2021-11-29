<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * IdAndBoolRequest
 *
 * @property int $id
 * @property int $isTrue
 */
class IdAndBoolRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => 'required|integer',
            'isTrue' => 'required|bool'
        ];
    }
}
