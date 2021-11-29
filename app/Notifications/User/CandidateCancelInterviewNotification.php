<?php

namespace App\Notifications\User;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Candidate;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\UserEvent;
use App\Models\Notifications\EmailMessageModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CandidateCancelInterviewNotification extends Notification implements ShouldQueue
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
     * @return EmailMessageModel
     */
    public function toEmail(): EmailMessageModel
    {
        $messageModel = (new EmailMessageModel());

        $startDate = Carbon::createFromTimestamp($this->userEvent->start_date);
        $endDate = Carbon::createFromTimestamp($this->userEvent->end_date);

        return $messageModel->setTemplate('emails.interview.candidateCancelInterview')
            ->setSubject(trans('notifications.user.candidateCancelInterview'))
            ->setData([
                'customerName' => $this->userEvent->user->name,
                'customerMiddleName' => $this->userEvent->user->middle_name,
                'candidateName' => $this->userEvent->candidate->name,
                'candidateSurname' => $this->userEvent->candidate->surname,
                'interviewStartDate' => $startDate->format('d.m.Y'),
                'interviewStartTime' => $startDate->format('H:i'),
                'interviewEndTime' => $endDate->format('H:i'),
                'interviewCreatedAt' => $this->userEvent->created_at->format('d.m.Y H:i:s'),
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
