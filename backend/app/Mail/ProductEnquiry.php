<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
class ProductEnquiry extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public array $enquiry) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Product enquiry: '.$this->enquiry['product_title']); }
    public function content(): Content { return new Content(view: 'emails.product-enquiry'); }
}
