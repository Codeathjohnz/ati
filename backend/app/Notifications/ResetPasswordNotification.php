<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(private string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = rtrim(config('app.frontend_url'), '/')
            .'/reset-password?token='.$this->token
            .'&email='.urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Reset Your Digital Trainee Tracker Password')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We received a request to reset the password for your ATI-Caraga Digital Trainee Tracker account.')
            ->action('Reset Password', $url)
            ->line('This link expires in 60 minutes.')
            ->line("If you didn't request this, you can safely ignore this email — your password won't be changed.");
    }
}
