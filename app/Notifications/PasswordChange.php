<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChange extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $html = "";

        $html .= "Esta mensagem é para te informar que a sua senha foi alterada em nosso site!";
        $html .= "<p>Se não foi você que fez esta alteração, nos informe através do e-mail <a href='mailto:" . env("MAIL_REPLY_TO") . "'>" . env("MAIL_REPLY_TO") . "</a>";

        return (new MailMessage)
            ->subject('Sua senha foi alterada')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line($html)
            ->markdown('vendor.notifications.email');
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
            //
        ];
    }
}
