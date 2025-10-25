<?php

namespace App\Notifications;

use App\Models\Agenda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación enviada a un consejero cuando es asignado a un nuevo consejo.
 *
 * Implementa ShouldQueue para que el envío se procese en segundo plano,
 * mejorando el rendimiento y la experiencia del usuario (el director).
 */
class NewAgendaAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * El consejo al que el usuario ha sido asignado.
     * La promoción de propiedades del constructor de PHP 8+ hace esto más limpio.
     *
     * @var \App\Models\Agenda
     */
    public $agenda;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * @param  \App\Models\Agenda $agenda
     * @return void
     */
    public function __construct(Agenda $agenda)
    {
        $this->agenda = $agenda;
    }

    /**
     * Define los canales de entrega de la notificación.
     *
     * @param  mixed  $notifiable La entidad que está siendo notificada (el usuario Consejero).
     * @return array
     */
    public function via($notifiable): array
    {
        // Se enviará por correo electrónico y también se guardará en la base de datos
        // para un posible centro de notificaciones dentro de la aplicación.
        return ['mail', 'database'];
    }

    /**
     * Construye la representación de la notificación por correo electrónico.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        // El director se carga aquí para asegurar que esté disponible,
        // aunque normalmente ya vendrá con el modelo si se creó correctamente.
        $this->agenda->loadMissing('director');

        return (new MailMessage)
                    ->subject('Invitación a un nuevo consejo: ' . $this->agenda->name)
                    ->greeting('Hola, ' . $notifiable->name . '!')
                    ->line('Has sido invitado a participar en un nuevo consejo organizado por ' . $this->agenda->director->name . '.')
                    ->line('**Consejo:** ' . $this->agenda->name)
                    ->line('**Fecha programada:** ' . $this->agenda->date->format('d/m/Y \a \l\a\s H:i'))
                    ->action('Ver Detalles del Consejo', route('agendas.show', $this->agenda))
                    ->line('Puedes acceder a la plataforma para revisar los puntos a tratar y prepararte para las votaciones.');
    }

    /**
     * Construye la representación de la notificación para ser almacenada en la base de datos.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'agenda_id' => $this->agenda->id,
            'agenda_name' => $this->agenda->name,
            'message' => 'Has sido asignado al consejo "' . $this->agenda->name . '".',
            'url' => route('agendas.show', $this->agenda),
            'icon' => 'calendar-plus', // Un ícono de ejemplo para el frontend.
        ];
    }
}