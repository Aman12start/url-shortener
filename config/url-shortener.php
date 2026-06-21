<?php

return [
    'base_url' => env('SHORT_URL_BASE_URL', env('APP_URL')),
    'code_length' => (int) env('SHORT_CODE_LENGTH', 8),
];
