<?php

return [
    'driver' => env('KAFKA_DRIVER', 'log'),
    'brokers' => env('KAFKA_BROKERS', '127.0.0.1:9092'),
    'topic' => env('KAFKA_CLICK_TOPIC', 'url-clicks'),
    'log_channel' => env('KAFKA_LOG_CHANNEL', 'stack'),
];
