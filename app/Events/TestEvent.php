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
