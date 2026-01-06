<?php

namespace Modules\AgendaConsejo\Notifications;

use Modules\AgendaConsejo\Models\Agenda;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AllPointsReadyForClosure extends Notification
{
    use Queueable;

    public function __construct(public Agenda $agenda)
    {
    }

    public function via($notifiable): array
    {
        return ['mail']; // Puedes añadir otros canales como 'database'.
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Consejo listo para cierre: ' . $this->agenda->name)
                    ->line('Se ha alcanzado el número mínimo de votos en todos los puntos del consejo "' . $this->agenda->name . '".')
                    ->line('Ya puede proceder a revisar los resultados y cerrar oficialmente el consejo.')
                    ->action('Ver Consejo', route('agendas.show', $this->agenda))
                    ->line('Gracias por utilizar nuestra aplicación.');
    }
}
