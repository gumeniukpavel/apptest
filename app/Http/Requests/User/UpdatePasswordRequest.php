<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdatePasswordRequest
 *
 * @property string $oldPassword
 * @property string $newPassword
 * @property string $repeatPassword
 */
class UpdatePasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'oldPassword' => 'required|max:255',
            'newPassword' => 'required|min:6|max:255',
            'repeatPassword' => 'required|min:6|max:255',
        ];
    }
}
