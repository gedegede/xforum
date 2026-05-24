<?php
define('ROOT_PATH', dirname(__FILE__));

require ROOT_PATH . '/lib/Autoloader.php';
use Lib\Autoloader;
use Lib\Template;
use Lib\Session;
use Lib\Request;
use Lib\Permission;
use Models\SessionModel;

Autoloader::register();
Template::init();

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
if (Request::has('fid')) $params[] = Request::getInt('fid');
if (Request::has('tid')) $params[] = Request::getInt('tid');
if (Request::has('pid')) $params[] = Request::getInt('pid');
if (Request::has('gid')) $params[] = Request::getInt('gid');
if (Request::has('uid')) $params[] = Request::getInt('uid');

call_user_func_array([$controllerClass, $action], $params);
?>
