<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatAvailabilityChanged implements ShouldBroadcastNow, ShouldDispatchAfterCommit, ShouldRescue
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  list<int>  $seatIds
     */
    public function __construct(
        public int $showtimeId,
        public array $seatIds,
        public string $status,
        public ?string $expiresAt = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('showtime.'.$this->showtimeId)];
    }

    public function broadcastAs(): string
    {
        return 'seat.availability.changed';
    }

    /**
     * @return array{showtime_id: int, seat_ids: list<int>, status: string, expires_at: ?string}
     */
    public function broadcastWith(): array
    {
        return [
            'showtime_id' => $this->showtimeId,
            'seat_ids' => $this->seatIds,
            'status' => $this->status,
            'expires_at' => $this->expiresAt,
        ];
    }
}
