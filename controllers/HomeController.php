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
use Models\PostModel;
use Models\SettingModel;
use Models\SessionModel;
use Models\UsergroupModel;
use Models\DataModel;
use Models\CreditModel;
use Lib\Permission;

class HomeController {
    public static function index(): void {
        Template::clear();
        
        $page = Request::getInt('page', 1);
        $page = $page < 1 ? 1 : $page;
        
        $order = Request::getString('order', 'reply_time');
        $orderMap = ['tid', 'reply_time', 'dateline', 'reply_num', 'view_num'];
        if (!in_array($order, $orderMap)) {
            $order = 'reply_time';
        }
        
        $keyword = Request::getString('keyword', '');
        
        $collapsedFids = SettingModel::getCollapsedFids();
        
        $forums = ForumModel::getForums();
        $allThreads = ThreadModel::getHomeThreadsWithFilter($page, $order, $keyword);
        $total = ThreadModel::getHomeThreadCount($keyword);
        $pages = (int)ceil($total / 20);

        $threads = [];
        $collapsedThreads = [];
        $collapsedTotal = 0;
        
        foreach ($allThreads as $thread) {
            if (!empty($collapsedFids) && in_array($thread['fid'], $collapsedFids)) {
                $collapsedThreads[] = $thread;
                $collapsedTotal++;
            } else {
                $threads[] = $thread;
            }
        }

        $users = [];
        if (!empty($allThreads)) {
            $uids = array_unique(array_column($allThreads, 'uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        $orderOptions = [
            ['value' => 'tid', 'label' => '最新发布'],
            ['value' => 'reply_time', 'label' => '最后回复'],
            ['value' => 'reply_num', 'label' => '回复数'],
            ['value' => 'view_num', 'label' => '查看数']
        ];

        $userStats = [];
        $currentUser = Session::getUser();
        if ($currentUser) {
            $userStats = [
                'thread_count' => (int)($currentUser['thread_num'] ?? 0),
                'post_count' => (int)($currentUser['reply_num'] ?? 0),
                'fav_count' => (int)($currentUser['fav_num'] ?? 0),
                'credit' => (int)($currentUser['credit'] ?? 0),
                'inbox_num' => (int)($currentUser['inbox_num'] ?? 0),
                'outbox_num' => (int)($currentUser['outbox_num'] ?? 0),
                'notify_num' => (int)($currentUser['notify_num'] ?? 0),
                'signed_today' => CreditModel::hasSignedToday((int)$currentUser['uid']),
            ];
        }

        $noticeThreads = [];
        $noticeFid = (int)SettingModel::get('notice_forum_fid', '0');
        if ($noticeFid > 0) {
            $noticeThreads = ThreadModel::getThreads($noticeFid, 1, 'dateline', '');
            $noticeThreads = array_slice($noticeThreads, 0, 5);
        }

        $onlineCount = self::getOnlineCount();

        $hotForums = ForumModel::getHotForums(10);

        $modStats = [];
        $canManage = false;
        if ($currentUser) {
            $canManage = Permission::isAdmin();
            if ($canManage) {
                $modStats = [
                    'pending_threads' => ThreadModel::getPendingApproveCount(),
                    'pending_posts' => PostModel::getPendingApproveCount(),
                    'pending_reports' => self::getPendingReportCount(),
                ];
            }
        }

        $collapsedForums = [];
        if (!empty($collapsedFids)) {
            $collapsedForums = ForumModel::getForumsByIds($collapsedFids);
        }

        Template::set('title', 'XForum');
        Template::set('forums', $forums);
        Template::set('threads', $threads);
        Template::set('collapsedThreads', $collapsedThreads);
        Template::set('collapsedTotal', $collapsedTotal);
        Template::set('collapsedForums', $collapsedForums);
        Template::set('users', $users);
        Template::set('user', $currentUser);
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
        Template::display('home/index');
    }

    private static function getPendingReportCount(): int {
        return DataModel::getInt('pending_reports');
    }

    private static function getOnlineCount(): int {
        return SessionModel::getOnlineCount();
    }
}
?>
