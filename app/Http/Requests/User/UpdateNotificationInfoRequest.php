<?php

namespace App\Http\Requests\User;

use App\Db\Entity\User;
use App\Db\Entity\UserProfile;
use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateNotificationInfoRequest
 *
 * @property boolean $isSendEmailNews
 * @property boolean $isSendEmailTestCompletedNotification
 * @property boolean $isSendEmailTariff
 * @property boolean $isSendSmsNews
 * @property boolean $isSendSmsTestCompletedNotification
 * @property boolean $isSendSmsTariff
 */
class UpdateNotificationInfoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'isSendEmailNews' => 'required|boolean',
            'isSendEmailTestCompletedNotification' => 'required|boolean',
            'isSendEmailTariff' => 'required|boolean',
            'isSendSmsNews' => 'required|boolean',
            'isSendSmsTestCompletedNotification' => 'required|boolean',
            'isSendSmsTariff' => 'required|boolean',
        ];
    }

    public function updateEntity(User &$user)
    {
        $user->is_send_email_news = $this->isSendEmailNews;
        $user->is_send_email_test_completed_notification = $this->isSendEmailTestCompletedNotification;
        $user->is_send_email_tariff = $this->isSendEmailTariff;
        $user->is_send_sms_news = $this->isSendSmsNews;
        $user->is_send_sms_test_completed_notification = $this->isSendSmsTestCompletedNotification;
        $user->is_send_sms_tariff = $this->isSendSmsTariff;
    }
}
