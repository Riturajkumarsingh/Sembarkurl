<?php

namespace App\Services;

use App\Models\ShortUrl;
use Illuminate\Support\Str;
use RuntimeException;

class ShortCodeGenerator
{
    private const MIN_LENGTH = 6;

    private const MAX_RETRIES = 10;

    public function generate(): string
    {
        $attempts = 0;
        $exists = false;

        do {
            // Vary length slightly so codes aren't all the same length over time.
            $length = random_int(self::MIN_LENGTH, 8);
            $code = Str::random($length);
            $exists = ShortUrl::where('short_code', $code)->exists();
            $attempts++;
        } while ($exists && $attempts < self::MAX_RETRIES);

        if ($exists) {
            throw new RuntimeException(
                'Could not generate a unique short code after '.self::MAX_RETRIES.' attempts.'
            );
        }

        return $code;
    }
}
