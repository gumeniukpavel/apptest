<?php

namespace App\Notifications\User;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Expert;
use App\Db\Entity\ExpertInterviewEvent;
use App\Db\Entity\LetterTemplate;
use App\Models\Notifications\EmailMessageModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ExpertAgreeInterviewNotification extends Notification implements ShouldQueue
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

        return $messageModel->setTemplate('emails.interview.expert.expertAgreedInterview')
            ->setSubject(trans('notifications.user.expertAgreeInterview'))
            ->setData([
                'customerName' => $this->expertInterviewEvent->user->name,
                'customerMiddleName' => $this->expertInterviewEvent->user->middle_name,
                'expertName' => $this->expertInterviewEvent->expert->name,
                'expertSurname' => $this->expertInterviewEvent->expert->surname,
                'interviewStartDate' => $startDate->format('d.m.Y'),
                'interviewStartTime' => $startDate->format('H:i'),
                'interviewEndTime' => $endDate->format('H:i'),
                'interviewCreatedAt' => $this->expertInterviewEvent->created_at->format('d.m.Y H:i:s'),
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
