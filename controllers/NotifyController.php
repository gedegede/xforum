<?php

class NotifyController {

    public static function index() {
        Template::clear();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $notifies = NotifyModel::getNotifies(Session::getUid(), $page);
        $total = NotifyModel::getNotifyCount(Session::getUid());

        NotifyModel::markAsRead(Session::getUid());

        // 获取通知相关数据
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
        Template::set('pages', ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('notify/index');
    }
}
?>
