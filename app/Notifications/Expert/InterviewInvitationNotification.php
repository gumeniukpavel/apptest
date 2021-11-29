<?php

namespace App\Notifications\Expert;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\ExpertInterviewEvent;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\User;
use App\Db\Entity\UserProfile;
use App\Models\Notifications\EmailMessageModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InterviewInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private ExpertInterviewEvent $expertInterviewEvent;
    private UserProfile $userProfile;
    private ?LetterTemplate $template;
    private string $agreed;
    private string $cancel;

    public function __construct(
        ExpertInterviewEvent $expertInterviewEvent,
        UserProfile $userProfile,
        ?LetterTemplate $template
    )
    {
        $this->expertInterviewEvent = $expertInterviewEvent;
        $this->userProfile = $userProfile;
        $this->template = $template;
        $this->agreed = config('app.front_url') . '/expert/interviewAgreed/' . $this->expertInterviewEvent->access_token_yes;
        $this->cancel = config('app.front_url') . '/expert/interviewCanceled/' . $this->expertInterviewEvent->access_token_no;
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
        $messageModel->setTemplate('emails.interview.expert.interviewInvitation')
            ->setSubject(trans('notifications.candidate.interviewInvitation'))
            ->setData([
                'expertName' => $this->expertInterviewEvent->expert->name,
                'expertMiddleName' => $this->expertInterviewEvent->expert->middle_name,
                'candidateName' => $this->expertInterviewEvent->userEvent->candidate->name,
                'candidateSurname' => $this->expertInterviewEvent->userEvent->candidate->surname,
                'startDate' => Carbon::createFromTimestamp($this->expertInterviewEvent->userEvent->start_date)->format('d.m.Y'),
                'startTime' => Carbon::createFromTimestamp($this->expertInterviewEvent->userEvent->start_date)->format('H:i'),
                'city' => $this->userProfile->city,
                'address' => $this->userProfile->address,
                'houseNumber' => $this->userProfile->house_number,
                'apartmentNumber' => $this->userProfile->apartment_number,
                'companyName' => $this->userProfile->organization_name,
                'customerName' => $this->userProfile->user->name,
                'customerSurname' => $this->userProfile->user->surname,
                'customerPhone' => $this->userProfile->user->phone,
                'customerEmail' => $this->userProfile->user->email,
                'yesUrl' => $this->agreed,
                'noUrl' => $this->cancel
            ]);

        if ($this->template)
        {
            return $messageModel->setSubject($this->template->subject)
                ->setBody($this->template->body);
        }
        return $messageModel->setSubject(trans('notifications.candidate.interviewInvitation'));
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
            'yes' => $this->agreed,
            'no' => $this->cancel,
            'expert' => $notifiable->toArray(),
        ];
    }
}
