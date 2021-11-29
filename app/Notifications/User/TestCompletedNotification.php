<?php

namespace App\Notifications\User;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\TestResult;
use App\Db\Entity\User;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TestCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private TestResult $testResult;
    private ?string $name;
    private ?string $middleName;

    public function __construct(
        TestResult $testResult,
        ?string $name,
        ?string $middleName
    )
    {
        $this->testResult = $testResult;
        $this->name = $name;
        $this->middleName = $middleName;
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

        $messageModel
            ->setTemplate('emails.test.testCompleted')
            ->setSubject(trans('notifications.user.testCompleted'))
            ->setData([
                'candidateName' => $this->testResult->projectCandidate->candidate->name,
                'candidateSurname' => $this->testResult->projectCandidate->candidate->surname,
                'customerName' => $this->name,
                'customerMiddleName' => $this->middleName,
                'testName' => $this->testResult->test->name,
                'maximumScore' => $this->testResult->maximumTestResultValue,
                'passPointValue' => $this->testResult->test->pass_point_value,
                'candidateObtainedPoints' => $this->testResult->questionTotalValue,
                'correctAnswersCount' => $this->testResult->correctAnswersCount,
                'questionsCount' => $this->testResult->questionsCount,
                'wrongAnswersCount' => $this->testResult->wrongAnswersCount
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
        return $this->testResult->toArray();
    }
}
