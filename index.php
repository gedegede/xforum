<?php
define('ROOT_PATH', dirname(__FILE__));

require ROOT_PATH . '/lib/Autoloader.php';
Autoloader::register();

Template::init();

$controller = isset($_GET['c']) ? $_GET['c'] : 'home';
$action = isset($_GET['a']) ? $_GET['a'] : 'index';

$controllerClass = ucfirst($controller) . 'Controller';

if (!class_exists($controllerClass)) {
    $controllerClass = 'HomeController';
    $action = 'index';
}

if (!method_exists($controllerClass, $action)) {
    $action = 'index';
}

$params = [];
if (isset($_GET['fid'])) $params[] = $_GET['fid'];
if (isset($_GET['tid'])) $params[] = $_GET['tid'];
if (isset($_GET['pid'])) $params[] = $_GET['pid'];
if (isset($_GET['gid'])) $params[] = $_GET['gid'];
if (isset($_GET['uid'])) $params[] = $_GET['uid'];

call_user_func_array([$controllerClass, $action], $params);
?>
