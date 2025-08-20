# Laravel Event Listener Bug Reproduction Instructions

**Goal**: Create a minimal Laravel repository to reproduce the event listener duplicate execution bug.

**Repository**: `/Users/scottgrayson/Code/event-bug` (fresh Laravel 12 installation)

**Files to create/edit**:

## 1. `app/Events/TestEvent.php`
```php
<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $message = 'test'
    ) {}
}
```

## 2. `app/Listeners/TestListener.php`
```php
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
```

## 3. `app/Providers/EventServiceProvider.php`
```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\TestEvent::class => [
            \App\Listeners\TestListener::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
```

## 4. `app/Console/Commands/TestEventCommand.php`
```php
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
```

## 5. `bootstrap/providers.php`
Make sure `App\Providers\EventServiceProvider::class` is in the providers array.

## 6. `README.md`
Create a README explaining the bug reproduction steps.

**Testing Steps**:
1. Run `php artisan event:list | grep TestEvent` - should show both class and @handle registrations
2. Run `php artisan test:event "hello world"`
3. Check `storage/logs/laravel.log` for duplicate log entries

**Expected Bug**: The listener should be called twice, showing duplicate log entries with the same timestamp.

This will create a clean, minimal reproduction of the Laravel event listener duplicate execution bug.
