<?php

namespace App\Notifications\FeedbackMessage;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\FeedbackMessage;
use App\Db\Entity\Role;
use App\Db\Entity\User;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class FeedbackMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private FeedbackMessage $feedbackMessage;

    public function __construct(FeedbackMessage $feedbackMessage)
    {
        $this->feedbackMessage = $feedbackMessage;
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
     * @param  User $user
     * @return EmailMessageModel
     */
    public function toEmail()
    {
        $messageModel = (new EmailMessageModel());

        $messageModel
            ->setTemplate('emails.feedback.feedbackMessage')
            ->setSubject(trans('notifications.feedback.feedback'))
            ->setData([
                'name' => $this->feedbackMessage->name,
                'email' => $this->feedbackMessage->email,
                'phone' => $this->feedbackMessage->phone,
                'message' => $this->feedbackMessage->message,
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
        return $this->feedbackMessage->toArray();
    }
}
