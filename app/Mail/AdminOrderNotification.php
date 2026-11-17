<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOrderNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova encomenda '.$this->order->reference,
            replyTo: [
                new Address($this->order->email, $this->order->full_name),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_order_notification',
            with: [
                'order' => $this->order,
                'bank' => SiteSetting::bankAccount(),
            ],
        );
    }
}
