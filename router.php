<?php
// 处理 PHP 内置服务器的路由
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . $requestUri;

// 如果是文件且存在，直接返回
if (file_exists($filePath) && is_file($filePath)) {
    return false; // 告诉 PHP 内置服务器直接返回文件
}

// 否则走 index.php 处理
require 'index.php';
?>
