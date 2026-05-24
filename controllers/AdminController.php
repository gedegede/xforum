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
use Lib\Permission;
use Models\MemberModel;
use Models\ThreadModel;
use Models\ForumModel;
use Models\SettingModel;
use Models\UsergroupModel;
use Models\PostModel;
use Models\ModLogModel;
use Models\ModeratorModel;

class AdminController {
    public static function index(): void {
        Template::clear();
        Permission::requireAdmin();

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
        Permission::requireAdmin();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Request::all();
            foreach ($postData as $key => $value) {
                if (strpos($key, 'setting_') === 0) {
                    $skey = substr($key, 8);
                    if ($skey === 'collapsed_fids' && is_array($value)) {
                        $value = implode(',', array_filter($value));
                    } elseif (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    SettingModel::set($skey, (string)$value);
                }
            }
            $success = '设置已保存';
        }

        $settings = SettingModel::getAll();
        $forums = ForumModel::getForumsFlat();

        Template::set('title', '站点设置');
        Template::set('settings', $settings);
        Template::set('forums', $forums);
        Template::set('error', $error);
        Template::set('success', $success);
        Template::set('user', Session::getUser());
        Template::display('admin/settings');
    }

    public static function forums(): void {
        Template::clear();
        Permission::requireAdmin();

        $forums = ForumModel::getForumsFlat();

        Template::set('title', '版块管理');
        Template::set('forums', $forums);
        Template::set('user', Session::getUser());
        Template::display('admin/forums');
    }

    public static function forumAdd(): void {
        Template::clear();
        Permission::requireAdmin();

        $parentForums = ForumModel::getForumsFlat();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = Request::postString('name');
            $upFid = Request::postInt('up_fid');

            if (empty($name)) {
                $error = '版块名称不能为空';
            } else {
                ForumModel::create([
                    'name' => $name,
                    'up_fid' => $upFid,
                    'status' => 1,
                ]);
                Response::redirect('index.php?c=admin&a=forums');
            }
        }

