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

class CandidateAgreeInterviewNotification extends Notification implements ShouldQueue
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

        $startDateIso = $startDate->format('Ymd\THis');
        $endDateIso = $endDate->format('Ymd\THis');

        $url = 'http://www.google.com/calendar/event?action=TEMPLATE&text=Собеседование&dates=' . $startDateIso . '/' .
            $endDateIso . '&location='. str_replace(" ", "+", $this->userEvent->user->profile->address . '+' .
                $this->userEvent->user->profile->city . '+' . $this->userEvent->user->profile->house_number);

        return $messageModel->setTemplate('emails.interview.candidateAgreedInterview')
            ->setSubject(trans('notifications.user.candidateAgreeInterview'))
            ->setData([
                'customerName' => $this->userEvent->user->name,
                'customerMiddleName' => $this->userEvent->user->middle_name,
                'candidateName' => $this->userEvent->candidate->name,
                'candidateSurname' => $this->userEvent->candidate->surname,
                'candidateMiddleName' => $this->userEvent->candidate->middle_name,
                'interviewStartDate' => $startDate->format('d.m.Y'),
                'interviewStartTime' => $startDate->format('H:i'),
                'interviewEndTime' => $endDate->format('H:i'),
                'interviewCreatedAt' => $this->userEvent->created_at->format('d.m.Y H:i:s'),
                'googleCalendarUrl' => $url,
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
