<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // optional
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RegistrationCredentialsNotification extends Notification // implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $email,
        public string $plainPassword
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('DigiStar Account Created - Login Credentials')
        ->greeting('Hi ' . ($notifiable->name ?? ''))
        ->line('Your DigiStar borrower account has been created.')
        ->line('Here are your login credentials:')
        ->line('Email: ' . $this->email)
        ->line('Password: ' . $this->plainPassword)
        ->action('Login to DigiStar', 'http://192.168.0.105:3000/login')
        ->line('For security, please change your password after logging in.');
}

}
