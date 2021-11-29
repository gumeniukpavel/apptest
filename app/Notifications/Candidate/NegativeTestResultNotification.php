<?php

namespace App\Notifications\Candidate;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\Candidate;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\TestResult;
use App\Db\Entity\User;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NegativeTestResultNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private TestResult $testResult;
    private string $customerName;
    private ?LetterTemplate $template;

    public function __construct(
        TestResult $testResult,
        string $customerName,
        ?LetterTemplate $template
    )
    {
        $this->testResult = $testResult;
        $this->customerName = $customerName;
        $this->template = $template;
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
        $messageModel->setTemplate('emails.test.negativeResult')
            ->setData([
                'testName' => $this->testResult->test->name,
                'categoryName' => $this->testResult->test->category->name,
                'candidateObtainedPoints' => $this->testResult->questionTotalValue,
                'maximumScore' => $this->testResult->maximumTestResultValue,
                'customerName' => $this->customerName
            ]);

        if ($this->template)
        {
            return $messageModel->setSubject($this->template->subject)
                ->setBody($this->template->body);
        }

        return $messageModel->setSubject(trans('notifications.candidate.negativeTestResult'));
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
            'candidate' => $notifiable->toArray(),
            'customerName' => $this->customerName,
        ];
    }
}
