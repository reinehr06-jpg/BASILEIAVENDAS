<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Venda;

class VendaConfirmadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Venda $venda;
    public float $comissao;
    public string $linkVenda;

    public function __construct(Venda $venda, float $comissao, string $linkVenda)
    {
        $this->venda = $venda;
        $this->comissao = $comissao;
        $this->linkVenda = $linkVenda;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pagamento confirmado da venda — Basiléia Vendas',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.venda-confirmada',
        );
    }
}
