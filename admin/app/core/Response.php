<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

final class Response
{
    public static function html(string $body): never
    {
        header('Content-Type: text/html; charset=UTF-8');
        echo $body;
        exit;
    }
}
