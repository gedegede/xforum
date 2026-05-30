<?php
declare(strict_types=1);

namespace Controllers;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Request;
use Lib\Response;

class MiniappController {
    public static function index(): void {
        self::redirectToHome();
    }

    public static function home(): void {
        self::redirectToHome();
    }

    public static function forums(): void {
        Response::redirect('index.php?c=forum&a=index');
    }

    public static function threads(): void {
        $fid = Request::getInt('fid');
        if ($fid > 0) {
            Response::redirect('index.php?c=forum&a=index&fid=' . $fid . self::buildQuery(['page', 'order', 'keyword']));
        }

        Response::redirect('index.php?c=forum&a=index');
    }

    public static function thread(): void {
        $tid = Request::getInt('tid');
        if ($tid > 0) {
            Response::redirect('index.php?c=thread&a=index&tid=' . $tid . self::buildQuery(['page', 'pid']));
        }

        self::redirectToHome();
    }

    private static function redirectToHome(): void {
        Response::redirect('index.php' . self::buildQuery(['page', 'order', 'keyword'], '?'));
    }

    private static function buildQuery(array $keys, string $prefix = '&'): string {
        $params = [];
        foreach ($keys as $key) {
            $value = Request::getString($key);
            if ($value !== '') {
                $params[$key] = $value;
            }
        }
        return empty($params) ? '' : $prefix . http_build_query($params);
    }
}
?>
