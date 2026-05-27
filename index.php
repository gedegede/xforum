<?php
define('ROOT_PATH', dirname(__FILE__));

require ROOT_PATH . '/lib/Autoloader.php';
use Lib\Autoloader;
use Lib\Template;
use Lib\Session;
use Lib\Request;
use Lib\Permission;
use Lib\ViewCounter;
use Lib\SettingsMiddleware;
use Models\SessionModel;
use Models\SettingModel;

Autoloader::register();

$appConfig = require ROOT_PATH . '/config/app.php';
$timezone = SettingModel::get('timezone', (string)($appConfig['timezone'] ?? 'UTC'));
if (!in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
    $timezone = (string)($appConfig['timezone'] ?? 'UTC');
}
date_default_timezone_set($timezone);

Template::init();

SettingsMiddleware::check();

ViewCounter::flushIfDue();

$uid = 0;
$gid = 0;
$invisible = 0;
if (Permission::isLoggedIn()) {
    $user = Session::getUser();
    if ($user) {
        $uid = $user['uid'];
        $gid = $user['gid'];
        $invisible = $user['invisible'];
    }
}

// 只在 POST 提交操作时更新在线状态，减少数据库写入
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    SessionModel::updateOnline($uid, $gid, $invisible);
}

$controller = Request::getString('c', 'home');
$action = Request::getString('a', 'index');

$controllerClass = 'Controllers\\' . ucfirst($controller) . 'Controller';

if (!class_exists($controllerClass)) {
    $controllerClass = 'Controllers\\HomeController';
    $action = 'index';
}

if (!method_exists($controllerClass, $action)) {
    $action = 'index';
}

$params = [];
$method = new ReflectionMethod($controllerClass, $action);
foreach ($method->getParameters() as $parameter) {
    $name = $parameter->getName();
    if (!Request::has($name)) {
        break;
    }
    $params[] = isset($_GET[$name]) ? Request::getInt($name) : Request::postInt($name);
}

call_user_func_array([$controllerClass, $action], $params);
?>
