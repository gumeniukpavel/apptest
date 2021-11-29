<?php

namespace App\Notifications\User;

use App\Broadcasting\TemplatedEmailChannel;
use App\Constant\ApprovalRequestStatus;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\QuestionnaireApprovalRequest;
use App\Db\Entity\User;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ExpertAcceptedQuestionnaireApprovalRequestsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private QuestionnaireApprovalRequest $questionnaireApprovalRequests;

    public function __construct(
        QuestionnaireApprovalRequest $questionnaireApprovalRequests
    )
    {
        $this->questionnaireApprovalRequests = $questionnaireApprovalRequests;
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

        if ($this->questionnaireApprovalRequests->status == ApprovalRequestStatus::$Approved->getValue())
        {
            $messageModel->setTemplate('emails.approvalRequests.expertAcceptedQuestionnaireApprovalRequest')
                ->setSubject(trans('notifications.user.expertAcceptedQuestionnaireApprovalRequests'));
        }
        else
        {
            $messageModel->setTemplate('emails.approvalRequests.expertCanceledQuestionnaireApprovalRequest')
                ->setSubject(trans('notifications.user.expertCanceledQuestionnaireApprovalRequests'));
        }
            $messageModel->setData([
                'customerName' => $this->questionnaireApprovalRequests->user->name,
                'customerMiddleName' => $this->questionnaireApprovalRequests->user->middle_name,
                'expertName' => $this->questionnaireApprovalRequests->expert->name,
                'expertSurname' => $this->questionnaireApprovalRequests->expert->name,
                'candidateName' => $this->questionnaireApprovalRequests->candidate->name,
                'candidateSurname' => $this->questionnaireApprovalRequests->candidate->surname,
                'testName' => $this->questionnaireApprovalRequests->questionnaireResult->test->name,
            ]);
        return $messageModel;
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
