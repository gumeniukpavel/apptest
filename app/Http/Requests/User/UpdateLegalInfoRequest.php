<?php

namespace App\Http\Requests\User;

use App\Db\Entity\UserProfile;
use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateProfileRequest
 *
 * @property string $legalName
 * @property string $EDRPOU
 * @property boolean $isVatPayer
 * @property string $accountNumber
 * @property string $legalAddress
 * @property string $legalPostcode
 * @property string $legalCityRegion
 *
 */
class UpdateLegalInfoRequest extends FormRequest
{
     /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'legalName' => 'nullable|string|max:255',
            'EDRPOU' => 'nullable|max:255',
            'isVatPayer' => 'boolean',
            'accountNumber' => 'nullable|string|max:255',
            'legalAddress' => 'nullable|string|max:255',
            'legalPostcode' => 'nullable|max:50/',
            'legalCityRegion' => 'nullable|string|max:255',
        ];
    }

    public function updateEntity(UserProfile $profile): UserProfile
    {
        $profile->customer_legal_name = $this->legalName;
        $profile->customer_legal_edrpou = $this->EDRPOU;
        $profile->customer_is_vat_payer = $this->isVatPayer;
        $profile->customer_legal_account_number = $this->accountNumber;
        $profile->customer_legal_address = $this->legalAddress;
        $profile->customer_legal_postcode = $this->legalPostcode;
        $profile->customer_legal_city_region = $this->legalCityRegion;
        return $profile;
    }
}
