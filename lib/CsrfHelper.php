<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class CsrfHelper {
    private const TOKEN_NAME = 'csrf_token';

    public static function generate(): string {
        if (!Session::isLoggedIn()) {
            return '';
        }
        
        $existingToken = Session::get(self::TOKEN_NAME);
        if ($existingToken) {
            return $existingToken;
        }
        
        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_NAME, $token);
        return $token;
    }

    public static function getToken(): string {
        return Session::get(self::TOKEN_NAME, '');
    }

    public static function field(): string {
        $token = self::getToken();
        if (empty($token)) {
            return '';
        }
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(?string $token = null): bool {
        if (!Session::isLoggedIn()) {
            return true;
        }
        
        if ($token === null) {
            $token = Request::postRaw('csrf_token', Request::getRaw('csrf_token', ''));
        }
        
        if (empty($token)) {
            return false;
        }
        
        $sessionToken = self::getToken();
        return hash_equals($sessionToken, $token);
    }

    public static function check(): bool {
        if (!self::validate()) {
            if (Response::isAjaxRequest()) {
                Response::error('CSRF token validation failed', 403);
            }
            Response::error('CSRF token validation failed', 403);
        }
        return true;
    }

    public static function refresh(): void {
        if (!Session::isLoggedIn()) {
            return;
        }
        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_NAME, $token);
    }

    public static function remove(): void {
        Session::delete(self::TOKEN_NAME);
    }
}
