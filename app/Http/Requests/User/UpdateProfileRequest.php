<?php

namespace App\Http\Requests\User;

use App\Db\Entity\Country;
use App\Db\Entity\User;
use App\Db\Entity\UserProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateProfileRequest
 *
 * @property string $name
 * @property string $middleName
 * @property string $surname
 * @property string $email
 * @property string $phone
 * @property string $organizationName
 * @property string $city
 * @property string $countryId
 * @property string $address
 * @property integer $postcode
 * @property string $houseBuilding
 * @property integer $apartmentNumber
 * @property string $houseNumber
 *
 */
class UpdateProfileRequest extends FormRequest
{
     /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string|min:3|max:255',
            'middleName' => 'nullable|string|min:3|max:255',
            'surname' => 'nullable|string|min:3|max:255',
            'phone' => 'nullable|integer|between:100000000000,999999999999',
            'organizationName' => 'nullable|string|max:255',
            'countryId' => [
                'required',
                Rule::exists(Country::class, 'id')
            ],
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'postcode' => 'nullable|integer',
            'houseBuilding' => 'nullable|string|max:255',
            'apartmentNumber' => 'nullable|integer',
            'houseNumber' => 'nullable|string|max:255',
        ];
    }

    public function updateEntity(User &$user, UserProfile &$profile)
    {
        $profile->organization_name = $this->organizationName;
        $profile->country_id = $this->countryId;
        $profile->city = $this->city;
        $profile->address = $this->address;
        $profile->postcode = $this->postcode;
        $profile->house_building = $this->houseBuilding;
        $profile->apartment_number = $this->apartmentNumber;
        $profile->house_number = $this->houseNumber;

        $user->name = $this->name;
        $user->middle_name = $this->middleName;
        $user->surname = $this->surname;
        $user->phone = $this->phone;
    }
}
