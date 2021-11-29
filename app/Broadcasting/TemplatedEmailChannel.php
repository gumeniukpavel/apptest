<?php

namespace App\Broadcasting;

use App\Db\Entity\Candidate;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\User;
use App\Mail\TemplatedEmail;
use App\Models\Notifications\EmailMessageModel;
use App\Service\AuthService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TemplatedEmailChannel
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  User  $user
     * @return array|bool
     */
    public function join(User $user)
    {
        return true;
    }

    /**
     * Send the given notification.
     *
     * @param User|Candidate $candidate
     * @param Notification $notification
     * @return void
     * @throws \ReflectionException
     */
    public function send($candidate, Notification $notification)
    {
        $emailModel = $notification->toEmail();
        if (empty($emailModel))
        {
            return;
        }
        if ($emailModel instanceof EmailMessageModel)
        {
            $data = array_merge([
                'fullName' => "$candidate->surname $candidate->name $candidate->middle_name",
                'firstName' => $candidate->name,
                'middleName' => $candidate->middle_name,
                'surname' => $candidate->surname,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
            ], $emailModel->data);

            Mail::to($candidate)
                ->send(new TemplatedEmail($emailModel->template, $emailModel->body, $emailModel->subject, $data));
        }
        else
        {
            Log::error('The email message is not instance of EmailMessage');
        }
    }
}

