<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Models\MemberModel;
use Models\UsergroupModel;
use Models\ForumModel;
use Models\ModeratorModel as ModeratorModel;

class Permission {
    private const ROOT_ADMIN_UID = 1;

    public static function isLoggedIn(): bool {
        return Session::isLoggedIn();
    }

    public static function isAdmin(): bool {
        $user = Session::getUser();
        return self::hasAdminPrivileges($user);
    }

    public static function isModerator(?int $fid = null): bool {
        $user = Session::getUser();
        if (!$user) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        if ($fid === null) {
            return !empty(ModeratorModel::getByUid((int)$user['uid']));
        }

        return ModeratorModel::isModerator((int)$user['uid'], $fid);
    }

    public static function isModeratorOfForum(int $uid, int $fid): bool {
        return ModeratorModel::isModerator($uid, $fid);
    }

    public static function canViewThread(array $thread): bool {
        if ($thread['sort_order'] >= 0) {
            return true;
        }
        return self::isModeratorOfThread($thread);
    }

    public static function canViewPost(array $post): bool {
        if ($post['sort_order'] >= 0) {
            return true;
        }
        $user = Session::getUser();
        if (!$user) {
            return false;
        }
        
        $thread = [
            'tid' => $post['tid'],
            'fid' => $post['fid'],
            'uid' => $post['uid'],
            'sort_order' => $post['sort_order']
        ];
        return self::isModeratorOfThread($thread) || $user['uid'] === $post['uid'];
    }

    public static function canPostThread(int $fid): bool {
        $user = Session::getUser();
        if (!$user) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        $group = UsergroupModel::get($user['gid']);
        if (!$group) {
            return false;
        }

        return ($group['allow_thread'] ?? 0) === 1;
    }

    public static function canReplyThread(int $fid): bool {
        $user = Session::getUser();
        if (!$user) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        $group = UsergroupModel::get($user['gid']);
        if (!$group) {
            return false;
        }

        return ($group['allow_reply'] ?? 0) === 1;
    }

    public static function canEditThread(array $thread): bool {
        $user = Session::getUser();
        if (!$user) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        if ($user['uid'] === $thread['uid']) {
            return true;
        }

        if ($thread['fid']) {
            return self::isModeratorOfForum($user['uid'], $thread['fid']);
        }

        return false;
    }

    public static function canEditPost(array $post): bool {
        $user = Session::getUser();
        if (!$user) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        if ($user['uid'] === $post['uid']) {
            return true;
        }

        if ($post['fid']) {
            return self::isModeratorOfForum($user['uid'], $post['fid']);
        }

        return false;
    }

    public static function canDeleteThread(array $thread): bool {
        return self::canEditThread($thread);
    }

    public static function canDeletePost(array $post): bool {
        return self::canEditPost($post);
    }

    public static function canPinThread(array $thread): bool {
        $user = Session::getUser();
        if (!$user || !$thread['fid']) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        return self::isModeratorOfForum($user['uid'], $thread['fid']);
    }

    public static function canMoveThread(array $thread): bool {
        $user = Session::getUser();
        if (!$user || !$thread['fid']) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        return self::isModeratorOfForum($user['uid'], $thread['fid']);
    }

    public static function canCloseThread(array $thread): bool {
        $user = Session::getUser();
        if (!$user || !$thread['fid']) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        return self::isModeratorOfForum($user['uid'], $thread['fid']);
    }

    public static function canManageForum(int $fid): bool {
        $user = Session::getUser();
        if (!$user) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        return self::isModeratorOfForum($user['uid'], $fid);
    }

    public static function canEditForum(int $fid): bool {
        return self::canManageForum($fid);
    }

    public static function canDeleteForum(int $fid): bool {
        return self::isAdmin();
    }

    public static function canAccessAdmin(): bool {
        return self::isAdmin();
    }

    public static function canSendPm(): bool {
        $user = Session::getUser();
        if (!$user) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        $group = UsergroupModel::get($user['gid']);
        if (!$group) {
            return false;
        }

        return ($group['allow_pm'] ?? 0) === 1;
    }

    public static function canSendNotification(): bool {
        return self::isLoggedIn();
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            if (Response::isAjaxRequest()) {
                Response::error('请先登录', 401);
            }
            Response::redirect('index.php?c=auth&a=login');
        }
    }

    public static function requireAdmin(): void {
        self::requireLogin();
        if (!self::isAdmin()) {
            if (Response::isAjaxRequest()) {
                Response::error('无权限访问', 403);
            }
            Response::redirect('index.php');
        }
    }

    public static function requireModerator(int $fid): void {
        self::requireLogin();
        if (!self::canManageForum($fid)) {
            if (Response::isAjaxRequest()) {
                Response::error('无权限访问', 403);
            }
            Response::redirect('index.php');
        }
    }

    private static function isModeratorOfThread(array $thread): bool {
        $user = Session::getUser();
        if (!$user || !$thread['fid']) {
            return false;
        }

        if (self::hasAdminPrivileges($user)) {
            return true;
        }

        return self::isModeratorOfForum((int)$user['uid'], (int)$thread['fid']);
    }

    private static function hasAdminPrivileges(?array $user): bool {
        if (!$user) {
            return false;
        }

        if ((int)($user['uid'] ?? 0) === self::ROOT_ADMIN_UID) {
            return true;
        }

        return UsergroupModel::canManage((int)($user['gid'] ?? 0));
    }
}
