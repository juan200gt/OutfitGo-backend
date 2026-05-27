<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Clase de correo encargada de enviar las recomendaciones de prendas personalizadas a los usuarios suscritos a la newsletter.
class RecomendacionNewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $recomendaciones;
    public $frontendUrl;

    public function __construct($user, $recomendaciones, $frontendUrl)
    {
        $this->user = $user;
        $this->recomendaciones = $recomendaciones;
        $this->frontendUrl = $frontendUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recomendaciones de estilo exclusivas para ti - OutfitGo',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.newsletter.recomendacion',
        );
    }
}
