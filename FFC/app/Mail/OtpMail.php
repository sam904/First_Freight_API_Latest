<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otpCode;
    public $user;
    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $otpCode)
    {
        Log::info("otp => " . $otpCode);
        $this->otpCode = $otpCode;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your OTP Code',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info("content otp => " . $this->user->first_name . ' ' . $this->user->last_name);
        return new Content(
            view: 'emails.otp', // Specify the correct view for the email
            with: [
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
                'otpCode' => $this->otpCode, // Pass the OTP code to the view
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
