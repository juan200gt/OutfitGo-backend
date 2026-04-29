<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecomendacionProductoMail extends Mailable
{
    use Queueable, SerializesModels;

public $user;
public $producto;

public function __construct($user, $producto)
{
    $this->user = $user;
    $this->producto = $producto;
}

public function envelope(): Envelope
{
    return new Envelope(
        subject: '¡Tenemos algo que te va a encantar!',
    );
}

public function content(): Content
{
    return new Content(
        view: 'mail.compras.recomendacion',
    );
}
}
