<?php

namespace App\Notifications\FeedbackMessage;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\CallbackMessage;
use App\Db\Entity\Role;
use App\Db\Entity\User;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CallbackNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private CallbackMessage $callback;

    public function __construct(CallbackMessage $callback)
    {
        $this->callback = $callback;
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
     * @return EmailMessageModel
     */
    public function toEmail()
    {
        $messageModel = (new EmailMessageModel());

        $messageModel
            ->setTemplate('emails.feedback.feedbackMessage')
            ->setSubject(trans('notifications.feedback.callback'))
            ->setData([
                'name' => $this->callback->name,
                'phone' => $this->callback->phone,
                'message' => $this->callback->message,
            ]);

        return $messageModel;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->callback->toArray();
    }
}
