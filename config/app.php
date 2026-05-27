<?php
if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

return [
    'name' => 'XForum',
    'url' => 'http://localhost',
    'timezone' => 'Asia/Shanghai',
    'debug' => false,
    'cookie_prefix' => 'xf_',
    'cookie_expire' => 86400 * 7,
];
