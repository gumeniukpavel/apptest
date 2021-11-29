<?php


namespace App\Http\Requests\Promotion;


use Illuminate\Foundation\Http\FormRequest;

/**
 * AddAffiliatedPersonRequest
 *
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $city
 */
class AddAffiliatedPersonRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|max:255|email:rfc',
            'phone' => 'required|integer|between:100000000000,999999999999',
            'city' => 'required|string|max:255'
        ];
    }
}
