<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class Response {
    public static function isAjaxRequest(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $url, int $statusCode = 302): void {
        http_response_code($statusCode);
        header('Location: ' . $url);
        exit;
    }

    public static function error(string $message, int $statusCode = 400, array $extra = []): void {
        self::json(array_merge([
            'success' => false,
            'message' => $message,
        ], $extra), $statusCode);
    }
}
?>