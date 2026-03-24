<?php

declare(strict_types=1);

namespace Revita\Crm\Helpers;

final class Escape
{
    public static function html(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
