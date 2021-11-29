<?php

namespace App\Notifications\User;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\User;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Notifications\Notification;

class DemoTemplateNotification extends Notification
{
    private LetterTemplate $letterTemplate;

    public function __construct(
        LetterTemplate $letterTemplate
    )
    {
        $this->letterTemplate = $letterTemplate;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', TemplatedEmailChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $user
     * @return EmailMessageModel
     */
    public function toEmail(User $user)
    {
        $messageModel = (new EmailMessageModel());
        return  $messageModel
                ->setTemplate('emails.demoMessage')
                ->setSubject(trans('notifications.user.demoTemplate'))
                ->setBody($this->letterTemplate->body);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->letterTemplate->toArray();
    }
}
