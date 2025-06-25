<?php

namespace App\Notifications\API\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivationEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $url = url('/#/bekraeft-email?token='.$notifiable->activation_token);

        return (new MailMessage)
            ->greeting('Hej')
            ->subject('Bekræft din konto hos Rollespilsfabrikkens forum')
            ->line('Vi har modtaget en anmodning vedrørende oprettelse af en konto til Rollespilsfabrikkens forum linket til denne email adresse.')
            ->line('For at fuldføre opsætningen benyt linket herunder')
            ->action('Bekræft konto', url($url))
            ->line('Tak for at du anvender forummet');
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
