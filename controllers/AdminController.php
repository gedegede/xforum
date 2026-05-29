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
use Lib\Database;
use Lib\ThreadHelper as ThreadViewHelper;
use Models\MemberModel;
use Models\ThreadModel;
use Models\ForumModel;
use Models\SettingModel;
use Models\UsergroupModel;
use Models\PostModel;
use Models\ModLogModel;
use Models\ModeratorModel;
use Models\CreditModel;
use Models\DataModel;
use Models\AuditModel;
use Models\NotifyModel;

class AdminController {
    public static function index(): void {
        Template::clear();
        Permission::requireAdminPermission('admin_thread');

        $cacheAction = Request::postString('cache_action');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cacheAction === 'opcache_reset') {
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            self::logAction('cache_opcache_reset', '清空 OPcache 缓存');
            Response::redirect('index.php?c=admin&a=index&success=' . urlencode('OPcache 缓存已清空'));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cacheAction === 'apcu_clear') {
            if (function_exists('apcu_clear_cache')) {
                apcu_clear_cache();
            }
            self::logAction('cache_apcu_clear', '清空 APCu 缓存');
            Response::redirect('index.php?c=admin&a=index&success=' . urlencode('APCu 缓存已清空'));
        }

        $stats = [
            'users' => MemberModel::count(),
            'threads' => ThreadModel::count(),
            'forums' => ForumModel::count(),
        ];

