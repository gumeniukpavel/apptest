<?php
namespace App\Constant;

use App\Constant\Common\Enum;

class AppMode extends Enum
{
    public static AppMode $Normal;
    public static AppMode $Beta;
    public static AppMode $Corporate;
}

AppMode::$Normal = new AppMode('normal', 'Normal');
AppMode::$Beta = new AppMode('beta', 'Beta');
AppMode::$Corporate = new AppMode('corporate', 'Corporate');

AppMode::init();
