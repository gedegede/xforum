<?php
if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

return [
    'default' => 'mysql',
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => ROOT_PATH . '/database/forum.db',
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'xforum',
            'username' => 'xforum',
            'password' => 'tP-camy3ejj',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_0900_as_cs',
        ],
    ],
];
