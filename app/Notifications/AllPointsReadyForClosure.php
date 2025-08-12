<?php

namespace App\Notifications;

use App\Models\Council;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AllPointsReadyForClosure extends Notification
{
    use Queueable;

    public function __construct(public Council $council)
    {
    }

    public function via($notifiable): array
    {
        return ['mail']; // Puedes añadir otros canales como 'database'.
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Consejo listo para cierre: ' . $this->council->name)
                    ->line('Se ha alcanzado el número mínimo de votos en todos los puntos del consejo "' . $this->council->name . '".')
                    ->line('Ya puede proceder a revisar los resultados y cerrar oficialmente el consejo.')
                    ->action('Ver Consejo', route('councils.show', $this->council))
                    ->line('Gracias por utilizar nuestra aplicación.');
    }
}
