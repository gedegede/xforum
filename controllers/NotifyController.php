<?php
declare(strict_types=1);

namespace Controllers;

use Lib\Session;
use Lib\Template;
use Models\NotifyModel;
use Models\MemberModel;
use Models\ThreadModel;

class NotifyController {
    public static function index(): void {
        Template::clear();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
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
        Template::display('notify/index');
    }
}
?>