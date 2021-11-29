<?php

namespace App\Notifications\Candidate;

use App\Broadcasting\TemplatedEmailChannel;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\TestResult;
use App\Db\Entity\User;
use App\Db\Entity\UserProfile;
use App\Models\Notifications\EmailMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class QuestionnaireResultNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private TestResult $testResult;
    private UserProfile $userProfile;
    private ?LetterTemplate $template;

    public function __construct(
        TestResult $testResult,
        UserProfile $userProfile,
        ?LetterTemplate $template
    )
    {
        $this->testResult = $testResult;
        $this->userProfile = $userProfile;
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
        $messageModel
            ->setTemplate('emails.questionnaire.questionnaireResult')
            ->setData([
                'candidateName' => $this->testResult->projectCandidate->candidate->name,
                'testName' => $this->testResult->test->name,
                'categoryName' => $this->testResult->test->category->name,
                'maximumScore' => $this->testResult->maximumTestResultValue,
                'passPointValue' => $this->testResult->test->pass_point_value,
                'candidateObtainedPoints' => $this->testResult->questionTotalValue,
                'correctAnswersCount' => $this->testResult->correctAnswersCount,
                'questionsCount' => $this->testResult->questionsCount,
                'wrongAnswersCount' => $this->testResult->wrongAnswersCount,
                'customerName' => $this->userProfile->user->name,
                'customerSurname' => $this->userProfile->user->surname,
                'companyName' => $this->userProfile->organization_name,
                'customerPhone' => $this->userProfile->user->phone,
                'customerEmail' => $this->userProfile->user->email,
            ]);

        if ($this->template)
        {
            return $messageModel->setSubject($this->template->subject)
                ->setBody($this->template->body);
        }
        return $messageModel->setSubject(trans('notifications.candidate.questionnaireResult'));
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
            'testResult' => $this->testResult->toArray(),
        ];
    }
}
