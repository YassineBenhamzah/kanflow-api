<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $payload,
        public int $boardId
    ) {}

    public function broadcastOn(): array {
        // Private channel — only board members can listen
        return [new PrivateChannel("board.{$this->boardId}")];
    }

    public function broadcastAs(): string {
        return 'task.moved';
    }

    public function broadcastWith(): array {
        // Send minimal payload — not full model!
        return $this->payload;
    }
}
