<?php

namespace App\Notifications\API\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
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
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('/#/nulstil-password?token='.$this->token);

        return (new MailMessage)
            ->subject('Nulstil din adgangskode')
            ->greeting('Hej')
            ->line('Du modtager denne mail da vi har modtaget en anmodning om at nulstille din adgangskode')
            ->action('Nulstil adgangskode: ', url($url))
            ->line('Hvis det ikke var dig der lavede denne anmodning, så skal du intet gøre.');
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
