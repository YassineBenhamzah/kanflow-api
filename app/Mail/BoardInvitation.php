<?php

namespace App\Mail;

use App\Models\Board;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoardInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Board $board;
    public User $invitee;
    public User $inviter;

    /**
     * Create a new message instance.
     */
    public function __construct(Board $board, User $invitee, User $inviter)
    {
        $this->board = $board;
        $this->invitee = $invitee;
        $this->inviter = $inviter;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->inviter->name} invited you to collaborate on '{$this->board->name}'",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.boards.invitation',
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
