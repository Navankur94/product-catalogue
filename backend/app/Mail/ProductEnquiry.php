<?php

declare(strict_types=1);

namespace App\Mail;

use App\Data\Product;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sales notification for a customer product enquiry. Transport (log, SMTP, ...)
 * is chosen by MAIL_MAILER; this class only defines the message.
 */
final class ProductEnquiry extends Mailable
{
    public function __construct(
        public readonly Product $product,
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly ?string $customerPhone,
        public readonly ?string $customerMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Product enquiry: '.$this->product->title,
            replyTo: [new Address($this->customerEmail, $this->customerName)],
        );
    }

    public function content(): Content
    {
        // Note: `$message` is reserved in mail views (Illuminate\Mail\Message), hence `customerMessage`.
        return new Content(view: 'emails.product-enquiry');
    }
}
