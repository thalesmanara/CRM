<?php

declare(strict_types=1);

use Revita\Crm\Controllers\PagesApiController;

return [
    'GET' => [
        '/api/pages' => [PagesApiController::class, 'index'],
    ],
    '_patterns' => [
        'GET' => [
            [
                'pattern' => '#^/api/pages/([^/]+)$#',
                'handler' => [PagesApiController::class, 'show'],
                'params' => ['slug'],
            ],
        ],
    ],
];
