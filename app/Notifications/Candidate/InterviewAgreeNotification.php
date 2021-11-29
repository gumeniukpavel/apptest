<?php

namespace App\Notifications\Candidate;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Candidate;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\UserEvent;
use App\Models\Notifications\EmailMessageModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InterviewAgreeNotification extends Notification implements ShouldQueue
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

        $startDate = Carbon::createFromTimestamp($this->userEvent->start_date);
        $endDate = Carbon::createFromTimestamp($this->userEvent->end_date);

        $startDateIso = $startDate->format('Ymd\THis');
        $endDateIso = $endDate->format('Ymd\THis');

        $url = 'http://www.google.com/calendar/event?action=TEMPLATE&text=Собеседование&dates=' . $startDateIso . '/' .
            $endDateIso . '&location='. str_replace(" ", "+", $this->userEvent->user->profile->address . '+' .
                $this->userEvent->user->profile->city . '+' . $this->userEvent->user->profile->house_number);

        return $messageModel->setTemplate('emails.interview.agreedToAnInterview')
            ->setSubject(trans('notifications.candidate.interviewAgree'))
            ->setData([
                'candidateName' => $this->userEvent->candidate->name,
                'startDate' => $startDate->format('d.m.Y'),
                'startTime' => $startDate->format('H:i'),
                'endDate' => $endDate->format('d.m.Y H:i'),
                'city' => $this->userEvent->user->profile->city,
                'address' => $this->userEvent->user->profile->address,
                'houseNumber' => $this->userEvent->user->profile->house_number,
                'googleCalendarUrl' => $url,
                'apartmentNumber' => $this->userEvent->user->profile->apartment_number,
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
