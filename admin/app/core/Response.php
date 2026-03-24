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

    /** @param array<string, mixed> $data */
    public static function json(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
