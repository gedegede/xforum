<?php
declare(strict_types=1);

namespace Controllers;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Template;
use Lib\Session;
use Lib\Request;
use Models\ForumModel;
use Models\ThreadModel;
use Models\MemberModel;
use Models\SettingModel;
use Models\SessionModel;
use Models\UsergroupModel;
use Models\CreditModel;
use Models\AuditModel;
use Lib\Permission;
use Lib\ThreadHelper;

class HomeController {
    public static function index(): void {
        Template::clear();
        
        $page = Request::getInt('page', 1);
        $page = $page < 1 ? 1 : $page;
        
        $order = Request::getString('order', 'reply_time');
        $orderMap = ['tid', 'reply_time', 'dateline', 'reply_num', 'view_num'];
        if (!in_array($order, $orderMap, true)) {
            $order = 'reply_time';
        }
        
        $keyword = Request::getString('keyword', '');
        
        $searchInterval = (int)SettingModel::get('search_interval', '10');
        $searchError = '';
        
        if (!empty($keyword) && !Permission::canSearch()) {
            $searchError = '无权限搜索';
            $keyword = '';
        }

        if (!empty($keyword) && $searchInterval > 0) {
            $ip = self::getClientIp();
            $lastSearchTime = Session::get('last_search_time_' . $ip, 0);
            if (time() - $lastSearchTime < $searchInterval) {
                $remaining = $searchInterval - (time() - $lastSearchTime);
                $searchError = '搜索过于频繁，请等待 ' . $remaining . ' 秒';
                $keyword = '';
            } else {
                Session::set('last_search_time_' . $ip, time());
            }
        }
        
        $collapsedFids = SettingModel::getCollapsedFids();
        
        $threadsPerPage = (int)SettingModel::get('threads_per_page', '20');
        $allThreads = ThreadModel::getHomeThreadsWithFilter($page, $order, $keyword, $threadsPerPage);
        $forums = [];
        if (!empty($allThreads)) {
            $fids = array_unique(array_map('intval', array_column($allThreads, 'fid')));
            $forums = ForumModel::getForumsByIds($fids);
        }

        $threads = [];
        $collapsedThreads = [];
        $collapsedTotal = 0;
        
        foreach ($allThreads as $thread) {
            $thread = ThreadHelper::maskUnauthorizedSubject($thread);
            $fid = (int)($thread['fid'] ?? 0);
            if (!empty($collapsedFids) && in_array($fid, $collapsedFids, true)) {
                $collapsedThreads[] = $thread;
                $collapsedTotal++;
            } else {
                $threads[] = $thread;
            }
        }
        $total = ThreadModel::getHomeThreadCount($keyword);
        $pages = (int)ceil($total / $threadsPerPage);

        $users = [];
        if (!empty($allThreads)) {
            $users = MemberModel::getMembersByUids(ThreadHelper::collectUserIds($allThreads));
        }

        $orderOptions = [
            ['value' => 'reply_time', 'label' => '最后回复'],
            ['value' => 'tid', 'label' => '最新发布'],
            ['value' => 'reply_num', 'label' => '回复数'],
            ['value' => 'view_num', 'label' => '查看数'],
        ];

        $userStats = [];
        $userGroup = null;
        $currentUser = Session::getUser();
        if ($currentUser) {
            $userStats = [
                'thread_count' => (int)($currentUser['thread_num'] ?? 0),
                'post_count' => (int)($currentUser['reply_num'] ?? 0),
                'fav_count' => (int)($currentUser['fav_num'] ?? 0),
                'credit' => (int)($currentUser['credit'] ?? 0),
                'inbox_num' => (int)($currentUser['inbox_num'] ?? 0),
                'notify_num' => (int)($currentUser['notify_num'] ?? 0),
                'signed_today' => CreditModel::hasSignedToday((int)$currentUser['uid']),
            ];
            $userGroup = UsergroupModel::get((int)($currentUser['gid'] ?? 0));
        }

        $noticeFid = (int)SettingModel::get('notice_forum_fid', '0');
        $noticeThreads = ThreadHelper::maskUnauthorizedSubjects(ThreadModel::getHomeNoticeThreads($noticeFid, 5));

        $onlineCount = self::getOnlineCount();

        $hotForums = array_values(array_filter(ForumModel::getHotForums(5), static function(array $forum): bool {
            return Permission::canViewForum((int)($forum['fid'] ?? 0));
        }));

        $modStats = [];
        $canManage = false;
        if ($currentUser) {
            $canManage = Permission::isAdmin();
            if ($canManage) {
                $modStats = AuditModel::getPendingStats();
                if (array_sum($modStats) <= 0) {
                    $modStats = [];
                }
            }
        }

        $collapsedForums = [];
        if (!empty($collapsedFids)) {
            $collapsedForums = array_filter(ForumModel::getForumsByIds($collapsedFids), static function(array $forum): bool {
                return Permission::canViewForum((int)($forum['fid'] ?? 0));
            });
        }

        Template::set('title', 'XForum');
        Template::set('forums', $forums);
        Template::set('threads', $threads);
        Template::set('collapsedThreads', $collapsedThreads);
        Template::set('collapsedTotal', $collapsedTotal);
        Template::set('collapsedForums', $collapsedForums);
        Template::set('users', $users);
        Template::set('user', $currentUser);
        Template::set('userGroup', $userGroup);
        Template::set('userStats', $userStats);
        Template::set('noticeThreads', $noticeThreads);
        Template::set('onlineCount', $onlineCount);
        Template::set('hotForums', $hotForums);
        Template::set('order', $order);
        Template::set('keyword', $keyword);
        Template::set('orderOptions', $orderOptions);
        Template::set('page', $page);
        Template::set('pages', $pages);
        Template::set('canManage', $canManage);
        Template::set('modStats', $modStats);
        Template::set('searchError', $searchError);
        Template::display('home/index');
    }

    private static function getClientIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private static function getOnlineCount(): int {
        return SessionModel::getOnlineCount();
    }
}
?>
