<?php

declare(strict_types=1);

namespace Revita\Crm\Helpers;

final class Youtube
{
    public static function extractId(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
            return $input;
        }
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)([a-zA-Z0-9_-]{11})~', $input, $m)) {
            return $m[1];
        }
        if (preg_match('~[?&]v=([a-zA-Z0-9_-]{11})~', $input, $m)) {
            return $m[1];
        }
        return null;
    }
}
