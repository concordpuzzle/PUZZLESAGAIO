<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStateUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $puzzle;
    public $timeLeft;
    public $leaderboard;

    public function __construct($puzzle, $timeLeft, $leaderboard)
    {
        $this->puzzle = $puzzle;
        $this->timeLeft = $timeLeft;
        $this->leaderboard = $leaderboard;
    }

    public function broadcastOn()
    {
        return new Channel('game');
    }
}
