<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduledDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $digestName;
    public $htmlContent;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(string $digestName, string $htmlContent, ?string $pdfPath = null)
    {
        $this->digestName = $digestName;
        $this->htmlContent = $htmlContent;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->digestName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.scheduled-digest',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            return [
                Attachment::fromPath($this->pdfPath)
                    ->as(str_replace(' ', '_', $this->digestName) . '.pdf')
                    ->mime('application/pdf'),
            ];
        }

        return [];
    }
}
