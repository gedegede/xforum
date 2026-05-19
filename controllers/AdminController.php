<?php

class AdminController {

    private static function checkAdmin() {
        if (!Session::isLoggedIn() || Session::getUser()['gid'] != 1) {
            header('Location: index.php');
            exit;
        }
    }

    public static function index() {
        Template::clear();
        self::checkAdmin();

        $stats = [
            'users' => MemberModel::count(),
            'threads' => ThreadModel::count(),
            'forums' => ForumModel::count(),
        ];

        Template::set('title', '管理后台');
        Template::set('stats', $stats);
        Template::set('user', Session::getUser());
        Template::display('admin/index');
    }

    public static function settings() {
        Template::clear();
        self::checkAdmin();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'setting_') === 0) {
                    $skey = substr($key, 8);
                    SettingModel::set($skey, $value);
                }
            }
            $success = '设置已保存';
        }

        $settings = SettingModel::getAll();

        Template::set('title', '站点设置');
        Template::set('settings', $settings);
        Template::set('error', $error);
        Template::set('success', $success);
        Template::set('user', Session::getUser());
        Template::display('admin/settings');
    }

    public static function forums() {
        Template::clear();
        self::checkAdmin();

        $forums = ForumModel::getForums();

        Template::set('title', '版块管理');
        Template::set('forums', $forums);
        Template::set('user', Session::getUser());
        Template::display('admin/forums');
    }

    public static function forumAdd() {
        Template::clear();
        self::checkAdmin();

        $parentForums = ForumModel::getForums();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $upFid = (int)$_POST['up_fid'] ?? 0;

            if (empty($name)) {
                $error = '版块名称不能为空';
            } else {
                ForumModel::create([
                    'name' => $name,
                    'up_fid' => $upFid,
                    'status' => 1,
                ]);
                header('Location: index.php?c=admin&a=forums');
                exit;
            }
        }

        Template::set('title', '添加版块');
        Template::set('parentForums', $parentForums);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/forum_add');
    }

    public static function forumEdit($fid) {
        Template::clear();
        self::checkAdmin();

        $forum = ForumModel::get($fid);
        $parentForums = ForumModel::getForums();

        if (!$forum) {
            header('Location: index.php?c=admin&a=forums');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $upFid = (int)$_POST['up_fid'] ?? 0;
            $status = (int)$_POST['status'] ?? 0;

            if (empty($name)) {
                $error = '版块名称不能为空';
            } else {
                ForumModel::update($fid, [
                    'name' => $name,
                    'up_fid' => $upFid,
                    'status' => $status,
                ]);
                header('Location: index.php?c=admin&a=forums');
                exit;
            }
        }

        Template::set('title', '编辑版块');
        Template::set('forum', $forum);
        Template::set('parentForums', $parentForums);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/forum_edit');
    }

    public static function forumDelete($fid) {
        self::checkAdmin();
        ForumModel::delete($fid);
        header('Location: index.php?c=admin&a=forums');
        exit;
    }

    public static function threads() {
        Template::clear();
        self::checkAdmin();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $fid = isset($_GET['fid']) ? (int)$_GET['fid'] : 0;
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

        $where = [];
        $params = [];

        if ($fid) {
            $where[] = 'fid = ?';
            $params[] = $fid;
        }
        if ($keyword) {
            $where[] = 'subject LIKE ?';
            $params[] = "%$keyword%";
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $threads = ThreadModel::search($whereStr, $params, $page);
        $total = ThreadModel::searchCount($whereStr, $params);

        // 获取发帖用户信息
        $users = [];
        if (!empty($threads)) {
            $uids = array_unique(array_column($threads, 'uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        $forums = ForumModel::getForums();

        Template::set('title', '主题管理');
        Template::set('threads', $threads);
        Template::set('users', $users);
        Template::set('forums', $forums);
        Template::set('fid', $fid);
        Template::set('keyword', $keyword);
        Template::set('page', $page);
        Template::set('pages', ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('admin/threads');
    }

    public static function threadDelete($tid) {
        self::checkAdmin();
        PostModel::deleteByTid($tid);
        ThreadModel::delete($tid);

        self::logAction('delete_thread', "删除主题: tid=$tid");

        header('Location: index.php?c=admin&a=threads');
        exit;
    }

    public static function threadBatch() {
        self::checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $tids = $_POST['tids'] ?? [];

            if ($action == 'delete' && $tids) {
                foreach ($tids as $tid) {
                    PostModel::deleteByTid($tid);
                    ThreadModel::delete($tid);
                }

                self::logAction('batch_delete_thread', "批量删除主题: " . implode(',', $tids));
            } elseif ($action == 'move' && $tids && isset($_POST['fid'])) {
                $fid = (int)$_POST['fid'];

                foreach ($tids as $tid) {
                    ThreadModel::update($tid, ['fid' => $fid]);
                }

                self::logAction('batch_move_thread', "批量移动主题到版块$fid: " . implode(',', $tids));
            }
        }

        header('Location: index.php?c=admin&a=threads');
        exit;
    }

    public static function usergroups() {
        Template::clear();
        self::checkAdmin();

        $groups = UsergroupModel::getAll();

        Template::set('title', '用户组管理');
        Template::set('groups', $groups);
        Template::set('user', Session::getUser());
        Template::display('admin/usergroups');
    }

    public static function usergroupAdd() {
        Template::clear();
        self::checkAdmin();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $groupType = $_POST['group_type'] ?? 'member';
            $creditLower = (int)$_POST['credit_lower'] ?? 0;

            if (empty($title)) {
                $error = '用户组名称不能为空';
            } else {
                UsergroupModel::create([
                    'title' => $title,
                    'group_type' => $groupType,
                    'credit_lower' => $creditLower,
                ]);
                header('Location: index.php?c=admin&a=usergroups');
                exit;
            }
        }

        Template::set('title', '添加用户组');
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/usergroup_add');
    }

    public static function usergroupEdit($gid) {
        Template::clear();
        self::checkAdmin();

        $group = UsergroupModel::get($gid);

        if (!$group) {
            header('Location: index.php?c=admin&a=usergroups');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $groupType = $_POST['group_type'] ?? 'member';
            $creditLower = (int)$_POST['credit_lower'] ?? 0;

            if (empty($title)) {
                $error = '用户组名称不能为空';
            } else {
                UsergroupModel::update($gid, [
                    'title' => $title,
                    'group_type' => $groupType,
                    'credit_lower' => $creditLower,
                ]);
                header('Location: index.php?c=admin&a=usergroups');
                exit;
            }
        }

        Template::set('title', '编辑用户组');
        Template::set('group', $group);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/usergroup_edit');
    }

    public static function usergroupDelete($gid) {
        self::checkAdmin();
        UsergroupModel::delete($gid);
        header('Location: index.php?c=admin&a=usergroups');
        exit;
    }

    public static function logs() {
        Template::clear();
        self::checkAdmin();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $logs = ModLogModel::getLogs($page);
        $total = ModLogModel::getCount();

        // 获取操作用户信息
        $users = [];
        if (!empty($logs)) {
            $uids = array_unique(array_column($logs, 'uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        Template::set('title', '管理日志');
        Template::set('logs', $logs);
        Template::set('users', $users);
        Template::set('page', $page);
        Template::set('pages', ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('admin/logs');
    }

    private static function logAction($action, $message) {
        ModLogModel::addLog(Session::getUid(), $action, $message);
    }
}
?>
