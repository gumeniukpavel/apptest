<?php
namespace App\Constant;

use App\Constant\Common\Enum;

class ResultOfChecking extends Enum
{
    public static ResultOfChecking $Approved;
    public static ResultOfChecking $NonApproved;
}

ResultOfChecking::$Approved = new ResultOfChecking('Approved', 'Approved');
ResultOfChecking::$NonApproved = new ResultOfChecking('NonApproved', 'NonApproved');

ResultOfChecking::init();
