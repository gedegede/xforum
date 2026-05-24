<?php
declare(strict_types=1);

namespace Controllers;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Session;
use Lib\Template;
use Lib\Response;
use Lib\Request;
use Models\MemberModel;
use Models\UsergroupModel;
use Models\SessionModel;
use Lib\Permission;

class AuthController {
    public static function login(): void {
        Template::clear();
        if (Permission::isLoggedIn()) {
            Response::redirect('index.php');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = Request::postRaw('username');
            $password = Request::postRaw('password');

            if (MemberModel::checkPassword($username, $password)) {
                $member = MemberModel::getByUsername($username);
                Session::regenerateId();
                Session::set('uid', $member['uid']);
                Session::set('username', $member['username']);
                
                // 登录成功后更新在线状态
                SessionModel::updateOnline($member['uid'], $member['gid'], 0);
                
                Response::redirect('index.php');
            } else {
                $error = '用户名或密码错误';
            }
        }

        Template::set('title', '登录');
        Template::set('error', $error);
        Template::display('auth/login');
    }

    public static function register(): void {
        Template::clear();
        if (Permission::isLoggedIn()) {
            Response::redirect('index.php');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = Request::postString('username');
            $email = Request::postString('email');
            $password = Request::postRaw('password');
            $confirmPassword = Request::postRaw('confirm_password');

            if (empty($username) || empty($email) || empty($password)) {
                $error = '请填写所有必填项';
            } elseif ($password !== $confirmPassword) {
                $error = '两次密码不一致';
            } elseif (strlen($password) < 6) {
                $error = '密码长度至少6位';
            } else {
                if (MemberModel::getByUsername($username)) {
                    $error = '用户名已存在';
                } elseif (MemberModel::getByEmail($email)) {
                    $error = '邮箱已被注册';
                } else {
                    $defaultGroup = UsergroupModel::getDefaultGroup();
                    $gid = $defaultGroup ? $defaultGroup['gid'] : 1;

                    MemberModel::register([
                        'username' => $username,
                        'email' => $email,
                        'password' => $password,
                        'gid' => $gid,
                    ]);

                    Response::redirect('index.php?c=auth&a=login');
                }
            }
        }

        Template::set('title', '注册');
        Template::set('error', $error);
        Template::display('auth/register');
    }

    public static function logout(): void {
        Session::clear();
        Response::redirect('index.php');
    }
}
?>