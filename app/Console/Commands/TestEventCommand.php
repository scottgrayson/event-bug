<?php

namespace App\Console\Commands;

use App\Events\TestEvent;
use Illuminate\Console\Command;

class TestEventCommand extends Command
{
    protected $signature = 'test:event {message?}';
    protected $description = 'Dispatch a test event to demonstrate the duplicate listener bug';

    public function handle()
    {
        $message = $this->argument('message') ?? 'test message';

        $this->info("Dispatching TestEvent with message: {$message}");

        TestEvent::dispatch($message);

        $this->info('Event dispatched! Check the logs to see if the listener was called twice.');
        $this->info('Run: tail -f storage/logs/laravel.log');

        return Command::SUCCESS;
    }
}
