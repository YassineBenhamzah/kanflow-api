<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $payload,
        public int $boardId
    ) {}

    public function broadcastOn(): array {
        return [new PrivateChannel("board.{$this->boardId}")];
    }

    public function broadcastAs(): string {
        return 'task.created';
    }

    public function broadcastWith(): array {
        return $this->payload;
    }
}