        Template::set('title', '添加版块');
        Template::set('parentForums', $parentForums);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/forum_add');
    }

    public static function forumEdit(int $fid): void {
        Permission::requireAdmin();

        $forum = ForumModel::get($fid);

        if (!$forum) {
            if (Request::getBool('ajax')) {
                Response::json(['success' => false, 'message' => '版块不存在'], 404);
            }
            Response::redirect('index.php?c=admin&a=forums');
        }

        if (Request::getBool('ajax')) {
            $parentForums = ForumModel::getForumsFlat();
            Response::json(['success' => true, 'forum' => $forum, 'parentForums' => $parentForums]);
        }

        Template::clear();
        $parentForums = ForumModel::getForumsFlat();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = Request::postString('name');
            $upFid = Request::postInt('up_fid');
            $status = Request::postInt('status');

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
                        Response::redirect('index.php?c=admin&a=forums');
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
        Permission::requireAdmin();
        ForumModel::delete($fid);
        Response::redirect('index.php?c=admin&a=forums');
    }

    public static function threads(): void {
        Template::clear();
        Permission::requireAdmin();

        $page = Request::getInt('page', 1);
        $fid = Request::getInt('fid');
        $keyword = Request::getString('keyword');
        $searchType = Request::getString('search_type', 'title');

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
        Permission::requireAdmin();
        PostModel::deleteByTid($tid);
        ThreadModel::delete($tid);

        self::logAction('delete_thread', "删除主题: tid=$tid");

        Response::redirect('index.php?c=admin&a=threads');
    }

    public static function threadBatch(): void {
        Permission::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = Request::postRaw('action');
            $tids = Request::postArray('tids');

            if ($action == 'delete' && $tids) {
                foreach ($tids as $tid) {
                    PostModel::deleteByTid((int)$tid);
                    ThreadModel::delete((int)$tid);
                }

                self::logAction('batch_delete_thread', "批量删除主题: " . implode(',', $tids));
            } elseif ($action == 'move' && $tids) {
                $fid = Request::postInt('fid');

                foreach ($tids as $tid) {
                    ThreadModel::update((int)$tid, ['fid' => $fid]);
                }

                self::logAction('batch_move_thread', "批量移动主题到版块$fid: " . implode(',', $tids));
            }
        }

        Response::redirect('index.php?c=admin&a=threads');
    }

    public static function usergroups(): void {
        Template::clear();
        Permission::requireAdmin();

        $groups = UsergroupModel::getAll();

        Template::set('title', '用户组管理');
        Template::set('groups', $groups);
        Template::set('user', Session::getUser());
        Template::display('admin/usergroups');
    }

    public static function usergroupAdd(): void {
        Template::clear();
        Permission::requireAdmin();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = Request::postString('title');
            $groupType = Request::postRaw('group_type', 'member');
            $creditLower = Request::postInt('credit_lower');
            $canManage = Request::postInt('can_manage');
            $threadNeedApprove = Request::postInt('thread_need_approve');
            $postNeedApprove = Request::postInt('post_need_approve');

            if (empty($title)) {
                $error = '用户组名称不能为空';
            } else {
                $jsonData = json_encode([
                    'can_manage' => $canManage,
                    'thread_need_approve' => $threadNeedApprove,
                    'post_need_approve' => $postNeedApprove,
                ]);
                UsergroupModel::create([
                    'title' => $title,
                    'group_type' => $groupType,
                    'credit_lower' => $creditLower,
                    'json_data' => $jsonData,
                ]);
                Response::redirect('index.php?c=admin&a=usergroups');
            }
        }

        Template::set('title', '添加用户组');
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/usergroup_add');
    }

    public static function usergroupEdit(int $gid): void {
        Permission::requireAdmin();

        $group = UsergroupModel::get($gid);

        if (!$group) {
            if (Request::getBool('ajax')) {
                Response::json(['success' => false, 'message' => '用户组不存在'], 404);
            }
            Response::redirect('index.php?c=admin&a=usergroups');
        }

        if (Request::getBool('ajax')) {
            Response::json(['success' => true, 'group' => $group]);
        }

        Template::clear();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = Request::postString('title');
            $groupType = Request::postRaw('group_type', 'member');
            $creditLower = Request::postInt('credit_lower');
            $canManage = Request::postInt('can_manage');
            $threadNeedApprove = Request::postInt('thread_need_approve');
            $postNeedApprove = Request::postInt('post_need_approve');

            if (empty($title)) {
                $error = '用户组名称不能为空';
            } else {
                $jsonData = json_encode([
                    'can_manage' => $canManage,
                    'thread_need_approve' => $threadNeedApprove,
                    'post_need_approve' => $postNeedApprove,
                ]);
                UsergroupModel::update($gid, [
                    'title' => $title,
                    'group_type' => $groupType,
                    'credit_lower' => $creditLower,
                    'json_data' => $jsonData,
                ]);
                Response::redirect('index.php?c=admin&a=usergroups');
            }
        }

        Template::set('title', '编辑用户组');
        Template::set('group', $group);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/usergroup_edit');
    }

    public static function usergroupDelete(int $gid): void {
        Permission::requireAdmin();
        UsergroupModel::delete($gid);
        Response::redirect('index.php?c=admin&a=usergroups');
    }

    public static function users(): void {
        Template::clear();
        Permission::requireAdmin();

        $page = Request::getInt('page', 1);
        $keyword = Request::getString('keyword');
        $gid = Request::getInt('gid');

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
        Permission::requireAdmin();

        $member = MemberModel::get($uid);
        if (!$member) {
            if (Request::getBool('ajax')) {
                Response::json(['success' => false, 'message' => '用户不存在'], 404);
            }
            Response::redirect('index.php?c=admin&a=users');
        }

        if (Request::getBool('ajax')) {
            Response::json(['success' => true, 'user' => $member]);
        }

        Template::clear();

        $groups = UsergroupModel::getAll();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'username' => Request::postString('username'),
                'email' => Request::postString('email'),
                'gid' => Request::postInt('gid'),
                'status' => Request::postInt('status'),
            ];

            $password = Request::postRaw('password');
            if (!empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            MemberModel::update($uid, $data);
            self::logAction('user_edit', "编辑用户: {$member['username']} (UID: {$uid})");
            Response::redirect('index.php?c=admin&a=users');
        }

        Template::set('title', '编辑用户');
        Template::set('member', $member);
        Template::set('groups', $groups);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/user_edit');
    }

    public static function userDelete(int $uid): void {
        Permission::requireAdmin();
        $member = MemberModel::get($uid);
        if ($member) {
            MemberModel::delete($uid);
            self::logAction('user_delete', "删除用户: {$member['username']} (UID: {$uid})");
        }
        Response::redirect('index.php?c=admin&a=users');
    }

    public static function logs(): void {
        Template::clear();
        Permission::requireAdmin();

        $page = Request::getInt('page', 1);
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

    public static function moderators(int $fid = 0): void {
        Template::clear();
        Permission::requireAdmin();

        if (!$fid) {
            Response::redirect('index.php?c=admin&a=forums');
        }

        $forum = ForumModel::get($fid);
        if (!$forum) {
            Response::redirect('index.php?c=admin&a=forums');
        }

        $moderators = ModeratorModel::getByFid($fid);
        usort($moderators, function($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });

        $users = [];
        if (!empty($moderators)) {
            $uids = array_unique(array_column($moderators, 'uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        Template::set('title', '版主管理 - ' . $forum['name']);
        Template::set('forum', $forum);
        Template::set('moderators', $moderators);
        Template::set('users', $users);
        Template::set('user', Session::getUser());
        Template::display('admin/moderators');
    }

    public static function moderatorAdd(): void {
        Permission::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fid = Request::postInt('fid');
            $username = Request::postString('username');
            $sortOrder = Request::postInt('sort_order');
            $endDate = Request::postString('end_date');

            $endDateTs = 0;
            if (!empty($endDate)) {
                $endDateTs = strtotime($endDate);
            }

            if ($fid && $username) {
                $member = MemberModel::getByUsername($username);
                if ($member && !ModeratorModel::isModerator($member['uid'], $fid)) {
                    ModeratorModel::create([
                        'uid' => $member['uid'],
                        'fid' => $fid,
                        'sort_order' => $sortOrder,
                        'end_date' => $endDateTs,
                    ]);
                    self::logAction('moderator_add', "添加版主: uid={$member['uid']}, fid=$fid");
                }
            }
            Response::redirect('index.php?c=admin&a=moderators&fid=' . $fid);
        }

        Response::redirect('index.php?c=admin&a=forums');
    }

    public static function moderatorEdit(): void {
        Permission::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fid = Request::postInt('fid');
            $uid = Request::postInt('uid');
            $sortOrder = Request::postInt('sort_order');
            $endDate = Request::postString('end_date');

            $endDateTs = 0;
            if (!empty($endDate)) {
                $endDateTs = strtotime($endDate);
            }

            if ($fid && $uid) {
                ModeratorModel::update($uid, $fid, [
                    'sort_order' => $sortOrder,
                    'end_date' => $endDateTs,
                ]);
                self::logAction('moderator_edit', "编辑版主: uid=$uid, fid=$fid");
            }
            Response::redirect('index.php?c=admin&a=moderators&fid=' . $fid);
        }

        Response::redirect('index.php?c=admin&a=forums');
    }

    public static function moderatorDelete(): void {
        Permission::requireAdmin();

        $fid = Request::getInt('fid');
        $uid = Request::getInt('uid');

        if ($fid && $uid) {
            ModeratorModel::delete($uid, $fid);
            self::logAction('moderator_delete', "删除版主: uid=$uid, fid=$fid");
        }

        Response::redirect('index.php?c=admin&a=moderators&fid=' . $fid);
    }
}
?>