<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    if (strpos($class, 'Revita\\Crm\\') !== 0) {
        return;
    }
    $rel = substr($class, strlen('Revita\\Crm\\'));
    $parts = explode('\\', $rel);
    $first = array_shift($parts);
    $map = [
        'Core' => 'core',
        'Controllers' => 'controllers',
        'Models' => 'models',
        'Helpers' => 'helpers',
        'Services' => 'services',
    ];
    if (!isset($map[$first])) {
        return;
    }
    $dir = $map[$first];
    $rest = $parts !== [] ? implode('/', $parts) : '';
    $file = REVITA_CRM_ROOT . '/app/' . $dir . ($rest !== '' ? '/' . $rest : '') . '.php';
    if (is_file($file)) {
        require $file;
    }
});
