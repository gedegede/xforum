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
use Models\ForumModel;
use Models\ThreadModel;
use Models\MemberModel;
use Models\ModeratorModel;
use Models\SettingModel;
use Models\AuditModel;
use Lib\Permission;
use Lib\ThreadHelper;

class ForumController {
    public static function index(int $fid = 0): void {
        Template::clear();
        if (!$fid) {
            $from = Request::getString('from');
            $forums = array_values(array_filter(ForumModel::getForumsFlat(), static function(array $forum) use ($from): bool {
                $fid = (int)($forum['fid'] ?? 0);
                return $from === 'create' ? Permission::canPostThread($fid) : Permission::canViewForum($fid);
            }));
            $lastTids = array_values(array_filter(array_unique(array_map('intval', array_column($forums, 'last_tid')))));
            $lastThreads = ThreadModel::getThreadsByTids($lastTids);

            Template::set('title', $from === 'create' ? '选择版块' : '论坛导航');
            Template::set('forums', $forums);
            Template::set('lastThreads', $lastThreads);
            Template::set('from', $from);
            Template::set('user', Session::getUser());
            Template::display('forum/list');
            return;
        }

        $forum = ForumModel::get($fid);
        if (!$forum) {
            Response::redirect('index.php?c=forum&a=index');
        }
        if (!Permission::canViewForum($fid)) {
            Response::redirect('index.php');
        }

        $parentForum = null;
        if (!empty($forum['up_fid'])) {
            $parentForum = ForumModel::get($forum['up_fid']);
        }

        $page = Request::getInt('page', 1);
        $order = Request::getString('order', 'reply_time');
        $allowedOrders = ['reply_time', 'dateline', 'reply_num', 'view_num'];
        if (!in_array($order, $allowedOrders)) {
            $order = 'reply_time';
        }
        $keyword = Request::getString('keyword');
        $searchError = '';
        if ($keyword !== '' && !Permission::canSearch()) {
            $searchError = '无权限搜索';
            $keyword = '';
        }
        if ($keyword !== '' && !self::checkSearchInterval()) {
            $searchError = '搜索过于频繁，请等待 ' . self::getSearchRemainingSeconds() . ' 秒';
            $keyword = '';
        }
        
        $threadsPerPage = (int)SettingModel::get('threads_per_page', '20');
        $threads = ThreadModel::getThreads($fid, $page, $order, $keyword, $threadsPerPage);
        $canAuditForum = Permission::canAuditForum($fid);
        $pendingThreads = $canAuditForum ? self::getPendingThreads($fid, $threadsPerPage) : [];
        $total = empty($keyword) ? (int)($forum['thread_num'] ?? 0) : ThreadModel::getThreadCount($fid, $keyword);
        $pages = (int)ceil($total / $threadsPerPage);

        $users = [];
        if (!empty($threads) || !empty($pendingThreads)) {
            $users = MemberModel::getMembersByUids(ThreadHelper::collectUserIds(array_merge($threads, $pendingThreads)));
        }

        $orderOptions = [
            ['value' => 'reply_time', 'label' => '最后回复'],
            ['value' => 'dateline', 'label' => '最新发布'],
            ['value' => 'reply_num', 'label' => '回复数'],
            ['value' => 'view_num', 'label' => '查看数']
        ];

        $moderators = ModeratorModel::getByFid($fid);
        usort($moderators, function($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });

        $moderatorUsers = [];
        if (!empty($moderators)) {
            $modUids = array_unique(array_column($moderators, 'uid'));
            $moderatorUsers = MemberModel::getMembersByUids($modUids);
        }

        Template::set('title', $forum['name']);
        Template::set('forum', $forum);
        Template::set('parentForum', $parentForum);
        Template::set('subForums', array_values(array_filter(ForumModel::getForums($fid), static function(array $subForum): bool {
            return Permission::canViewForum((int)($subForum['fid'] ?? 0));
        })));
        Template::set('threads', $threads);
        Template::set('pendingThreads', $pendingThreads);
        Template::set('canAuditForum', $canAuditForum);
        Template::set('users', $users);
        Template::set('page', $page);
        Template::set('pages', $pages);
        Template::set('order', $order);
        Template::set('keyword', $keyword);
        Template::set('searchError', $searchError);
        Template::set('orderOptions', $orderOptions);
        Template::set('hotThreads', ThreadModel::getHotThreadsByFid($fid, 5));
        Template::set('moderators', $moderators);
        Template::set('moderatorUsers', $moderatorUsers);
        Template::set('user', Session::getUser());
        Template::display('forum/index');
    }

    private static function checkSearchInterval(): bool {
        $interval = (int)SettingModel::get('search_interval', '10');
        if ($interval <= 0) {
            return true;
        }
        $key = 'last_search_time_' . self::getClientIp();
        $lastSearchTime = (int)Session::get($key, 0);
        if (time() - $lastSearchTime < $interval) {
            return false;
        }
        Session::set($key, time());
        return true;
    }

    private static function getSearchRemainingSeconds(): int {
        $interval = (int)SettingModel::get('search_interval', '10');
        $lastSearchTime = (int)Session::get('last_search_time_' . self::getClientIp(), 0);
        return max(1, $interval - (time() - $lastSearchTime));
    }

    private static function getClientIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private static function getPendingThreads(int $fid, int $limit): array {
        $tids = AuditModel::getPendingThreadTidsByFid($fid, $limit);
        if (empty($tids)) {
            return [];
        }

        $threads = ThreadModel::getPendingThreadsByTids($tids);
        $result = [];
        foreach ($tids as $tid) {
            $thread = $threads[$tid] ?? null;
            if ($thread && (int)($thread['fid'] ?? 0) === $fid && (int)($thread['sort_order'] ?? 0) === -1) {
                $result[] = $thread;
                if (count($result) >= $limit) {
                    break;
                }
            }
        }
        return $result;
    }
}
?>
