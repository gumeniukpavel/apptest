<?php

namespace App\Notifications\User;

use App\Broadcasting\TemplatedEmailChannel;
use App\Constant\ApprovalRequestStatus;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\TestApprovalRequest;
use App\Db\Entity\User;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ExpertAcceptedTestApprovalRequestsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private TestApprovalRequest $testApprovalRequest;

    public function __construct(
        TestApprovalRequest $testApprovalRequest
    )
    {
        $this->testApprovalRequest = $testApprovalRequest;
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

        if ($this->testApprovalRequest->status == ApprovalRequestStatus::$Approved->getValue())
        {
            $messageModel->setTemplate('emails.approvalRequests.expertAcceptedTestApprovalRequest')
                ->setSubject(trans('notifications.user.expertAcceptedTestApprovalRequests'));
        }
        else
        {
            $messageModel->setTemplate('emails.approvalRequests.expertCanceledTestApprovalRequest')
                ->setSubject(trans('notifications.user.expertCanceledTestApprovalRequests'));
        }
            $messageModel->setData([
                'customerName' => $this->testApprovalRequest->user->name,
                'customerMiddleName' => $this->testApprovalRequest->user->middle_name,
                'expertName' => $this->testApprovalRequest->expert->name,
                'expertSurname' => $this->testApprovalRequest->expert->surname,
                'candidateName' => $this->testApprovalRequest->candidate->name,
                'candidateSurname' => $this->testApprovalRequest->candidate->surname,
                'testName' => $this->testApprovalRequest->testResult->test->name,
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
