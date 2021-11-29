<?php

namespace App\Http\Requests\CompanyPosition;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AddRequest
 *
 * @property string $name
 * @property int $vacancyCount
 */
class AddRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'vacancyCount' => 'nullable|integer'
        ];
    }
}
