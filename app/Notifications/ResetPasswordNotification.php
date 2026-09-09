<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset.form', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset Password - '.config('app.name'))
            ->greeting("Halo, {$notifiable->name}!")
            ->line('Anda menerima email ini karena ada permintaan reset password untuk akun Anda.')
            ->action('Reset Password', $url)
            ->line('Tautan ini berlaku '.config('auth.passwords.users.expire', 60).' menit.')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini.');
    }
}
