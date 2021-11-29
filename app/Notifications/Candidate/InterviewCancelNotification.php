<?php

namespace App\Notifications\Candidate;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Candidate;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\TestResult;
use App\Db\Entity\User;
use App\Db\Entity\UserEvent;
use App\Db\Entity\UserProfile;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InterviewCancelNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private UserEvent $userEvent;

    public function __construct(
        UserEvent $userEvent
    )
    {
        $this->userEvent = $userEvent;
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
     * @param Candidate|null $candidate
     * @return EmailMessageModel
     */
    public function toEmail()
    {
        $template = null;
        $messageModel = (new EmailMessageModel());

        return $messageModel->setTemplate('emails.interview.cancelToAnInterview')
            ->setSubject(trans('notifications.candidate.interviewCancel'))
            ->setData([
                'customerName' => $this->userEvent->user->name,
                'customerSurname' => $this->userEvent->user->surname,
                'companyName' => $this->userEvent->user->profile->organization_name,
                'customerPhone' => $this->userEvent->user->phone,
                'customerEmail' => $this->userEvent->user->email,
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
            'candidate' => $notifiable->toArray()
        ];
    }
}
