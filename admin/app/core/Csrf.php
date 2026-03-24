<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        $t = Session::get(self::KEY);
        if (!is_string($t) || $t === '') {
            $t = bin2hex(random_bytes(32));
            Session::set(self::KEY, $t);
        }
        return $t;
    }

    public static function validate(?string $submitted): bool
    {
        $expected = Session::get(self::KEY);
        if (!is_string($expected) || $expected === '' || !is_string($submitted)) {
            return false;
        }
        return hash_equals($expected, $submitted);
    }
}
