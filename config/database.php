<?php
if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

return [
    'default' => 'sqlite',
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => ROOT_PATH . '/database/forum.db',
        ],
    ],
];
