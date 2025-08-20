<?php

namespace App\Listeners;

use App\Events\TestEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class TestListener implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(TestEvent $event): void
    {
        Log::info('TestListener called', [
            'message' => $event->message,
            'timestamp' => now()->toISOString(),
            'memory_usage' => memory_get_usage(true),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
        ]);
    }
}
