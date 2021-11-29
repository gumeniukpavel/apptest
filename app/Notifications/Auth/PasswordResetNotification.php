<?php

namespace App\Notifications\Auth;

use App\Db\Entity\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $resetUrl;

    public function __construct(string $token)
    {
        $this->resetUrl = config('app.front_url') . '/restore-password-confirm?token=' . $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param  User $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = "Password reset";

        if (empty($subject))
        {
            return null;
        }

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.resetPassword', [
                'resetUrl' => $this->resetUrl,
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
            $this->resetUrl
        ];
    }
}
