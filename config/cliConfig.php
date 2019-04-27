<?php

declare(strict_types=1);

use BuzzingPixel\Work\Development;

$isDev = defined('SCRIBBLE_DEV') && SCRIBBLE_DEV === true;

if (! $isDev) {
    return [];
}

return [
    'scribble' => [
        'description' => 'Scribble dev',
        'commands' => [
            'dev' => [
                'class' => Development::class,
            ],
        ],
    ],
];
