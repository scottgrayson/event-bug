# Laravel Event Listener Bug Reproduction

This repository demonstrates a bug in Laravel where event listeners are executed twice due to duplicate event registrations.

## Bug Description

When using Laravel's event system with listeners that implement `ShouldQueue`, the listener may be called twice due to both class-based and method-based event registrations being present.

## For Laravel Maintainers

### Relevant Files to Review

**Core Laravel Framework Files:**
- `vendor/laravel/framework/src/Illuminate/Events/EventServiceProvider.php` - Event registration logic
- `vendor/laravel/framework/src/Illuminate/Events/Dispatcher.php` - Event dispatching logic
- `vendor/laravel/framework/src/Illuminate/Events/CallQueuedListener.php` - Queued listener execution

**Application Files (this repository):**
- `app/Events/TestEvent.php` - Simple test event
- `app/Listeners/TestListener.php` - Listener that implements ShouldQueue and logs execution
- `app/Providers/EventServiceProvider.php` - Event service provider with explicit listener registration
- `app/Console/Commands/TestEventCommand.php` - Artisan command to dispatch the test event

### Steps to Reproduce

1. **Clone and setup this repository:**
   ```bash
   git clone <repository-url>
   cd event-bug
   composer install
   ```

2. **Check event registrations** (this should show both class and @handle registrations):
   ```bash
   php artisan event:list | grep TestEvent
   ```
   **Expected output shows duplicate registrations:**
   ```
   App\Events\TestEvent ...................................................................................
   ⇂ App\Listeners\TestListener (ShouldQueue)  
   ⇂ App\Listeners\TestListener@handle (ShouldQueue)  
   ```

3. **Dispatch the test event:**
   ```bash
   php artisan test:event "hello world"
   ```

4. **Process the queued jobs** (run twice to see both executions):
   ```bash
   php artisan queue:work --once
   php artisan queue:work --once
   ```

5. **Check the logs for duplicate entries:**
   ```bash
   cat storage/logs/laravel.log
   ```

### Expected Bug Behavior

The listener should be called twice, showing duplicate log entries with the same message but different timestamps, indicating the bug where the same listener is executed multiple times for a single event dispatch.

**Example log output:**
```
[2025-08-20 16:50:03] local.INFO: TestListener called {"message":"hello world","timestamp":"2025-08-20T16:50:03.141733Z",...}
[2025-08-20 16:50:06] local.INFO: TestListener called {"message":"hello world","timestamp":"2025-08-20T16:50:06.858048Z",...}
```

### Root Cause Analysis

The issue appears to be in Laravel's event registration system where:
1. The listener is registered as a class: `App\Listeners\TestListener`
2. The listener is also registered as a method: `App\Listeners\TestListener@handle`
3. Both registrations are processed, causing the same listener to be executed twice

This affects listeners that implement `ShouldQueue` and are processed through the queue system.

## Files Created

- `app/Events/TestEvent.php` - A simple test event
- `app/Listeners/TestListener.php` - A listener that implements ShouldQueue and logs execution
- `app/Providers/EventServiceProvider.php` - Event service provider with explicit listener registration
- `app/Console/Commands/TestEventCommand.php` - Artisan command to dispatch the test event

## Files Modified

- `bootstrap/providers.php` - Already contains EventServiceProvider registration
