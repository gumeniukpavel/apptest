<?php

namespace App\Notifications\Expert;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Candidate;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\QuestionnaireApprovalRequest;
use App\Db\Entity\User;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class QuestionnaireApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private QuestionnaireApprovalRequest $questionnaireApprovalRequest;
    private string $verify;
    private string $cancel;

    public function __construct(
        QuestionnaireApprovalRequest $questionnaireApprovalRequest
    )
    {
        $this->questionnaireApprovalRequest = $questionnaireApprovalRequest;
        $this->verify = config('app.front_url') . '/expert/verify?token=' . $this->questionnaireApprovalRequest->access_token_verify . '&type=questionnaire';
        $this->cancel = config('app.front_url') . '/expert/cancel?token=' . $this->questionnaireApprovalRequest->access_token_cancel . '&type=questionnaire';
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

        return $messageModel->setTemplate('emails.questionnaire.questionnaireApprovalRequestsInvitation')
            ->setSubject(trans('notifications.expert.questionnaireApproval'))
            ->setData([
                'expertName' => $this->questionnaireApprovalRequest->expert->name,
                'expertSurname' => $this->questionnaireApprovalRequest->expert->surname,
                'expertMiddleName' => $this->questionnaireApprovalRequest->expert->middle_name,
                'candidateName' => $this->questionnaireApprovalRequest->candidate->name,
                'candidateSurname' => $this->questionnaireApprovalRequest->candidate->surname,
                'questionnaireName' => $this->questionnaireApprovalRequest->questionnaireResult->test->name,
                'verifyUrl' => $this->verify,
                'cancelUrl' => $this->cancel,
                'customerName' => $this->questionnaireApprovalRequest->user->name,
                'customerSurname' => $this->questionnaireApprovalRequest->user->surname,
                'companyName' => $this->questionnaireApprovalRequest->user->profile->organization_name,
                'customerPhone' => $this->questionnaireApprovalRequest->user->phone,
                'customerEmail' => $this->questionnaireApprovalRequest->user->email,
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
            'verify' => $this->verify,
            'cancel' => $this->cancel,
            'candidate' => $notifiable->toArray()
        ];
    }
}
