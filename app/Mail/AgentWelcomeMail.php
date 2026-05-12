<?php

namespace App\Mail;

use App\Models\Agent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Agent $agent,
        public string $passwordSetupUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'مرحباً بك في برنامج ولاء 29FLY',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.agent-welcome',
            with: [
                'businessName' => $this->agent->business_name,
                'fullName'     => $this->agent->user->full_name,
                'tier'         => $this->agent->current_tier,
                'setupUrl'     => $this->passwordSetupUrl,
            ],
        );
    }
}
