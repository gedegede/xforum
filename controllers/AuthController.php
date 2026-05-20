<?php
declare(strict_types=1);

namespace Controllers;

use Lib\Session;
use Lib\Template;
use Models\MemberModel;
use Models\UsergroupModel;

class AuthController {
    public static function login(): void {
        Template::clear();
        if (Session::isLoggedIn()) {
            header('Location: index.php');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (MemberModel::checkPassword($username, $password)) {
                $member = MemberModel::getByUsername($username);
                Session::set('uid', $member['uid']);
                Session::set('username', $member['username']);
                header('Location: index.php');
                exit;
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
        if (Session::isLoggedIn()) {
            header('Location: index.php');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

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

                    header('Location: index.php?c=auth&a=login');
                    exit;
                }
            }
        }

        Template::set('title', '注册');
        Template::set('error', $error);
        Template::display('auth/register');
    }

    public static function logout(): void {
        Session::clear();
        header('Location: index.php');
        exit;
    }
}
?>