<?php

namespace App\Notifications\Candidate;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Candidate;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\User;
use App\Db\Entity\UserEvent;
use App\Db\Entity\UserProfile;
use App\Models\Notifications\EmailMessageModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class InterviewInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private UserEvent $userEvent;
    private UserProfile $userProfile;
    private ?LetterTemplate $template;
    private string $agreed;
    private string $cancel;

    public function __construct(
        UserEvent $userEvent,
        UserProfile $userProfile,
        ?LetterTemplate $template
    )
    {
        $this->userEvent = $userEvent;
        $this->userProfile = $userProfile;
        $this->template = $template;
        $this->agreed = config('app.front_url') . '/candidate/agreed/' . $this->userEvent->access_token_yes;
        $this->cancel = config('app.front_url') . '/candidate/cancel/' . $this->userEvent->access_token_no;
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
     * @param User|null $templateOwner
     * @return EmailMessageModel
     */
    public function toEmail()
    {
        $messageModel = (new EmailMessageModel());
        $messageModel->setTemplate('emails.interview.interviewInvitation')
            ->setSubject(trans('notifications.candidate.interviewInvitation'))
            ->setData([
                'candidateName' => $this->userEvent->candidate->name,
                'description' => $this->userEvent->description,
                'startDate' => Carbon::createFromTimestamp($this->userEvent->start_date)->format('d.m.Y'),
                'startTime' => Carbon::createFromTimestamp($this->userEvent->start_date)->format('H:i'),
                'city' => $this->userProfile->city,
                'address' => $this->userProfile->address,
                'houseNumber' => $this->userProfile->house_number,
                'apartmentNumber' => $this->userProfile->apartment_number,
                'customerName' => $this->userProfile->user->name,
                'customerSurname' => $this->userProfile->user->surname,
                'companyName' => $this->userProfile->organization_name,
                'customerPhone' => $this->userProfile->user->phone,
                'customerEmail' => $this->userProfile->user->email,
                'yesUrl' => $this->agreed,
                'noUrl' => $this->cancel,
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
            'candidate' => $notifiable->toArray(),
        ];
    }
}
