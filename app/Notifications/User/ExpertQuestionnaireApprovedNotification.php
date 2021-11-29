<?php

namespace App\Notifications\User;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Candidate;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\QuestionnaireApprovalRequest;
use App\Db\Entity\User;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ExpertQuestionnaireApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private QuestionnaireApprovalRequest $questionnaireApprovalRequest;

    public function __construct(
        QuestionnaireApprovalRequest $questionnaireApprovalRequest
    )
    {
        $this->questionnaireApprovalRequest = $questionnaireApprovalRequest;
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

        return $messageModel->setTemplate('emails.questionnaire.expertQuestionnaireApproved')
            ->setSubject(trans('notifications.user.expertQuestionnaireApproved'))
            ->setData([
                'customerName' => $this->questionnaireApprovalRequest->user->name,
                'customerMiddleName' => $this->questionnaireApprovalRequest->user->middle_name,
                'expertName' => $this->questionnaireApprovalRequest->expert->name,
                'expertSurname' => $this->questionnaireApprovalRequest->expert->surname,
                'candidateName' => $this->questionnaireApprovalRequest->candidate->name,
                'candidateSurname' => $this->questionnaireApprovalRequest->candidate->surname,
                'questionnaireName' => $this->questionnaireApprovalRequest->questionnaireResult->test->name,
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
