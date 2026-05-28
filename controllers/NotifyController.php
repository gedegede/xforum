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
use Models\NotifyModel;
use Models\MemberModel;
use Models\ThreadModel;
use Models\UsergroupModel;
use Lib\Permission;

class NotifyController {
    public static function index(): void {
        Template::clear();
        Permission::requireLogin();

        $page = Request::getInt('page', 1);
        $notifies = NotifyModel::getNotifies(Session::getUid(), $page);
        $total = NotifyModel::getNotifyCount(Session::getUid());

        NotifyModel::markAsRead(Session::getUid());

        $users = [];
        $threads = [];
        if (!empty($notifies)) {
            $fromUids = array_filter(array_unique(array_column($notifies, 'from_uid')));
            $tids = array_filter(array_unique(array_column($notifies, 'tid')));

            if (!empty($fromUids)) {
                $users = MemberModel::getMembersByUids(array_unique($fromUids));
            }
            if (!empty($tids)) {
                $threads = ThreadModel::getThreadsByTids(array_unique($tids));
            }
        }

        Template::set('title', '通知');
        Template::set('notifies', $notifies);
        Template::set('users', $users);
        Template::set('threads', $threads);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::set('member', Session::getUser());
        Template::set('memberGroup', UsergroupModel::get((int)(Session::getUser()['gid'] ?? 0)));
        Template::set('isSelf', true);
        Template::display('notify/index');
    }

}
?>
