<?php

namespace App\Http\Requests\Feedback;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CallbackMessageRequest
 *
 * @property string $name
 * @property int $phone
 * @property string $message
 */
class CallbackMessageRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name'=> 'string|required',
            'phone' => 'regex:/[0-9]{12}/',
            'message' => 'required|string'
        ];
    }
}
