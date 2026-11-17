<?php

namespace App\Mail;

use App\Mail\Concerns\AttachesPaymentProof;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentProofSubmittedMail extends Mailable
{
    use AttachesPaymentProof;
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comprovativo de transferência — '.$this->order->reference,
            replyTo: [
                new Address($this->order->email, $this->order->full_name),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment_proof');
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return $this->paymentProofAttachments();
    }
}
