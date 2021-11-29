<?php

namespace App\Notifications\User;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\User;
use App\Models\Notifications\EmailMessageModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TariffExpirationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private User $user;
    private string $tariffName;
    private int $leftDays;
    private Carbon $tariffEndDate;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, $tariffName, $leftDays, $tariffEndDate)
    {
        $this->user = $user;
        $this->tariffName = $tariffName;
        $this->leftDays = $leftDays;
        $this->tariffEndDate = $tariffEndDate;
    }

    /**
     * @param $notifiable
     * @return string[]
     */
    public function via($notifiable)
    {
        return ['database', TemplatedEmailChannel::class];
    }

    public function toEmail()
    {
        $messageModel = (new EmailMessageModel());

        if ($this->leftDays == 7 || ($this->leftDays <= 4 && $this->leftDays != 0))
        {
            $template = 'emails.tariff.expireTariff';
        }
        elseif ($this->leftDays == 0)
        {
            $template = 'emails.tariff.expiredTariff';
        }

        return $messageModel->setTemplate($template)
            ->setSubject('Напоминание об оплате тарифа.')
            ->setData([
                'customerName' => $this->user->name,
                'customerMiddleName' => $this->user->middle_name,
                'userTariffName' => $this->tariffName,
                'leftDays' => $this->leftDays,
                'tariffEndDate' => $this->tariffEndDate->format('d-m-Y')
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
