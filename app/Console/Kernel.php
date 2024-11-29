<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Events\GameStateUpdate;
use App\Models\ActivePlayer;
use App\Http\Controllers\PuzzleController;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            event(new GameStateUpdate(
                (new PuzzleController)->generateNewPuzzle(),
                30,
                ActivePlayer::orderByDesc('streak')->take(10)->get()
            ));
        })->everyThirtySeconds();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
