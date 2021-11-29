<?php


namespace App\Constant;


use App\Constant\Common\Enum;

class TariffUserStatus extends Enum
{
    public static TariffUserStatus $Pending;
    public static TariffUserStatus $Active;
    public static TariffUserStatus $Exhausted;
    public static TariffUserStatus $Paused;
}

TariffUserStatus::$Pending = new TariffUserStatus('Pending', 'В ожидании');
TariffUserStatus::$Active = new TariffUserStatus('Active', 'Активен');
TariffUserStatus::$Exhausted = new TariffUserStatus('Exhausted', 'Исчерпан');
TariffUserStatus::$Paused = new TariffUserStatus('Paused', 'На паузе');

TariffUserStatus::init();
