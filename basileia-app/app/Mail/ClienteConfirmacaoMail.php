<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Cliente;

class ClienteConfirmacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public Venda $venda;
    public Pagamento $pagamento;
    public Cliente $cliente;

    public function __construct(Venda $venda, Pagamento $pagamento, Cliente $cliente)
    {
        $this->venda = $venda;
        $this->pagamento = $pagamento;
        $this->cliente = $cliente;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sua compra foi confirmada! — Basiléia',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.cliente-confirmacao',
        );
    }
}
