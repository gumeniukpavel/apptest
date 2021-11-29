<?php
namespace App\Constant;

use App\Constant\Common\Enum;

class Localization extends Enum
{
    public static Localization $En;
    public static Localization $Ru;
    public static Localization $Ua;
}

Localization::$En = new Localization('en', 'En');
Localization::$Ru = new Localization('ru', 'Ru');
Localization::$Ua = new Localization('ua', 'Ua');

Localization::init();
