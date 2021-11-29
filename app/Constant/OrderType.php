<?php
namespace App\Constant;

use App\Constant\Common\Enum;

class OrderType extends Enum
{
    public static OrderType $CreatedAtDesc;
    public static OrderType $CreatedAtAsc;
    public static OrderType $NameDesc;
    public static OrderType $NameAsc;
}

OrderType::$CreatedAtDesc = new OrderType('createdAtDesc', 'createdAtDesc');
OrderType::$CreatedAtAsc = new OrderType('createdAtAsc', 'createdAtAsc');
OrderType::$NameDesc = new OrderType('nameDesc', 'nameDesc');
OrderType::$NameAsc = new OrderType('nameAsc', 'nameAsc');

OrderType::init();
