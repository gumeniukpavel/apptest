<?php


namespace App\Http\Requests\Promotion;


use App\Db\Entity\Promotion\AffiliatedPerson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateAffiliatedPersonRequest
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $city
 */
class UpdateAffiliatedPersonRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists(AffiliatedPerson::class, 'id')
            ],
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|max:255|email:rfc',
            'phone' => 'required|integer|between:100000000000,999999999999',
            'city' => 'required|string|max:255',
        ];
    }
}