        Template::set('title', '管理后台');
        Template::set('stats', $stats);
        Template::set('success', Request::getString('success'));
        Template::set('systemInfo', self::getSystemInfo());
        Template::set('user', Session::getUser());
        Template::display('admin/index');
    }

    private static function getSystemInfo(): array {
        return [
            '当前版本' => '1.0.0-alpha build 20250624',
            '当前时间' => date('Y-m-d H:i:s'),
            '当前时区' => date_default_timezone_get(),
            '服务器软件' => $_SERVER['SERVER_SOFTWARE'] ?? '',
            '操作系统及 PHP' => PHP_OS . ' / PHP v' . PHP_VERSION,
            'OPcache' => self::getOpcacheInfo(),
            'APCu' => self::getApcuInfo(),
            'PHP 禁用函数' => ini_get('disable_functions') ?: '',
            'PHP 单次上传尺寸' => ini_get('upload_max_filesize') ?: '',
            'PHP 单次上传数量' => ini_get('max_file_uploads') ?: '',
            'MYSQL版本' => self::getMysqlVersion(),
            '数据库数据尺寸' => self::getDatabaseSize('data'),
            '数据库索引尺寸' => self::getDatabaseSize('index'),
            '访问IP' => $_SERVER['REMOTE_ADDR'] ?? '',
        ];
    }

    private static function getOpcacheInfo(): string {
        if (!function_exists('opcache_get_status') || !opcache_get_status(false)) {
            return '未启用';
        }
        $version = function_exists('opcache_get_configuration') ? (opcache_get_configuration()['version']['version'] ?? PHP_VERSION) : PHP_VERSION;
        return '已启用, 版本' . $version;
    }

    private static function getApcuInfo(): string {
        if (!function_exists('apcu_enabled') || !apcu_enabled()) {
            return '未启用';
        }
        $version = phpversion('apcu') ?: '';
        return '已启用' . ($version !== '' ? ', 版本' . $version : '');
    }

    private static function getMysqlVersion(): string {
        $row = Database::fetch('SELECT VERSION() AS version');
        return (string)($row['version'] ?? '');
    }

    private static function getDatabaseSize(string $type): string {
        $column = $type === 'index' ? 'INDEX_LENGTH' : 'DATA_LENGTH';
        $row = Database::fetch("SELECT SUM({$column}) AS size FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()");
        return self::formatBytes((int)($row['size'] ?? 0));
    }

    private static function formatBytes(int $bytes): string {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int)floor(log($bytes, 1024)), count($units) - 1);
        return number_format($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }

    public static function settings(): void {
        Template::clear();
        Permission::requireAdminPermission('admin_setting');

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $handledSettings = [];
            if (isset($_POST['signin_credit_min']) || isset($_POST['signin_credit_max'])) {
                SettingModel::set('signin_credit_range', self::buildSigninCreditRange());
                $handledSettings[] = 'signin_credit_range';
            }
            if (isset($_POST['credit_rule_credit']) || isset($_POST['credit_rule_daily_max']) || isset($_POST['credit_rule_enabled'])) {
                SettingModel::set(
                    'credit_rules',
                    self::buildCreditRuleText(
                        Request::postArray('credit_rule_credit'),
                        Request::postArray('credit_rule_daily_max'),
                        Request::postArray('credit_rule_enabled')
                    )
                );
                $handledSettings[] = 'credit_rules';
            }

            $postData = Request::all();
            foreach ($postData as $key => $value) {
                if (strpos($key, 'setting_') === 0) {
                    $skey = substr($key, 8);
                    if (in_array($skey, $handledSettings, true)) {
                        continue;
                    }
                    if ($skey === 'collapsed_fids' && is_array($value)) {
                        $value = implode(',', array_filter($value));
                    } elseif ($skey === 'register_default_gid') {
                        $group = UsergroupModel::getRegisterDefaultGroup((int)$value);
                        $value = $group ? (string)$group['gid'] : '';
                    } elseif ($skey === 'timezone') {
                        $value = self::normalizeTimezone((string)$value);
                    } elseif (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    SettingModel::set($skey, (string)$value);
                }
            }
            self::logAction('settings_update', '保存站点设置');
            $success = '设置已保存';
        }

        $settings = SettingModel::getAll();
        $forums = ForumModel::getForumsFlat();
        $usergroups = UsergroupModel::getAll();

        Template::set('title', '站点设置');
        Template::set('settings', $settings);
        Template::set('forums', $forums);
        Template::set('usergroups', $usergroups);
        Template::set('timezone', self::normalizeTimezone($settings['timezone'] ?? ''));
        Template::set('timezoneOptions', \DateTimeZone::listIdentifiers());
        Template::set('creditRules', CreditModel::getRules());
        Template::set('creditActionLabels', CreditModel::getActionLabels());
        Template::set('signinRange', CreditModel::getSigninRange());
        Template::set('error', $error);
        Template::set('success', $success);
        Template::set('user', Session::getUser());
        Template::display('admin/settings');
    }

    private static function normalizeTimezone(string $timezone): string {
        if (in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            return $timezone;
        }

        $config = require ROOT_PATH . '/config/app.php';
        $default = (string)($config['timezone'] ?? 'UTC');
        return in_array($default, \DateTimeZone::listIdentifiers(), true) ? $default : 'UTC';
    }

    private static function buildSigninCreditRange(): string {
        [$currentMin, $currentMax] = CreditModel::getSigninRange();
        $min = isset($_POST['signin_credit_min']) ? max(0, (int)$_POST['signin_credit_min']) : $currentMin;
        $max = isset($_POST['signin_credit_max']) ? max(0, (int)$_POST['signin_credit_max']) : $currentMax;

        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }

        return $min . ',' . $max;
    }

    private static function buildCreditRuleText(array $credits, array $dailyMaxes, array $enabledActions): string {
        $currentRules = CreditModel::getRules();
        $lines = [];

        foreach (CreditModel::getActionLabels() as $action => $label) {
            if ($action === CreditModel::ACTION_SIGNIN) {
                continue;
            }

            $enabled = isset($enabledActions[$action]);
            $currentRule = $currentRules[$action] ?? ['credit' => 0, 'daily_max' => 0];
            $credit = $enabled ? (int)($credits[$action] ?? $currentRule['credit']) : 0;
            $dailyMax = $enabled ? max(0, (int)($dailyMaxes[$action] ?? $currentRule['daily_max'])) : 0;

            if ($credit <= 0) {
                $dailyMax = 0;
            } elseif ($dailyMax < $credit) {
                $dailyMax = $credit;
            }

            $lines[] = $action . ',' . $credit . ',' . $dailyMax;
        }

        return implode("\n", $lines);
    }

    private static function buildUsergroupPermissions(): array {
        $permissions = [];
        foreach (UsergroupModel::PERMISSION_KEYS as $key) {
            $permissions[$key] = Request::postInt($key);
        }
        return $permissions;
    }

    public static function forums(): void {
        Template::clear();
        Permission::requireAdminPermission('admin_forum');

        $forums = ForumModel::getForumsFlat();

        Template::set('title', '版块管理');
        Template::set('forums', $forums);
        Template::set('parentForums', $forums);
        Template::set('success', Request::getString('success'));
        Template::set('user', Session::getUser());
        Template::display('admin/forums');
    }

    public static function forumAdd(): void {
        Template::clear();
        Permission::requireAdminPermission('admin_forum');

        $parentForums = ForumModel::getForumsFlat();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = Request::postString('name');
            $upFid = Request::postInt('up_fid');

            if (empty($name)) {
                $error = '版块名称不能为空';
            } else {
                $newFid = ForumModel::create([
                    'name' => $name,
                    'up_fid' => $upFid,
                    'status' => 1,
                    'json_data' => json_encode(['group_permissions' => self::buildForumGroupPermissions()]),
                ]);
                self::logAction('forum_add', "添加版块: {$name} (FID: {$newFid})");
                Response::redirect('index.php?c=admin&a=forums&success=' . urlencode('版块已添加'));
            }
        }

        Template::set('title', '添加版块');
        Template::set('parentForums', $parentForums);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/forum_add');
    }

    public static function forumEdit(int $fid): void {
        Permission::requireAdminPermission('admin_forum');

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
                        'json_data' => json_encode(['group_permissions' => self::buildForumGroupPermissions()]),
                    ]);
                    if ($result === false) {
                        $error = '更新失败';
                    } else {
                        self::logAction('forum_edit', "编辑版块: {$forum['name']} (FID: {$fid})");
                        Response::redirect('index.php?c=admin&a=forums&success=' . urlencode('版块已更新'));
                    }
                } catch (Exception $e) {
                    $error = '更新出错: ' . $e->getMessage();
                }
            }
        }

        Template::set('title', '编辑版块');
        Template::set('forum', $forum);
        Template::set('parentForums', $parentForums);
        Template::set('usergroups', UsergroupModel::getAll());
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/forum_edit');
    }

    private static function buildForumGroupPermissions(): array {
        $permissions = [];
        foreach (ForumModel::GROUP_PERMISSION_KEYS as $key) {
            $permissions[$key] = array_map('intval', Request::postArray('group_' . $key));
        }
        return $permissions;
    }

    public static function forumDelete(int $fid): void {
        Permission::requireAdminPermission('admin_forum');
        self::requirePost();
        $forum = ForumModel::get($fid);
        $forums = array_merge([$forum], ForumModel::getDescendantsFlat($fid));
        $forumIds = array_values(array_filter(array_map(static fn($item): int => (int)($item['fid'] ?? 0), $forums)));
        self::deleteThreadsByForumIds($forumIds);
        foreach ($forumIds as $forumId) {
            ModeratorModel::deleteByFid($forumId);
            ForumModel::delete($forumId);
        }
        if ($forum) {
            self::logAction('forum_delete', "删除版块: {$forum['name']} (FID: {$fid})");
        }
        Response::redirect('index.php?c=admin&a=forums&success=' . urlencode('版块已删除'));
    }

    public static function threads(): void {
        Template::clear();
        Permission::requireAdminPermission('admin_thread');

        $page = Request::getInt('page', 1);
        $fid = Request::getInt('fid');
        $keyword = Request::getString('keyword');
        $author = Request::getString('author');

        $searchValid = true;
        $uid = 0;

        if ($author !== '') {
            if (ctype_digit($author)) {
                $uid = (int)$author;
                if (!MemberModel::get($uid)) {
                    $searchValid = false;
                }
            } else {
                $member = MemberModel::getByUsername($author);
                if ($member) {
                    $uid = (int)$member['uid'];
                } else {
                    $searchValid = false;
                }
            }
        }

        $threads = [];
        $total = 0;
        
        if ($searchValid) {
            $threads = ThreadModel::search($page, 'tid', $fid, $uid, $keyword);
            $total = ThreadModel::searchCount($fid, $uid, $keyword);
        }

        $users = [];
        if (!empty($threads)) {
            $users = MemberModel::getMembersByUids(ThreadViewHelper::collectUserIds($threads));
        }

        $forums = ForumModel::getForumsFlat();

        Template::set('title', '主题管理');
        Template::set('threads', $threads);
        Template::set('users', $users);
        Template::set('forums', $forums);
        Template::set('fid', $fid);
        Template::set('keyword', $keyword);
        Template::set('author', $author);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('admin/threads');
    }

    public static function threadDelete(int $tid): void {
        Permission::requireAdminPermission('admin_thread');
        self::requirePost();
        self::deleteThreadWithCounters($tid);

        Response::redirect('index.php?c=admin&a=threads');
    }

    public static function threadBatch(): void {
        Permission::requireAdminPermission('admin_thread');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = Request::postRaw('action');
            $tids = Request::postArray('tids');

            if ($action == 'delete' && $tids) {
                foreach ($tids as $tid) {
                    self::deleteThreadWithCounters((int)$tid);
                }

                self::logAction('batch_delete_thread', "批量删除主题: " . implode(',', $tids));
            } elseif ($action == 'move' && $tids) {
                $fid = Request::postInt('fid');
                $affectedFids = [$fid];

                foreach ($tids as $tid) {
                    $thread = ThreadModel::get((int)$tid);
                    if ($thread) {
                        $affectedFids[] = (int)$thread['fid'];
                        ThreadModel::update((int)$tid, ['fid' => $fid]);
                        PostModel::updateFidByTid((int)$tid, $fid);
                        self::logAction(
                            'thread_move',
                            '转移主题: ' . ($thread['subject'] ?? '') . " (TID: {$tid}, FID: {$thread['fid']}->{$fid})",
                            (int)$tid,
                            0,
                            (int)($thread['uid'] ?? 0)
                        );
                    }
                }
                self::rebuildForumStats($affectedFids);

                self::logAction('batch_move_thread', "批量移动主题到版块$fid: " . implode(',', $tids));
            }
        }

        Response::redirect('index.php?c=admin&a=threads');
    }

    public static function audits(): void {
        Template::clear();
        Permission::requireAdminPermission('admin_thread');

        $filter = Request::getString('filter', 'all');
        if (!in_array($filter, ['all', 'thread', 'post', 'report', 'done', 'rejected'], true)) {
            $filter = 'all';
        }
        $audits = AuditModel::getList($filter);
        $pendingStats = AuditModel::getPendingStats();
        $tids = array_values(array_filter(array_unique(array_map('intval', array_column($audits, 'tid')))));
        $threads = ThreadModel::getThreadsByTids($tids);
        $users = MemberModel::getMembersByUids(ThreadViewHelper::collectUserIds(array_values($threads)));
        $posts = [];
        if ($filter === 'post') {
            $pids = array_values(array_filter(array_unique(array_map('intval', array_column($audits, 'pid')))));
            $posts = PostModel::getPostsByPids($pids);
        }
        $reportUids = [];
        foreach ($audits as $audit) {
            if ($audit['type'] === 'report') {
                $jsonData = json_decode((string)$audit['json_data'], true) ?: [];
                $reportUids[] = (int)($jsonData['report_uid'] ?? 0);
            }
        }
        $reportUsers = MemberModel::getMembersByUids($reportUids);
        $auditUids = [];
        foreach ($audits as $audit) {
            $jsonData = json_decode((string)$audit['json_data'], true) ?: [];
            $auditUids[] = (int)($jsonData['audit_uid'] ?? 0);
        }
        $auditUsers = MemberModel::getMembersByUids($auditUids);

        Template::set('title', '内容审核');
        Template::set('audits', $audits);
        Template::set('filter', $filter);
        Template::set('pendingStats', $pendingStats);
        Template::set('threads', $threads);
        Template::set('users', $users);
        Template::set('posts', $posts);
        Template::set('reportUsers', $reportUsers);
        Template::set('auditUsers', $auditUsers);
        Template::set('user', Session::getUser());
        Template::display('admin/audits');
    }

    public static function auditView(int $did): void {
        Permission::requireAdminPermission('admin_thread');

        $audit = AuditModel::get($did) ?? AuditModel::getArchive($did);
        if (!$audit) {
            Response::json(['success' => false, 'message' => '审核任务不存在'], 404);
        }

        $content = '';
        if ($audit['type'] === 'thread') {
            $post = PostModel::getThreadPost((int)$audit['tid']);
            $content = (string)($post['message'] ?? '');
        } elseif ($audit['type'] === 'post') {
            $post = PostModel::get((int)$audit['pid']);
            $content = (string)($post['message'] ?? '');
        } elseif ($audit['type'] === 'report') {
            $post = PostModel::get((int)$audit['pid']);
            $content = (string)($post['message'] ?? '');
        }

        Response::json(['success' => true, 'content' => $content]);
    }

    public static function auditHandle(int $did): void {
        Permission::requireAdminPermission('admin_thread');
        self::requirePost();

        $audit = AuditModel::get($did);
        $status = Request::postString('status') === 'pass' ? 1 : -1;
        if (!$audit) {
            Response::json(['success' => false, 'message' => '审核任务不存在'], 404);
        }

        $type = (string)$audit['type'];
        $tid = (int)$audit['tid'];
        $pid = (int)$audit['pid'];

        if ($type === 'thread') {
            $thread = ThreadModel::get($tid);
            ThreadModel::update($tid, ['sort_order' => $status === 1 ? 0 : -2]);
            if ($status === 1) {
                PostModel::approveByTid($tid);
            }
            DataModel::updateCount('pending_threads', -1);
            if ($status === 1 && $thread) {
                $threadPost = PostModel::getThreadPost($tid);
                MemberModel::incrementThreadNum((int)$thread['uid']);
                ForumModel::incrementThreadNum((int)$thread['fid'], $tid);
                ForumModel::incrementTodayNum((int)$thread['fid']);
                if ((int)CreditModel::getRule(CreditModel::ACTION_THREAD_CREATE)['credit'] > 0) {
                    CreditModel::apply(
                        CreditModel::ACTION_THREAD_CREATE,
                        (int)$thread['uid'],
                        '发布主题：' . ($thread['subject'] ?? ''),
                        "index.php?c=thread&a=index&tid={$tid}"
                    );
                }
                if ($threadPost) {
                    ThreadController::handleAtMentions((string)$threadPost['message'], $tid, (int)$threadPost['pid'], (int)$thread['uid']);
                }
            }
            if ($thread) {
                $threadPost = $threadPost ?? PostModel::getThreadPost($tid);
                self::logAction(
                    $status === 1 ? 'thread_approve' : 'thread_reject',
                    ($status === 1 ? '通过主题: ' : '拒绝主题: ') . ($thread['subject'] ?? '') . " (TID: {$tid})",
                    $tid,
                    (int)($threadPost['pid'] ?? 0),
                    (int)($thread['uid'] ?? 0)
                );
            }
        } elseif ($type === 'post') {
            PostModel::update($pid, ['sort_order' => $status === 1 ? 0 : -2]);
            DataModel::updateCount('pending_posts', -1);
            if ($status === 1) {
                $post = PostModel::get($pid);
                if ($post) {
                    ThreadModel::updateReply((int)$post['tid'], (int)$post['uid']);
                    MemberModel::incrementReplyNum((int)$post['uid']);
                    ForumModel::incrementReplyNum((int)$post['fid'], (int)$post['tid']);
                    ForumModel::incrementTodayNum((int)$post['fid']);
                    if ((int)CreditModel::getRule(CreditModel::ACTION_THREAD_REPLY)['credit'] > 0) {
                        $thread = ThreadModel::get((int)$post['tid']);
                        CreditModel::apply(
                            CreditModel::ACTION_THREAD_REPLY,
                            (int)$post['uid'],
                            '回复主题：' . ($thread['subject'] ?? ''),
                            "index.php?c=thread&a=index&tid={$post['tid']}&pid={$pid}"
                        );
                    }
                    $thread = $thread ?? ThreadModel::get((int)$post['tid']);
                    if ($thread && (int)$thread['uid'] !== (int)$post['uid']) {
                        NotifyModel::addNotify((int)$thread['uid'], (int)$post['uid'], (int)$post['tid'], $pid, '回复了你的主题');
                    }
                    $quoteUid = (int)($post['quote_uid'] ?? 0);
                    if ($thread && $quoteUid > 0 && $quoteUid !== (int)$post['uid'] && $quoteUid !== (int)$thread['uid']) {
                        NotifyModel::addNotify($quoteUid, (int)$post['uid'], (int)$post['tid'], $pid, '在 ' . ($thread['subject'] ?? '主题') . ' 中引用了你的回复');
                    }
                    ThreadController::handleAtMentions((string)$post['message'], (int)$post['tid'], $pid, (int)$post['uid']);
                }
            }
        } elseif ($type === 'report') {
            DataModel::updateCount('pending_reports', -1);
        }

        AuditModel::finish($did, $status, Session::getUid());
        Response::json(['success' => true]);
    }

    public static function usergroups(): void {
        Template::clear();
        Permission::requireAdminPermission('admin_usergroup');

        $groups = UsergroupModel::getAll();

        Template::set('title', '用户组管理');
        Template::set('groups', $groups);
        Template::set('success', Request::getString('success'));
        Template::set('user', Session::getUser());
        Template::display('admin/usergroups');
    }

    public static function usergroupAdd(): void {
        Template::clear();
        Permission::requireAdminPermission('admin_usergroup');

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = Request::postString('title');
            $groupType = Request::postRaw('group_type', 'member');
            $creditLower = Request::postInt('credit_lower');
            if ($groupType !== 'member') {
                $creditLower = 0;
            }

            if (empty($title)) {
                $error = '用户组名称不能为空';
            } else {
                $jsonData = json_encode(self::buildUsergroupPermissions());
                $newGid = UsergroupModel::create([
                    'title' => $title,
                    'group_type' => $groupType,
                    'credit_lower' => $creditLower,
                    'json_data' => $jsonData,
                ]);
                self::logAction('usergroup_add', "添加用户组: {$title} (GID: {$newGid})");
                Response::redirect('index.php?c=admin&a=usergroups&success=' . urlencode('用户组已添加'));
            }
        }

        Template::set('title', '添加用户组');
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/usergroup_add');
    }

    public static function usergroupEdit(int $gid): void {
        Permission::requireAdminPermission('admin_usergroup');

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
            if ($groupType !== 'member') {
                $creditLower = 0;
            }

            if (empty($title)) {
                $error = '用户组名称不能为空';
            } else {
                $jsonData = json_encode(self::buildUsergroupPermissions());
                UsergroupModel::update($gid, [
                    'title' => $title,
                    'group_type' => $groupType,
                    'credit_lower' => $creditLower,
                    'json_data' => $jsonData,
                ]);
                self::logAction('usergroup_edit', "编辑用户组: {$group['title']} (GID: {$gid})");
                Response::redirect('index.php?c=admin&a=usergroups&success=' . urlencode('用户组已更新'));
            }
        }

        Template::set('title', '编辑用户组');
        Template::set('group', $group);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/usergroup_edit');
    }

    public static function usergroupDelete(int $gid): void {
        Permission::requireAdminPermission('admin_usergroup');
        self::requirePost();
        $group = UsergroupModel::get($gid);
        UsergroupModel::delete($gid);
        if ($group) {
            self::logAction('usergroup_delete', "删除用户组: {$group['title']} (GID: {$gid})");
        }
        Response::redirect('index.php?c=admin&a=usergroups&success=' . urlencode('用户组已删除'));
    }

    public static function users(): void {
        Template::clear();
        Permission::requireAdminPermission('admin_user');

        $page = Request::getInt('page', 1);
        $keyword = Request::getString('keyword');
        $gid = Request::getInt('gid');
        $email = Request::getString('email');
        $regIp = Request::getString('reg_ip');
        $lastIp = Request::getString('last_ip');

        $users = MemberModel::search($keyword, $gid, $page, $email, $regIp, $lastIp);
        $total = MemberModel::searchCount($keyword, $gid, $email, $regIp, $lastIp);
        $groups = UsergroupModel::getAll();

        Template::set('title', '用户管理');
        Template::set('users', $users);
        Template::set('groups', $groups);
        Template::set('keyword', $keyword);
        Template::set('gid', $gid);
        Template::set('email', $email);
        Template::set('regIp', $regIp);
        Template::set('lastIp', $lastIp);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('success', Request::getString('success'));
        Template::set('user', Session::getUser());
        Template::display('admin/users');
    }

    public static function userEdit(int $uid): void {
        Permission::requireAdminPermission('admin_user');

        if ($uid <= 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $uid = Request::postInt('uid');
        }

        $member = MemberModel::get($uid);
        $groups = UsergroupModel::getAll();

        if (!$member) {
            if (Request::getBool('ajax')) {
                Response::json(['success' => false, 'message' => '用户不存在'], 404);
            }
            Response::redirect('index.php?c=admin&a=users');
        }

        if (Request::getBool('ajax') && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => true, 'user' => $member]);
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $username = Request::postString('username');
                $email = Request::postString('email');

                if (empty($username)) {
                    throw new \RuntimeException('用户名不能为空');
                }
                if (empty($email)) {
                    throw new \RuntimeException('邮箱不能为空');
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('邮箱格式不正确');
                }

                $gid = Request::postInt('gid');
                if (!$gid) {
                    $gid = (int)SettingModel::get('register_default_gid', '1');
                }
                if (!isset($groups[$gid])) {
                    throw new \RuntimeException('无效的用户组');
                }

                $data = [
                    'username' => $username,
                    'email' => $email,
                    'avatar' => Request::postString('avatar'),
                    'gid' => $gid,
                    'credit' => max(0, Request::postInt('credit')),
                    'status' => Request::postInt('status') ?: 0,
                ];

                $password = Request::postRaw('password');
                if (!empty($password)) {
                    $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                }

                $result = MemberModel::update($uid, $data);
                if ($result === 0) {
                    if (!MemberModel::get($uid)) {
                        throw new \RuntimeException('更新失败：用户不存在');
                    }
                }
                self::logAction('user_edit', "编辑用户: {$member['username']} (UID: {$uid})", 0, 0, $uid);
                if (Response::isAjaxRequest()) {
                    Response::json(['success' => true, 'message' => '用户已更新']);
                }
                Response::redirect('index.php?c=admin&a=users&success=' . urlencode('用户已更新'));
            } catch (\Throwable $e) {
                if (Response::isAjaxRequest()) {
                    Response::json(['success' => false, 'message' => $e->getMessage()], 500);
                }
                $error = $e->getMessage();
            }
        }

        Template::set('title', '编辑用户');
        Template::set('member', $member);
        Template::set('groups', $groups);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('admin/user_edit');
    }

    public static function userDelete(int $uid): void {
        Permission::requireAdminPermission('admin_user');
        self::requirePost();
        $member = MemberModel::get($uid);
        if ($member) {
            MemberModel::delete($uid);
            self::logAction('user_delete', "删除用户: {$member['username']} (UID: {$uid})", 0, 0, $uid, json_encode($member, JSON_UNESCAPED_UNICODE));
        }
        Response::redirect('index.php?c=admin&a=users&success=' . urlencode('用户已删除'));
    }

    public static function logs(): void {
        Template::clear();
        Permission::requireAdminPermission('admin_log');

        $page = Request::getInt('page', 1);
        $tid = Request::getInt('tid');
        $authorid = Request::getInt('authorid');
        $message = Request::getString('message');
        $operator = Request::getString('operator');
        $action = Request::getString('action_type');
        $actionLabels = self::getModLogActionLabels();
        if ($action !== '' && !isset($actionLabels[$action])) {
            $action = '';
        }
        $operatorUid = self::resolveOperatorUid($operator);
        $logs = ModLogModel::getLogs($page, $tid, $message, $operatorUid, $action, $authorid);
        $total = ModLogModel::getCount($tid, $message, $operatorUid, $action, $authorid);

        $users = [];
        if (!empty($logs)) {
            $uids = array_unique(array_column($logs, 'uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        Template::set('title', '管理日志');
        Template::set('logs', $logs);
        Template::set('users', $users);
        Template::set('tid', $tid);
        Template::set('authorid', $authorid);
        Template::set('message', $message);
        Template::set('operator', $operator);
        Template::set('actionType', $action);
        Template::set('actionLabels', $actionLabels);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('admin/logs');
    }

    public static function restorePost(int $did): void {
        Permission::requireAdminPermission('admin_log');
        self::requirePost();

        $log = ModLogModel::get($did);
        if ($log && ($log['action'] ?? '') === 'user_delete') {
            self::restoreUserFromLog($log);
            return;
        }
        if ($log && ($log['action'] ?? '') === 'thread_delete') {
            self::restoreThreadFromLog($log);
            return;
        }
        if ($log && in_array(($log['action'] ?? ''), ['thread_edit', 'post_edit'], true)) {
            self::restorePostEditFromLog($log);
            return;
        }
        if (!$log || ($log['action'] ?? '') !== 'post_delete' || (string)($log['archive_data'] ?? '') === '') {
            Response::error('存档不存在');
        }

        $tid = (int)$log['tid'];
        $pid = (int)$log['pid'];
        $thread = ThreadModel::get($tid);
        if (!$thread) {
            Response::error('主题不存在');
        }
        if (PostModel::get($pid)) {
            Response::error('回帖已存在');
        }

        $post = json_decode((string)$log['archive_data'], true);
        if (!is_array($post) || empty($post['pid'])) {
            Response::error('存档数据不完整');
        }
        $uid = (int)($post['uid'] ?? $log['authorid']);

        Database::beginTransaction();
        try {
            PostModel::restore([
                'pid' => $pid,
                'fid' => (int)$thread['fid'],
                'tid' => $tid,
                'is_thread' => 0,
                'uid' => $uid,
                'dateline' => (int)($post['dateline'] ?? 0),
                'edited' => (int)($post['edited'] ?? 0),
                'report_time' => (int)($post['report_time'] ?? 0),
                'message' => (string)($post['message'] ?? ''),
                'credit_log' => (string)($post['credit_log'] ?? '[]'),
                'ip' => (string)($post['ip'] ?? ''),
                'rate_num' => (int)($post['rate_num'] ?? 0),
                'sort_order' => 0,
                'reply_num' => (int)($post['reply_num'] ?? 0),
                'quote_pid' => (int)($post['quote_pid'] ?? 0),
                'quote_uid' => (int)($post['quote_uid'] ?? 0),
                'quote_floor' => (int)($post['quote_floor'] ?? 0),
            ]);
            ThreadModel::rebuildReplyStats($tid);
            MemberModel::incrementReplyNum($uid);
            ForumModel::rebuildStats((int)$thread['fid']);
            self::logAction('post_restore', "还原回帖: TID: {$tid}, PID: {$pid}", $tid, $pid, $uid);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            Response::error('还原失败');
        }

        Response::json(['success' => true, 'message' => '已还原']);
    }

    private static function restorePostEditFromLog(array $log): void {
        $post = json_decode((string)($log['archive_data'] ?? ''), true);
        if (!is_array($post) || empty($post['pid'])) {
            Response::error('存档数据不完整');
        }
        $pid = (int)$post['pid'];
        $current = PostModel::get($pid);
        if (!$current) {
            Response::error('帖子不存在');
        }
        try {
            PostModel::update($pid, [
                'message' => (string)($post['message'] ?? ''),
                'edited' => (int)($post['edited'] ?? 0),
            ]);
            self::logAction(
                ($log['action'] ?? '') === 'thread_edit' ? 'thread_edit_restore' : 'post_edit_restore',
                "还原编辑内容: TID: {$post['tid']}, PID: {$pid}",
                (int)($post['tid'] ?? 0),
                $pid,
                (int)($post['uid'] ?? 0)
            );
        } catch (\Throwable $e) {
            Response::error('还原失败');
        }
        Response::json(['success' => true, 'message' => '已还原']);
    }

    private static function restoreUserFromLog(array $log): void {
        $member = json_decode((string)($log['archive_data'] ?? ''), true);
        if (!is_array($member) || empty($member['uid'])) {
            Response::error('存档数据不完整');
        }
        $uid = (int)$member['uid'];
        if (MemberModel::get($uid)) {
            Response::error('用户已存在');
        }
        try {
            MemberModel::restore([
                'uid' => $uid,
                'username' => (string)($member['username'] ?? ''),
                'gid' => (int)($member['gid'] ?? 0),
                'avatar' => (string)($member['avatar'] ?? ''),
                'password' => (string)($member['password'] ?? ''),
                'auth_secret' => (string)($member['auth_secret'] ?? ''),
                'auth_enabled' => (int)($member['auth_enabled'] ?? 0),
                'notify_num' => (int)($member['notify_num'] ?? 0),
                'credit' => (int)($member['credit'] ?? 0),
                'reg_ip' => (string)($member['reg_ip'] ?? ''),
                'reg_date' => (int)($member['reg_date'] ?? 0),
                'last_ip' => (string)($member['last_ip'] ?? ''),
                'last_visit' => (int)($member['last_visit'] ?? 0),
                'reply_time' => (int)($member['reply_time'] ?? 0),
                'reply_num' => (int)($member['reply_num'] ?? 0),
                'thread_num' => (int)($member['thread_num'] ?? 0),
                'fav_num' => (int)($member['fav_num'] ?? 0),
                'inbox_num' => (int)($member['inbox_num'] ?? 0),
                'log_num' => (int)($member['log_num'] ?? 0),
                'email' => (string)($member['email'] ?? ''),
                'email_status' => (int)($member['email_status'] ?? 0),
                'signin_time' => (int)($member['signin_time'] ?? 0),
                'invisible' => (int)($member['invisible'] ?? 0),
                'timeoffset' => (string)($member['timeoffset'] ?? ''),
                'search_time' => (int)($member['search_time'] ?? 0),
                'status' => (int)($member['status'] ?? 0),
                'json_data' => (string)($member['json_data'] ?? '{}'),
            ]);
            self::logAction('user_restore', "还原用户: {$member['username']} (UID: {$uid})", 0, 0, $uid);
        } catch (\Throwable $e) {
            Response::error('还原失败');
        }
        Response::json(['success' => true, 'message' => '已还原']);
    }

    private static function restoreThreadFromLog(array $log): void {
        $thread = json_decode((string)($log['archive_data'] ?? ''), true);
        if (!is_array($thread) || empty($thread['tid'])) {
            Response::error('存档数据不完整');
        }
        $tid = (int)$thread['tid'];
        if (ThreadModel::get($tid)) {
            Response::error('主题已存在');
        }
        Database::beginTransaction();
        try {
            ThreadModel::restore([
                'tid' => $tid,
                'fid' => (int)($thread['fid'] ?? 0),
                'pid' => (int)($thread['pid'] ?? 0),
                'typeid' => (int)($thread['typeid'] ?? 0),
                'read_perm' => (int)($thread['read_perm'] ?? 0),
                'uid' => (int)($thread['uid'] ?? 0),
                'pm_uid' => (int)($thread['pm_uid'] ?? 0),
                'subject' => (string)($thread['subject'] ?? ''),
                'hash' => (string)($thread['hash'] ?? ''),
                'dateline' => (int)($thread['dateline'] ?? 0),
                'sort_order' => (int)($thread['sort_order'] ?? 0),
                'highlight' => (int)($thread['highlight'] ?? 0),
                'digest' => (int)($thread['digest'] ?? 0),
                'closed' => (int)($thread['closed'] ?? 0),
                'reply_time' => (int)($thread['reply_time'] ?? 0),
                'reply_uid' => (int)($thread['reply_uid'] ?? 0),
                'reply_num' => (int)($thread['reply_num'] ?? 0),
                'view_num' => (int)($thread['view_num'] ?? 0),
                'fav_num' => (int)($thread['fav_num'] ?? 0),
                'log_num' => (int)($thread['log_num'] ?? 0),
            ]);
            ThreadModel::rebuildReplyStats($tid);
            MemberModel::incrementThreadNum((int)($thread['uid'] ?? 0));
            ForumModel::rebuildStats((int)($thread['fid'] ?? 0));
            self::logAction('thread_restore', '还原主题: ' . (string)($thread['subject'] ?? '') . " (TID: {$tid})", $tid, 0, (int)($thread['uid'] ?? 0));
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            Response::error('还原失败');
        }
        Response::json(['success' => true, 'message' => '已还原']);
    }

    private static function logAction(string $action, string $message, int $tid = 0, int $pid = 0, int $authorid = 0, string $archiveData = ''): void {
        ModLogModel::addLog(Session::getUid(), $action, $message, $tid, $pid, $authorid, $archiveData);
    }

    private static function resolveOperatorUid(string $operator): int {
        if ($operator === '') {
            return 0;
        }
        if (ctype_digit($operator)) {
            return (int)$operator;
        }
        $member = MemberModel::getByUsername($operator);
        return (int)($member['uid'] ?? 0);
    }

    private static function getModLogActionLabels(): array {
        return [
            'thread_delete' => '删除主题',
            'thread_restore' => '还原主题',
            'thread_move' => '转移主题',
            'thread_edit' => '编辑主题',
            'thread_edit_restore' => '还原主题编辑',
            'thread_approve' => '通过主题',
            'thread_reject' => '拒绝主题',
            'post_delete' => '删除回帖',
            'post_restore' => '还原回帖',
            'post_edit' => '编辑回帖',
            'post_edit_restore' => '还原回帖编辑',
            'post_credit' => '帖子评分',
            'user_delete' => '删除用户',
            'user_restore' => '还原用户',
            'user_edit' => '编辑用户',
            'forum_add' => '添加版块',
            'forum_edit' => '编辑版块',
            'forum_delete' => '删除版块',
            'settings_update' => '保存设置',
            'usergroup_add' => '添加用户组',
            'usergroup_edit' => '编辑用户组',
            'usergroup_delete' => '删除用户组',
            'moderator_add' => '添加版主',
            'moderator_edit' => '编辑版主',
            'moderator_delete' => '删除版主',
            'batch_delete_thread' => '批量删除主题',
            'batch_move_thread' => '批量转移主题',
            'cache_opcache_reset' => '清空 OPcache',
            'cache_apcu_clear' => '清空 APCu',
        ];
    }

    private static function requirePost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect('index.php?c=admin&a=index');
        }
    }

    private static function deleteThreadsByForumIds(array $forumIds): void {
        $forumIds = array_values(array_filter(array_unique(array_map('intval', $forumIds))));
        if (empty($forumIds)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($forumIds), '?'));
        $threads = Database::fetchAll('SELECT tid FROM ' . ThreadModel::TABLE . " WHERE fid IN ($placeholders)", $forumIds);
        foreach ($threads as $thread) {
            self::deleteThreadWithCounters((int)$thread['tid']);
        }
    }

    private static function deleteThreadWithCounters(int $tid): void {
        $thread = ThreadModel::get($tid);
        if (!$thread) {
            return;
        }

        ThreadModel::delete($tid);
        self::closeThreadAudits($tid);
        self::rebuildForumStats([(int)$thread['fid']]);
        self::logAction(
            'thread_delete',
            '删除主题: ' . ($thread['subject'] ?? '') . " (TID: {$tid})",
            $tid,
            0,
            (int)($thread['uid'] ?? 0),
            json_encode($thread, JSON_UNESCAPED_UNICODE)
        );
    }

    private static function closeThreadAudits(int $tid): void {
        $closedAudits = AuditModel::finishPendingByThread($tid, -1, Session::getUid());
        if (($closedAudits['thread'] ?? 0) > 0) {
            DataModel::updateCount('pending_threads', -($closedAudits['thread'] ?? 0));
        }
        if (($closedAudits['post'] ?? 0) > 0) {
            DataModel::updateCount('pending_posts', -($closedAudits['post'] ?? 0));
        }
        if (($closedAudits['report'] ?? 0) > 0) {
            DataModel::updateCount('pending_reports', -($closedAudits['report'] ?? 0));
        }
    }

    private static function rebuildMemberContentStats(array $uids): void {
        foreach (array_values(array_filter(array_unique(array_map('intval', $uids)))) as $uid) {
            MemberModel::rebuildContentStats($uid);
        }
    }

    private static function rebuildForumStats(array $fids): void {
        foreach (array_values(array_filter(array_unique(array_map('intval', $fids)))) as $fid) {
            ForumModel::rebuildStats($fid);
        }
    }

    public static function moderators(int $fid = 0): void {
        Template::clear();
        Permission::requireAdminPermission('admin_forum');

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
        Permission::requireAdminPermission('admin_forum');

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
                    self::logAction('moderator_add', "添加版主: uid={$member['uid']}, fid=$fid", 0, 0, (int)$member['uid']);
                }
            }
            Response::redirect('index.php?c=admin&a=moderators&fid=' . $fid);
        }

        Response::redirect('index.php?c=admin&a=forums');
    }

    public static function moderatorEdit(): void {
        Permission::requireAdminPermission('admin_forum');

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
                self::logAction('moderator_edit', "编辑版主: uid=$uid, fid=$fid", 0, 0, $uid);
            }
            Response::redirect('index.php?c=admin&a=moderators&fid=' . $fid);
        }

        Response::redirect('index.php?c=admin&a=forums');
    }

    public static function moderatorDelete(): void {
        Permission::requireAdminPermission('admin_forum');
        self::requirePost();

        $fid = Request::getInt('fid');
        $uid = Request::getInt('uid');

        if ($fid && $uid) {
            ModeratorModel::delete($uid, $fid);
            self::logAction('moderator_delete', "删除版主: uid=$uid, fid=$fid", 0, 0, $uid);
        }

        Response::redirect('index.php?c=admin&a=moderators&fid=' . $fid);
    }
}
?>
