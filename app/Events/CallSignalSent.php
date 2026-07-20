<?php

namespace App\Events;

use App\Models\CallSignal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignalSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(public CallSignal $signal)
    {
        $signal->loadMissing(['sender', 'recipient']);
        $this->payload = [
            'id' => $signal->id,
            'call_id' => $signal->call_session_id,
            'callId' => $signal->call_session_id,
            'sender_id' => $signal->sender_id,
            'senderId' => $signal->sender_id,
            'recipient_id' => $signal->recipient_id,
            'recipientId' => $signal->recipient_id,
            'signal_type' => $signal->signal_type,
            'signalType' => $signal->signal_type,
            'type' => $signal->signal_type,
            'payload' => $signal->payload ?? [],
            'created_at' => $signal->created_at?->toISOString(),
            'createdAt' => $signal->created_at?->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.signal.sent';
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('calls.'.$this->signal->call_session_id)];

        if ($this->signal->recipient_id) {
            $channels[] = new PrivateChannel('users.'.$this->signal->recipient_id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return ['signal' => $this->payload];
    }
}
