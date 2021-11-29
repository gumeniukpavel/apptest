<?php

namespace App\Db\Entity;

/**
 * UserProfile
 *
 * @property int $id
 * @property int $user_id
 * @property string $organization_name
 * @property string $city
 * @property string $country_id
 * @property string $address
 * @property integer $postcode
 * @property string $house_building
 * @property integer $apartment_number
 * @property string $house_number
 * @property string $customer_legal_name
 * @property string $customer_legal_edrpou
 * @property string $customer_legal_account_number
 * @property bool $customer_is_vat_payer
 * @property string $customer_legal_address
 * @property string $customer_legal_postcode
 * @property string $customer_legal_city_region
 *
 * @property User $user
 */
class UserProfile extends BaseEntity
{
    protected $fillable = [];

    protected $visible = [
        'id',
        'organization_name',
        'city',
        'country_id',
        'address',
        'postcode',
        'house_building',
        'apartment_number',
        'house_number',
        'customer_legal_name',
        'customer_legal_edrpou',
        'customer_is_vat_payer',
        'customer_legal_account_number',
        'customer_legal_address',
        'customer_legal_postcode',
        'customer_legal_city_region',
        'user'
    ];

    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $appends = [

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
