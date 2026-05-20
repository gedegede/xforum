<?php
declare(strict_types=1);

namespace Controllers;

use Lib\Session;
use Lib\Template;
use Models\MemberModel;
use Models\ThreadModel;
use Models\PostModel;
use Models\FavModel;

class MemberController {
    public static function profile(): void {
        Template::clear();
        $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
        if (!$uid) {
            header('Location: index.php');
            exit;
        }
        $member = MemberModel::get($uid);

        if (!$member) {
            header('Location: index.php');
            exit;
        }

        $type = isset($_GET['type']) ? $_GET['type'] : 'threads';

        $threads = [];
        $posts = [];
        $favorites = [];
        $total = 0;

        switch ($type) {
            case 'replies':
                $posts = PostModel::getUserPosts($uid, isset($_GET['page']) ? (int)$_GET['page'] : 1);
                $total = PostModel::getUserPostCount($uid);
                if (!empty($posts)) {
                    $tids = array_unique(array_column($posts, 'tid'));
                    $threads = ThreadModel::getThreadsByTids($tids);
                }
                break;
            case 'favorites':
                if ($uid != Session::getUid()) {
                    header("Location: index.php?c=member&a=profile&uid={$uid}");
                    exit;
                }
                $favorites = FavModel::getUserFavorites($uid, isset($_GET['page']) ? (int)$_GET['page'] : 1);
                $total = FavModel::getUserFavoriteCount($uid);
                if (!empty($favorites)) {
                    $tids = array_column($favorites, 'tid');
                    $threads = ThreadModel::getThreadsByTids($tids);
                }
                break;
            default:
                $threads = ThreadModel::getUserThreads($uid, isset($_GET['page']) ? (int)$_GET['page'] : 1);
                $total = ThreadModel::getUserThreadCount($uid);
                break;
        }

        $isSelf = $uid == Session::getUid();

        Template::set('title', $member['username'] . '的个人主页');
        Template::set('member', $member);
        Template::set('type', $type);
        Template::set('isSelf', $isSelf);
        Template::set('threads', $threads);
        Template::set('posts', $posts);
        Template::set('favorites', $favorites);
        Template::set('page', isset($_GET['page']) ? (int)$_GET['page'] : 1);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('member/profile');
    }

    public static function settings(): void {
        Template::clear();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $member = MemberModel::get(Session::getUid());

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action == 'profile') {
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');

                if (empty($username)) {
                    $error = '用户名不能为空';
                } elseif ($username != $member['username'] && MemberModel::getByUsername($username)) {
                    $error = '用户名已存在';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = '邮箱格式不正确';
                } else {
                    MemberModel::update(Session::getUid(), [
                        'username' => $username,
                        'email' => $email,
                    ]);
                    Session::set('username', $username);
                    $success = '个人信息修改成功';
                    $member = MemberModel::get(Session::getUid());
                }
            } elseif ($action == 'password') {
                $oldPassword = $_POST['old_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (!MemberModel::checkPassword($member['username'], $oldPassword)) {
                    $error = '原密码不正确';
                } elseif (strlen($newPassword) < 6) {
                    $error = '新密码长度至少6位';
                } elseif ($newPassword != $confirmPassword) {
                    $error = '两次密码不一致';
                } else {
                    MemberModel::update(Session::getUid(), [
                        'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                    ]);
                    $success = '密码修改成功';
                }
            } elseif ($action == 'theme') {
                $theme = $_POST['theme'] ?? 'light';
                if (!in_array($theme, ['light', 'dark'])) {
                    $theme = 'light';
                }
                MemberModel::setJsonData(Session::getUid(), 'theme', $theme);
                $success = '主题设置已保存';
                $member = MemberModel::get(Session::getUid());
            }
        }

        Template::set('title', '个人设置');
        Template::set('member', $member);
        Template::set('error', $error);
        Template::set('success', $success);
        Template::set('user', Session::getUser());
        Template::display('member/settings');
    }
}
?>