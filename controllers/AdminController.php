<?php
declare(strict_types=1);

namespace Controllers;

use Lib\Session;
use Lib\Template;
use Models\MemberModel;
use Models\ThreadModel;
use Models\ForumModel;
use Models\SettingModel;
use Models\UsergroupModel;
use Models\PostModel;
use Models\ModLogModel;

class AdminController {
    private static function checkAdmin(): void {
        if (!Session::isLoggedIn() || Session::getUser()['gid'] != 1) {
            header('Location: index.php');
            exit;
        }
    }

    public static function index(): void {
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

    public static function settings(): void {
        Template::clear();
        self::checkAdmin();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'setting_') === 0) {
                    $skey = substr($key, 8);
                    SettingModel::set($skey, (string)$value);
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

    public static function forums(): void {
        Template::clear();
        self::checkAdmin();

        $forums = ForumModel::getForumsFlat();

        Template::set('title', '版块管理');
        Template::set('forums', $forums);
        Template::set('user', Session::getUser());
        Template::display('admin/forums');
    }

    public static function forumAdd(): void {
        Template::clear();
        self::checkAdmin();

        $parentForums = ForumModel::getForumsFlat();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $upFid = (int)($_POST['up_fid'] ?? 0);

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

    public static function forumEdit(int $fid): void {
        self::checkAdmin();

        $forum = ForumModel::get($fid);

        if (!$forum) {
            if (isset($_GET['ajax'])) {
                echo json_encode(['success' => false, 'message' => '版块不存在']);
                exit;
            }
            header('Location: index.php?c=admin&a=forums');
            exit;
        }

        if (isset($_GET['ajax'])) {
            $parentForums = ForumModel::getForumsFlat();
            echo json_encode(['success' => true, 'forum' => $forum, 'parentForums' => $parentForums]);
            exit;
        }

        Template::clear();
        $parentForums = ForumModel::getForumsFlat();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $upFid = (int)($_POST['up_fid'] ?? 0);
            $status = (int)($_POST['status'] ?? 0);

            if (empty($name)) {
                $error = '版块名称不能为空';
            } else {
                try {
                    $result = ForumModel::update($fid, [
                        'name' => $name,
                        'up_fid' => $upFid,
                        'status' => $status,
                    ]);
                    if ($result === false) {
                        $error = '更新失败';
                    } else {
                        header('Location: index.php?c=admin&a=forums');
                        exit;
                    }
                } catch (Exception $e) {
                    $error = '更新出错: ' . $e->getMessage();
                }
            }
        }

        Template::set('title', '编辑版块');
        Template::set('forum', $forum);
        Template::set('parentForums', $parentForums);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/forum_edit');
    }

    public static function forumDelete(int $fid): void {
        self::checkAdmin();
        ForumModel::delete($fid);
        header('Location: index.php?c=admin&a=forums');
        exit;
    }

    public static function threads(): void {
        Template::clear();
        self::checkAdmin();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $fid = isset($_GET['fid']) ? (int)$_GET['fid'] : 0;
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $searchType = isset($_GET['search_type']) ? $_GET['search_type'] : 'title';

        $where = [];
        $params = [];
        $searchValid = true;

        if ($fid) {
            $where[] = 'fid = ?';
            $params[] = $fid;
        }
        
        if ($keyword) {
            if ($searchType == 'username') {
                $member = MemberModel::getByUsername($keyword);
                if ($member) {
                    $where[] = 'uid = ?';
                    $params[] = $member['uid'];
                } else {
                    $searchValid = false;
                }
            } else {
                $where[] = 'subject LIKE ?';
                $params[] = "%$keyword%";
            }
        }

        $threads = [];
        $total = 0;
        
        if ($searchValid) {
            $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $threads = ThreadModel::search($whereStr, $params, $page);
            $total = ThreadModel::searchCount($whereStr, $params);
        }

        $users = [];
        if (!empty($threads)) {
            $uids = array_unique(array_column($threads, 'uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        $forums = ForumModel::getForumsFlat();

        Template::set('title', '主题管理');
        Template::set('threads', $threads);
        Template::set('users', $users);
        Template::set('forums', $forums);
        Template::set('fid', $fid);
        Template::set('keyword', $keyword);
        Template::set('searchType', $searchType);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('admin/threads');
    }

    public static function threadDelete(int $tid): void {
        self::checkAdmin();
        PostModel::deleteByTid($tid);
        ThreadModel::delete($tid);

        self::logAction('delete_thread', "删除主题: tid=$tid");

        header('Location: index.php?c=admin&a=threads');
        exit;
    }

    public static function threadBatch(): void {
        self::checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $tids = $_POST['tids'] ?? [];

            if ($action == 'delete' && $tids) {
                foreach ($tids as $tid) {
                    PostModel::deleteByTid((int)$tid);
                    ThreadModel::delete((int)$tid);
                }

                self::logAction('batch_delete_thread', "批量删除主题: " . implode(',', $tids));
            } elseif ($action == 'move' && $tids && isset($_POST['fid'])) {
                $fid = (int)$_POST['fid'];

                foreach ($tids as $tid) {
                    ThreadModel::update((int)$tid, ['fid' => $fid]);
                }

                self::logAction('batch_move_thread', "批量移动主题到版块$fid: " . implode(',', $tids));
            }
        }

        header('Location: index.php?c=admin&a=threads');
        exit;
    }

    public static function usergroups(): void {
        Template::clear();
        self::checkAdmin();

        $groups = UsergroupModel::getAll();

        Template::set('title', '用户组管理');
        Template::set('groups', $groups);
        Template::set('user', Session::getUser());
        Template::display('admin/usergroups');
    }

    public static function usergroupAdd(): void {
        Template::clear();
        self::checkAdmin();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $groupType = $_POST['group_type'] ?? 'member';
            $creditLower = (int)($_POST['credit_lower'] ?? 0);

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

    public static function usergroupEdit(int $gid): void {
        self::checkAdmin();

        $group = UsergroupModel::get($gid);

        if (!$group) {
            if (isset($_GET['ajax'])) {
                echo json_encode(['success' => false, 'message' => '用户组不存在']);
                exit;
            }
            header('Location: index.php?c=admin&a=usergroups');
            exit;
        }

        if (isset($_GET['ajax'])) {
            echo json_encode(['success' => true, 'group' => $group]);
            exit;
        }

        Template::clear();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $groupType = $_POST['group_type'] ?? 'member';
            $creditLower = (int)($_POST['credit_lower'] ?? 0);

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

    public static function usergroupDelete(int $gid): void {
        self::checkAdmin();
        UsergroupModel::delete($gid);
        header('Location: index.php?c=admin&a=usergroups');
        exit;
    }

    public static function users(): void {
        Template::clear();
        self::checkAdmin();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $gid = isset($_GET['gid']) ? (int)$_GET['gid'] : 0;

        $users = MemberModel::search($keyword, $gid, $page);
        $total = MemberModel::searchCount($keyword, $gid);
        $groups = UsergroupModel::getAll();

        Template::set('title', '用户管理');
        Template::set('users', $users);
        Template::set('groups', $groups);
        Template::set('keyword', $keyword);
        Template::set('gid', $gid);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('admin/users');
    }

    public static function userEdit(int $uid): void {
        self::checkAdmin();

        $member = MemberModel::get($uid);
        if (!$member) {
            if (isset($_GET['ajax'])) {
                echo json_encode(['success' => false, 'message' => '用户不存在']);
                exit;
            }
            header('Location: index.php?c=admin&a=users');
            exit;
        }

        if (isset($_GET['ajax'])) {
            echo json_encode(['success' => true, 'user' => $member]);
            exit;
        }

        Template::clear();

        $groups = UsergroupModel::getAll();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'gid' => (int)$_POST['gid'],
                'status' => (int)$_POST['status'],
            ];

            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            MemberModel::update($uid, $data);
            self::logAction('user_edit', "编辑用户: {$member['username']} (UID: {$uid})");
            header('Location: index.php?c=admin&a=users');
            exit;
        }

        Template::set('title', '编辑用户');
        Template::set('member', $member);
        Template::set('groups', $groups);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/user_edit');
    }

    public static function userDelete(int $uid): void {
        self::checkAdmin();
        $member = MemberModel::get($uid);
        if ($member) {
            MemberModel::delete($uid);
            self::logAction('user_delete', "删除用户: {$member['username']} (UID: {$uid})");
        }
        header('Location: index.php?c=admin&a=users');
        exit;
    }

    public static function logs(): void {
        Template::clear();
        self::checkAdmin();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $logs = ModLogModel::getLogs($page);
        $total = ModLogModel::getCount();

        $users = [];
        if (!empty($logs)) {
            $uids = array_unique(array_column($logs, 'uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        Template::set('title', '管理日志');
        Template::set('logs', $logs);
        Template::set('users', $users);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('admin/logs');
    }

    private static function logAction(string $action, string $message): void {
        ModLogModel::addLog(Session::getUid(), $action, $message);
    }
}
?>