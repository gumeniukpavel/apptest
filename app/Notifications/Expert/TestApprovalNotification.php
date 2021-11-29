<?php

namespace App\Notifications\Expert;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Candidate;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\TestApprovalRequest;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TestApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private TestApprovalRequest $testApprovalRequest;
    private string $verify;
    private string $cancel;

    public function __construct(
        TestApprovalRequest $testApprovalRequest
    )
    {
        $this->testApprovalRequest = $testApprovalRequest;
        $this->verify = config('app.front_url') . '/expert/verify?token=' . $this->testApprovalRequest->access_token_verify . '&type=test';
        $this->cancel = config('app.front_url') . '/expert/cancel?token=' . $this->testApprovalRequest->access_token_cancel . '&type=test';
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

        return $messageModel->setTemplate('emails.test.testApprovalRequestsInvitation')
            ->setSubject(trans('notifications.expert.testApproval'))
            ->setData([
                'expertName' => $this->testApprovalRequest->expert->name,
                'expertSurname' => $this->testApprovalRequest->expert->surname,
                'expertMiddleName' => $this->testApprovalRequest->expert->middle_name,
                'candidateName' => $this->testApprovalRequest->candidate->name,
                'candidateSurname' => $this->testApprovalRequest->candidate->surname,
                'testName' => $this->testApprovalRequest->testResult->test->name,
                'verifyUrl' => $this->verify,
                'cancelUrl' => $this->cancel,
                'customerName' => $this->testApprovalRequest->user->name,
                'customerSurname' => $this->testApprovalRequest->user->surname,
                'companyName' => $this->testApprovalRequest->user->profile->organization_name,
                'customerPhone' => $this->testApprovalRequest->user->phone,
                'customerEmail' => $this->testApprovalRequest->user->email,
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
