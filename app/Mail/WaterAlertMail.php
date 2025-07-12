<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaterAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alertType;
    public $meterId;
    public $currentConsumption;
    public $threshold;
    public $previousConsumption; // Specific for leak alert

    /**
     * Create a new message instance.
     *
     * @param string $alertType 'leak' or 'high_consumption'
     * @param string $meterId The ID of the meter
     * @param float $currentConsumption Today's consumption
     * @param float|null $threshold The user's set threshold (for high consumption)
     * @param float|null $previousConsumption Yesterday's consumption (for leak detection)
     */
    public function __construct(
        $alertType,
        $meterId,
        $currentConsumption,
        $threshold = null,
        $previousConsumption = null
    ) {
        $this->alertType = $alertType;
        $this->meterId = $meterId;
        $this->currentConsumption = $currentConsumption;
        $this->threshold = $threshold;
        $this->previousConsumption = $previousConsumption;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = '';
        if ($this->alertType === 'leak') {
            $subject = "💧 Potential Water Leak Alert for Meter: {$this->meterId}";
        } elseif ($this->alertType === 'high_consumption') {
            $subject = "📈 High Water Consumption Alert for Meter: {$this->meterId}";
        } else {
            $subject = "Water Alert for Meter: {$this->meterId}";
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.water-alert', // This points to resources/views/emails/water-alert.blade.php
            with: [
                'alertType' => $this->alertType,
                'meterId' => $this->meterId,
                'currentConsumption' => $this->currentConsumption,
                'threshold' => $this->threshold,
                'previousConsumption' => $this->previousConsumption,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}