<?php

namespace App\Http\Requests\Feedback;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FeedbackMessageRequest
 *
 * @property string $name
 * @property string $email
 * @property int $phone
 * @property string $message
 */
class FeedbackMessageRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name'=> 'string|required',
            'email' => [
                Rule::requiredIf(!$this->phone),
                'string',
                'max:255',
                'email:rfc'
            ],
            'phone' => [
                Rule::requiredIf(!$this->email),
                'integer',
                'between:100000000000,999999999999'
            ],
            'message' => 'required|string'
        ];
    }
}
