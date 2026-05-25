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
use Models\ThreadModel;
use Models\PostModel;
use Models\FavModel;
use Models\CreditModel;
use Models\SessionModel;
use Models\UsergroupModel;
use Lib\Permission;

class MemberController {
    public static function profile(): void {
        Template::clear();
        $uid = Request::getInt('uid');
        if (!$uid) {
            Response::redirect('index.php');
        }
        $member = MemberModel::get($uid);

        if (!$member) {
            Response::redirect('index.php');
        }

        $type = Request::getString('type', 'threads');

        $threads = [];
        $posts = [];
        $favorites = [];
        $credits = [];
        $total = 0;

        $page = Request::getInt('page', 1);
        switch ($type) {
            case 'replies':
                $posts = PostModel::getUserPosts($uid, $page);
                $total = (int)($member['reply_num'] ?? 0);
                if (!empty($posts)) {
                    $tids = array_unique(array_column($posts, 'tid'));
                    $threads = ThreadModel::getThreadsByTids($tids);
                }
                break;
            case 'favorites':
                if ($uid != Session::getUid()) {
                    Response::redirect("index.php?c=member&a=profile&uid={$uid}");
                }
                $favorites = FavModel::getUserFavorites($uid, $page);
                $total = (int)($member['fav_num'] ?? 0);
                if (!empty($favorites)) {
                    $tids = array_column($favorites, 'tid');
                    $threads = ThreadModel::getThreadsByTids($tids);
                }
                break;
            case 'credits':
                if ($uid != Session::getUid()) {
                    Response::redirect("index.php?c=member&a=profile&uid={$uid}");
                }
                $credits = CreditModel::getUserCredits($uid, $page);
                $total = CreditModel::getUserCreditCount($uid);
                break;
            default:
                $threads = ThreadModel::getUserThreads($uid, $page);
                $total = (int)($member['thread_num'] ?? 0);
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
        Template::set('credits', $credits);
        Template::set('signedToday', $isSelf ? CreditModel::hasSignedToday($uid) : false);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('member/profile');
    }

    public static function signin(): void {
        Permission::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect('index.php?c=member&a=profile&uid=' . Session::getUid() . '&type=credits');
        }

        $result = CreditModel::signin(Session::getUid());
        if (Response::isAjaxRequest()) {
            Response::json($result, $result['success'] ? 200 : 400);
        }

        Response::redirect('index.php?c=member&a=profile&uid=' . Session::getUid() . '&type=credits');
    }

    public static function settings(): void {
        Template::clear();
        Permission::requireLogin();

        $member = MemberModel::get(Session::getUid());

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = Request::postRaw('action');

            if ($action == 'profile') {
                $username = Request::postString('username');
                $email = Request::postString('email');

                if (empty($username)) {
                    $error = '用户名不能为空';
                } elseif ($username != $member['username'] && MemberModel::getByUsername($username)) {
                    $error = '用户名已存在';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = '邮箱格式不正确';
                } else {
                    $isUsernameChanged = $username != $member['username'];
                    MemberModel::update(Session::getUid(), [
                        'username' => $username,
                        'email' => $email,
                    ]);
                    Session::set('username', $username);
                    
                    if ($isUsernameChanged) {
                        CreditModel::apply(CreditModel::ACTION_USERNAME_CHANGE, Session::getUid(), '修改用户名', 'index.php?c=member&a=profile&uid=' . Session::getUid() . '&type=credits');
                    }
                    
                    $success = '个人信息修改成功';
                    $member = MemberModel::get(Session::getUid());
                }
            } elseif ($action == 'password') {
                $oldPassword = Request::postRaw('old_password');
                $newPassword = Request::postRaw('new_password');
                $confirmPassword = Request::postRaw('confirm_password');

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
                $theme = Request::postRaw('theme', 'light');
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
        Template::set('usernameChangeCredit', CreditModel::getRule(CreditModel::ACTION_USERNAME_CHANGE)['credit'] ?? 0);
        Template::display('member/settings');
    }

    public static function online(): void {
        Template::clear();
        
        $onlineUsers = SessionModel::getOnlineUsers();
        
        $uids = array_column($onlineUsers, 'uid');
        $uids = array_filter($uids, function($uid) { return $uid > 0; });
        $members = MemberModel::getMembersByUids($uids);
        
        $groups = UsergroupModel::getAll();
        $groupMap = [];
        foreach ($groups as $group) {
            $groupMap[$group['gid']] = $group['title'];
        }
        
        $threads = [];
        $tids = array_unique(array_column($onlineUsers, 'tid'));
        $tids = array_filter($tids, function($tid) { return $tid > 0; });
        if (!empty($tids)) {
            $threads = ThreadModel::getThreadsByTids($tids);
        }
        
        foreach ($onlineUsers as &$online) {
            if ($online['uid'] > 0 && isset($members[$online['uid']])) {
                $online['username'] = $members[$online['uid']]['username'];
                $online['group_name'] = $groupMap[$online['gid']] ?? '未知';
            } else {
                $online['username'] = '游客';
                $online['group_name'] = '游客';
            }
            if ($online['tid'] > 0 && isset($threads[$online['tid']])) {
                $online['thread_subject'] = $threads[$online['tid']]['subject'];
            }
        }
        
        Template::set('title', '在线用户');
        Template::set('onlineUsers', $onlineUsers);
        Template::set('user', Session::getUser());
        Template::display('member/online');
    }
}
?>
