<?php
declare(strict_types=1);

namespace Lib;

use Models\SettingModel;

class SettingsMiddleware {
    public static function check(): void {
        self::checkSiteClosed();
        self::checkAccessIp();
        self::checkAccessTime();
    }

    private static function checkSiteClosed(): void {
        $siteClosed = SettingModel::get('site_closed', '0');
        if ($siteClosed !== '1') {
            return;
        }

        $controller = Request::getString('c', '');
        $action = Request::getString('a', '');

        if ($controller === 'admin' || ($controller === 'auth' && $action === 'login')) {
            return;
        }

        $reason = SettingModel::get('site_closed_reason', '站点暂时关闭，敬请期待');
        
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>站点维护中</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px 60px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
        }
        h1 {
            font-size: 24px;
            margin: 0 0 16px;
            color: #1a1a1a;
        }
        p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
            margin: 0;
        }
        a {
            display: inline-block;
            margin-top: 24px;
            padding: 10px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 14px;
            transition: transform 0.2s;
        }
        a:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🛠️</div>
        <h1>站点维护中</h1>
        <p>' . htmlspecialchars($reason) . '</p>
        <a href="index.php?c=auth&a=login">管理员登录</a>
    </div>
</body>
</html>';
        exit;
    }

    private static function checkAccessIp(): void {
        $ip = self::getClientIp();

        $allowIps = SettingModel::get('allow_access_ips', '');
        if (!empty($allowIps)) {
            $allowed = false;
            foreach (explode("\n", $allowIps) as $pattern) {
                $pattern = trim($pattern);
                if (empty($pattern)) continue;
                if (self::matchIpPattern($ip, $pattern)) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed) {
                self::denyAccess('您的IP地址不在允许访问列表中');
            }
        }

        $blockIps = SettingModel::get('block_access_ips', '');
        if (!empty($blockIps)) {
            foreach (explode("\n", $blockIps) as $pattern) {
                $pattern = trim($pattern);
                if (empty($pattern)) continue;
                if (self::matchIpPattern($ip, $pattern)) {
                    self::denyAccess('您的IP地址已被禁止访问');
                }
            }
        }
    }

    private static function checkAccessTime(): void {
        $blockTime = SettingModel::get('block_access_time', '');
        if (empty($blockTime)) {
            return;
        }

        if (!preg_match('/^(\d{2}:\d{2})-(\d{2}:\d{2})$/', $blockTime, $matches)) {
            return;
        }

        list($startTime, $endTime) = [$matches[1], $matches[2]];
        $currentTime = date('H:i');

        if (self::isTimeInRange($currentTime, $startTime, $endTime)) {
            self::denyAccess('当前时间段禁止访问');
        }
    }

    private static function getClientIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private static function matchIpPattern(string $ip, string $pattern): bool {
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '\d+', $pattern);
        return preg_match("/^$pattern$/", $ip) === 1;
    }

    private static function isTimeInRange(string $current, string $start, string $end): bool {
        $currentMinutes = self::timeToMinutes($current);
        $startMinutes = self::timeToMinutes($start);
        $endMinutes = self::timeToMinutes($end);

        if ($startMinutes < $endMinutes) {
            return $currentMinutes >= $startMinutes && $currentMinutes < $endMinutes;
        } else {
            return $currentMinutes >= $startMinutes || $currentMinutes < $endMinutes;
        }
    }

    private static function timeToMinutes(string $time): int {
        list($hours, $minutes) = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }

    private static function denyAccess(string $message): void {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>访问被拒绝</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px 60px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
        }
        h1 {
            font-size: 24px;
            margin: 0 0 16px;
            color: #1a1a1a;
        }
        p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>访问被拒绝</h1>
        <p>' . htmlspecialchars($message) . '</p>
    </div>
</body>
</html>';
        exit;
    }
}
?>
