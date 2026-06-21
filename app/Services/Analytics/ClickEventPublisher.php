<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Log;

class ClickEventPublisher
{
    public function publish(array $payload): void
    {
        $driver = config('kafka.driver', 'log');

        if ($driver === 'log') {
            Log::channel(config('kafka.log_channel', 'stack'))->info('url.click', [
                'topic' => config('kafka.topic'),
                'payload' => $payload,
            ]);

            return;
        }

        Log::warning('Kafka driver is not configured; click event skipped.', [
            'driver' => $driver,
            'topic' => config('kafka.topic'),
        ]);
    }
}
