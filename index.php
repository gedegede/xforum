<?php
define('ROOT_PATH', dirname(__FILE__));

require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/lib/Autoloader.php';
Lib\Autoloader::register();

Lib\Template::init();

$controller = isset($_GET['c']) ? $_GET['c'] : 'home';
$action = isset($_GET['a']) ? $_GET['a'] : 'index';

$controllerClass = 'Controllers\\' . ucfirst($controller) . 'Controller';

if (!class_exists($controllerClass)) {
    $controllerClass = 'Controllers\\HomeController';
    $action = 'index';
}

if (!method_exists($controllerClass, $action)) {
    $action = 'index';
}

$params = [];
if (isset($_GET['fid'])) $params[] = (int)$_GET['fid'];
if (isset($_GET['tid'])) $params[] = (int)$_GET['tid'];
if (isset($_GET['pid'])) $params[] = (int)$_GET['pid'];
if (isset($_GET['gid'])) $params[] = (int)$_GET['gid'];
if (isset($_GET['uid'])) $params[] = (int)$_GET['uid'];

call_user_func_array([$controllerClass, $action], $params);
?>
