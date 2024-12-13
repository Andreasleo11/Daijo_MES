<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ParentDataUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $payload;

    /**
     * Create a new event instance.
     */
    public function __construct($parents, $childs, $materialLogs, $mouldingUserLogs)
    {
        $this->payload = [
            'parents' => $parents,
            'childs' => $childs,
            'materialLogs' => $materialLogs,
            'mouldingUserLogs' => $mouldingUserLogs,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return ['dashboard-data'];
    }

    public function broadcastWith() : array
    {
        return $this->payload;
    }
}
