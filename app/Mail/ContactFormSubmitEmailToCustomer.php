<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
class ContactFormSubmitEmailToCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    public $filePaths;

    /**
     * Create a new message instance.
     */
    public function __construct($mailData, $filePaths)
    {
        $this->mailData = $mailData;
        $this->filePaths = $filePaths;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A new email has been sent to admin',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contactFormSubmitEmailToCustomer'
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Add each file as an attachment
         // Add each file as an attachment
         foreach ($this->filePaths as $file) {
            $attachments[] = Attachment::fromStorageDisk('public', $file);
        }

        return $attachments;
    }
}
