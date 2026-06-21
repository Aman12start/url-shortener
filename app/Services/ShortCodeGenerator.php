<?php

namespace App\Services;

use App\Models\Url;
use Illuminate\Support\Str;

class ShortCodeGenerator
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function unique(?int $length = null): string
    {
        $length ??= max(4, min((int) config('url-shortener.code_length', 8), 20));

        do {
            $code = $this->randomBase62($length);
        } while (Url::query()->where('short_code', $code)->exists());

        return $code;
    }

    private function randomBase62(int $length): string
    {
        $code = '';
        $max = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < $length; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        return $code ?: Str::random($length);
    }
}
