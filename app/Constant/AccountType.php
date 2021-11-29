<?php
namespace App\Constant;

use App\Constant\Common\Enum;

class AccountType extends Enum
{
    public static AccountType $LegalEntity;
    public static AccountType $Individual;
}

AccountType::$LegalEntity = new AccountType('LegalEntity', 'LegalEntity');
AccountType::$Individual = new AccountType('Individual', 'Individual');

AccountType::init();
