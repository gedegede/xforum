<?php
return [
    'default' => 'sqlite',
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => ROOT_PATH . '/database/forum.db',
        ],
    ],
];
