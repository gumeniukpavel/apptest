<?php

namespace App\Notifications\Expert;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Candidate;
use App\Db\Entity\ExpertInterviewEvent;
use App\Db\Entity\LetterTemplate;
use App\Models\Notifications\EmailMessageModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InterviewAgreeNotification extends Notification implements ShouldQueue
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

        $startDate = Carbon::createFromTimestamp($this->expertInterviewEvent->userEvent->start_date);
        $endDate = Carbon::createFromTimestamp($this->expertInterviewEvent->userEvent->end_date);

        $startDateIso = $startDate->format('Ymd\THis');
        $endDateIso = $endDate->format('Ymd\THis');

        $url = 'http://www.google.com/calendar/event?action=TEMPLATE&text=Собеседование&dates=' . $startDateIso . '/' .
            $endDateIso . '&location='. str_replace(" ", "+", $this->expertInterviewEvent->user->profile->address . '+' .
                $this->expertInterviewEvent->user->profile->city . '+' . $this->expertInterviewEvent->user->profile->house_number);

        return $messageModel->setTemplate('emails.interview.expert.agreedToAnInterview')
            ->setSubject(trans('notifications.candidate.interviewAgree'))
            ->setData([
                'candidateName' => $this->expertInterviewEvent->userEvent->candidate->name,
                'startDate' => $startDate->format('d.m.Y'),
                'startTime' => $startDate->format('H:i'),
                'endDate' => $endDate->format('d.m.Y H:i'),
                'city' => $this->expertInterviewEvent->user->profile->city,
                'address' => $this->expertInterviewEvent->user->profile->address,
                'houseNumber' => $this->expertInterviewEvent->user->profile->house_number,
                'googleCalendarUrl' => $url,
                'apartmentNumber' => $this->expertInterviewEvent->user->profile->apartment_number,
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
