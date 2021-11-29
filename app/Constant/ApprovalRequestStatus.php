<?php
namespace App\Constant;

use App\Constant\Common\Enum;

class ApprovalRequestStatus extends Enum
{
    public static ApprovalRequestStatus $Pending;
    public static ApprovalRequestStatus $Approved;
    public static ApprovalRequestStatus $Canceled;
    public static ApprovalRequestStatus $Expired;
}

ApprovalRequestStatus::$Pending = new ApprovalRequestStatus('Pending', 'Pending');
ApprovalRequestStatus::$Approved = new ApprovalRequestStatus('Approved', 'Approved');
ApprovalRequestStatus::$Canceled = new ApprovalRequestStatus('Canceled', 'Canceled');
ApprovalRequestStatus::$Expired = new ApprovalRequestStatus('Expired', 'Expired');

ApprovalRequestStatus::init();
