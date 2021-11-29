<?php

namespace App\Notifications\Expert;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Candidate;
use App\Db\Entity\Expert;
use App\Db\Entity\ExpertInterviewEvent;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\UserEvent;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InterviewCancelNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private ExpertInterviewEvent $expertInterviewEvent;

    public function __construct(
        ExpertInterviewEvent $expertInterviewEvent
    )
    {
        $this->expertInterviewEvent = $expertInterviewEvent;
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

        return $messageModel->setTemplate('emails.interview.expert.cancelToAnInterview')
            ->setSubject(trans('notifications.candidate.interviewCancel'))
            ->setData([
                'customerName' => $this->expertInterviewEvent->user->name,
                'customerSurname' => $this->expertInterviewEvent->user->surname,
                'companyName' => $this->expertInterviewEvent->user->profile->organization_name,
                'customerPhone' => $this->expertInterviewEvent->user->phone,
                'customerEmail' => $this->expertInterviewEvent->user->email,
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
            'expert' => $notifiable->toArray()
        ];
    }
}
