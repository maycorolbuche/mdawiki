<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\CryptHelper;

class VerificationCodeEmail extends Notification
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

        $html .= "Para validar seu e-mail, digite o código abaixo na tela de validação de e-mail:";
        $html .= "<div style='text-align:center;margin: 22px 0;'>";
        $html .= "<span style='background-color: #2d3748;color: #FFF;padding: 10px 30px;border-radius: 5px;font-size: 30px;letter-spacing: 10px;'>";
        $html .= $notifiable->verification_code;
        $html .= "</span>";
        $html .= "</div>";
        $html .= "<p>Ou, valide seu e-mail clicando no botão abaixo:</p>";

        return (new MailMessage)
            ->subject('Validação de E-mail')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line($html)
            ->action('VALIDAR CÓDIGO', route('validate', [
                'id_user_hash' => CryptHelper::encryptNumber($notifiable->id),
                'id_code_hash' => CryptHelper::encryptNumber($notifiable->verification_code),
            ]))
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
