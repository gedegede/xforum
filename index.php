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
use Lib\CsrfHelper;
use Models\SessionModel;
use Models\SettingModel;
use Models\MemberModel;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CsrfHelper::check();
}

$uid = 0;
$gid = 0;
$invisible = 0;
if (Permission::isLoggedIn()) {
    $user = Session::getUser();
    if ($user) {
        $uid = $user['uid'];
        $gid = $user['gid'];
        $invisible = $user['invisible'];
        MemberModel::touchVisit((int)$uid, $_SERVER['REMOTE_ADDR'] ?? '');
    }
}

// 只在 POST 提交操作时更新在线状态，减少数据库写入
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    SessionModel::updateOnline($uid, $gid, $invisible);
}

$controller = Request::getString('c', 'home');
$action = Request::getString('a', 'index');
if (!preg_match('/^[A-Za-z0-9_]+$/', $controller)) {
    $controller = 'home';
}
if (!preg_match('/^[A-Za-z0-9_]+$/', $action)) {
    $action = 'index';
}

$controllerClass = 'Controllers\\' . ucfirst($controller) . 'Controller';

if (!class_exists($controllerClass)) {
    $controllerClass = 'Controllers\\HomeController';
    $action = 'index';
}

if (!method_exists($controllerClass, $action)) {
    $action = 'index';
}

$method = new ReflectionMethod($controllerClass, $action);
if (!$method->isPublic() || $method->getName() !== $action) {
    $action = 'index';
    $method = new ReflectionMethod($controllerClass, $action);
}

$params = [];
foreach ($method->getParameters() as $parameter) {
    $name = $parameter->getName();
    if (!Request::has($name)) {
        break;
    }
    $params[] = isset($_GET[$name]) ? Request::getInt($name) : Request::postInt($name);
}

call_user_func_array([$controllerClass, $action], $params);
?>
