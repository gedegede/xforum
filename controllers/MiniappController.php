<?php
declare(strict_types=1);

namespace Controllers;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Permission;
use Lib\Request;
use Lib\Response;
use Lib\Session;
use Lib\CsrfHelper;
use Models\FavModel;
use Models\ForumModel;
use Models\MemberModel;
use Models\PostModel;
use Models\RateModel;
use Models\SettingModel;
use Models\ThreadModel;
use Models\UsergroupModel;
use Models\CreditModel;
use Models\NotifyModel;
use Models\PmModel;

class MiniappController {
    public static function index(): void {
        self::home();
    }

    public static function home(): void {
        $page = max(1, Request::getInt('page', 1));
        $pageSize = self::pageSize(20, 40);
        $order = 'tid';
        $keyword = Request::getString('keyword');
        if ($keyword !== '' && !Permission::canSearch()) {
            Response::error('无权限搜索', 403);
        }

        $threads = ThreadModel::getHomeThreadsWithFilter($page, $order, $keyword, $pageSize);
        $total = ThreadModel::getHomeThreadCount($keyword);

        Response::json([
            'success' => true,
            'site' => self::siteInfo(),
            'user' => self::formatCurrentUser(),
            'threads' => self::formatThreads($threads),
            'forums' => self::formatForumsByThreadList($threads),
            'page' => $page,
            'pages' => (int)ceil($total / $pageSize),
            'total' => $total,
        ]);
    }

    public static function forums(): void {
        $forums = array_values(array_filter(ForumModel::getForumsFlat(), static function(array $forum): bool {
            return Permission::canViewForum((int)($forum['fid'] ?? 0));
        }));

        Response::json([
            'success' => true,
            'site' => self::siteInfo(),
            'forums' => array_map([self::class, 'formatForum'], $forums),
        ]);
    }

    public static function threads(): void {
        $fid = Request::getInt('fid');
        $page = max(1, Request::getInt('page', 1));
        $pageSize = self::pageSize(20, 40);
        $order = 'tid';
        $keyword = Request::getString('keyword');
        if ($fid <= 0) {
            Response::error('版块不存在', 404);
        }
        $forum = ForumModel::get($fid);
        if (!$forum || !Permission::canViewForum($fid)) {
            Response::error('无权访问版块', 403);
        }
        if ($keyword !== '' && !Permission::canSearch()) {
            Response::error('无权限搜索', 403);
        }

        $threads = ThreadModel::getThreads($fid, $page, $order, $keyword, $pageSize);
        $total = $keyword === '' ? (int)($forum['thread_num'] ?? 0) : ThreadModel::getThreadCount($fid, $keyword);

        Response::json([
            'success' => true,
            'forum' => self::formatForum($forum),
            'threads' => self::formatThreads($threads),
            'users' => self::formatUsersByThreadList($threads),
            'page' => $page,
            'pages' => (int)ceil($total / $pageSize),
            'total' => $total,
            'can_post' => Permission::canPostThread($fid),
            'can_reply' => Permission::canReplyThread($fid),
        ]);
    }

    public static function thread(): void {
        $tid = Request::getInt('tid');
        $page = max(1, Request::getInt('page', 1));
        $pageSize = self::pageSize((int)SettingModel::get('posts_per_page', '20'), 50);
        $thread = ThreadModel::get($tid);
        if (!$thread) {
            Response::error('主题不存在', 404);
        }

        $fid = (int)$thread['fid'];
        if (!Permission::canViewForum($fid)) {
            Response::error('主题不存在或无权访问', 404);
        }

        $forum = ForumModel::get($fid);
        $posts = PostModel::getPosts($tid, $page, false, $pageSize);
        $total = PostModel::getPostCount($tid);
        ThreadModel::incrementView($tid);

        $uids = array_unique(array_merge([(int)$thread['uid']], array_map('intval', array_column($posts, 'uid'))));
        $quoteUids = array_filter(array_unique(array_map('intval', array_column($posts, 'quote_uid'))));
        $users = MemberModel::getMembersByUids(array_unique(array_merge($uids, $quoteUids)));
        $ratePids = self::collectRatePids($posts);
        $ratedPids = [];
        if (Session::isLoggedIn() && !empty($ratePids)) {
            $ratedPids = RateModel::getRatedPids(Session::getUid(), $ratePids);
        }

        Response::json([
            'success' => true,
            'thread' => self::formatThread($thread),
            'forum' => $forum ? self::formatForum($forum) : null,
            'posts' => self::formatPosts($posts, $users, $ratedPids, $page, $pageSize),
            'users' => self::formatUsers($users),
            'page' => $page,
            'pages' => (int)ceil($total / $pageSize),
            'total' => $total,
            'can_reply' => Permission::canReplyThread($fid),
            'can_favorite' => Permission::canFavorite(),
            'favorited' => self::isThreadFavorited($thread, $tid),
        ]);
    }

    public static function me(): void {
        Response::json([
            'success' => true,
            'user' => self::formatCurrentUser(true),
            'permissions' => [
                'search' => Permission::canSearch(),
                'favorite' => Permission::canFavorite(),
                'rate' => Permission::canRate(),
                'pm' => Permission::canSendPm(),
                'admin' => Permission::isAdmin(),
            ],
        ]);
    }

    public static function csrf(): void {
        Response::json([
            'success' => true,
            'csrf_token' => CsrfHelper::generate(),
        ]);
    }

    public static function signin(): void {
        Permission::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        Response::json(CreditModel::signin(Session::getUid()));
    }

    public static function favorite(): void {
        Permission::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        if (!Permission::canFavorite()) {
            Response::error('无权限收藏', 403);
        }

        $tid = Request::postInt('tid');
        $thread = ThreadModel::get($tid);
        if (!$thread || !Permission::canViewForum((int)$thread['fid'])) {
            Response::error('主题不存在或无权限访问', 404);
        }

        $isFavorited = FavModel::isFavorite(Session::getUid(), $tid);
        if ($isFavorited) {
            FavModel::removeFavorite(Session::getUid(), $tid);
        } else {
            FavModel::addFavorite(Session::getUid(), $tid);
        }

        Response::json(['success' => true, 'favorited' => !$isFavorited]);
    }

    public static function rate(): void {
        Permission::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        if (!Permission::canRate()) {
            Response::error('无权限点赞', 403);
        }

        $pid = Request::postInt('pid');
        $post = PostModel::get($pid);
        if (!$post) {
            Response::error('帖子不存在', 404);
        }
        if (!Permission::canViewForum((int)$post['fid']) || !Permission::canViewPost($post)) {
            Response::error('无权限访问', 403);
        }

        $rated = RateModel::isRated(Session::getUid(), $pid);
        if ($rated) {
            RateModel::removeRate(Session::getUid(), $pid);
        } else {
            RateModel::addRate(Session::getUid(), $pid);
        }
        $post = PostModel::get($pid) ?? $post;
        Response::json([
            'success' => true,
            'rated' => !$rated,
            'rate_num' => (int)($post['rate_num'] ?? 0),
        ]);
    }

    public static function notifies(): void {
        Permission::requireLogin();
        $page = max(1, Request::getInt('page', 1));
        $rows = NotifyModel::getNotifies(Session::getUid(), $page);
        $total = NotifyModel::getNotifyCount(Session::getUid());
        $fromUids = array_filter(array_unique(array_map('intval', array_column($rows, 'from_uid'))));
        $users = MemberModel::getMembersByUids($fromUids);
        NotifyModel::markAsRead(Session::getUid());

        Response::json([
            'success' => true,
            'notifies' => array_map(static function(array $row) use ($users): array {
                $fromUid = (int)($row['from_uid'] ?? 0);
                return [
                    'did' => (int)($row['did'] ?? 0),
                    'tid' => (int)($row['tid'] ?? 0),
                    'pid' => (int)($row['pid'] ?? 0),
                    'from_uid' => $fromUid,
                    'from_user' => self::formatUser($users[$fromUid] ?? []),
                    'message' => (string)($row['message'] ?? ''),
                    'dateline' => (int)($row['dateline'] ?? 0),
                    'status' => (int)($row['status'] ?? 0),
                ];
            }, $rows),
            'page' => $page,
            'pages' => (int)ceil($total / 20),
            'total' => $total,
        ]);
    }

    public static function pms(): void {
        Permission::requireLogin();
        $page = max(1, Request::getInt('page', 1));
        $rows = PmModel::getConversations(Session::getUid(), $page);
        $total = PmModel::getConversationCount(Session::getUid());
        $uids = array_filter(array_unique(array_map('intval', array_column($rows, 'partner_uid'))));
        $users = MemberModel::getMembersByUids($uids);

        Response::json([
            'success' => true,
            'conversations' => array_map(static function(array $row) use ($users): array {
                $partnerUid = (int)($row['partner_uid'] ?? 0);
                return [
                    'pmid' => (int)($row['pmid'] ?? 0),
                    'partner_uid' => $partnerUid,
                    'partner' => self::formatUser($users[$partnerUid] ?? []),
                    'content' => (string)($row['content'] ?? ''),
                    'dateline' => (int)($row['dateline'] ?? 0),
                    'unread_num' => (int)($row['unread_num'] ?? 0),
                ];
            }, $rows),
            'page' => $page,
            'pages' => (int)ceil($total / PmModel::PAGE_SIZE),
            'total' => $total,
        ]);
    }

    private static function siteInfo(): array {
        return [
            'name' => SettingModel::get('site_name', 'XForum'),
            'desc' => SettingModel::get('site_desc', ''),
        ];
    }

    private static function pageSize(int $default, int $max): int {
        $pageSize = Request::getInt('page_size', $default);
        return min($max, max(1, $pageSize));
    }

    private static function order(string $order, array $allowed): string {
        return in_array($order, $allowed, true) ? $order : $allowed[0];
    }

    private static function formatThreads(array $threads): array {
        return array_values(array_map([self::class, 'formatThread'], $threads));
    }

    private static function formatThread(array $thread): array {
        if (!Permission::canViewForum((int)($thread['fid'] ?? 0))) {
            $thread['subject'] = '无权浏览';
        }

        return [
            'tid' => (int)($thread['tid'] ?? 0),
            'fid' => (int)($thread['fid'] ?? 0),
            'uid' => (int)($thread['uid'] ?? 0),
            'subject' => (string)($thread['subject'] ?? ''),
            'reply_num' => (int)($thread['reply_num'] ?? 0),
            'view_num' => (int)($thread['view_num'] ?? 0),
            'fav_num' => (int)($thread['fav_num'] ?? 0),
            'dateline' => (int)($thread['dateline'] ?? 0),
            'reply_time' => (int)($thread['reply_time'] ?? 0),
        ];
    }

    private static function formatForum(array $forum): array {
        return [
            'fid' => (int)($forum['fid'] ?? 0),
            'up_fid' => (int)($forum['up_fid'] ?? 0),
            'name' => (string)($forum['name'] ?? ''),
            'brief' => (string)($forum['brief'] ?? ''),
            'thread_num' => (int)($forum['thread_num'] ?? 0),
            'reply_num' => (int)($forum['reply_num'] ?? 0),
            'today_num' => (int)($forum['today_num'] ?? 0),
            'last_tid' => (int)($forum['last_tid'] ?? 0),
            'depth' => (int)($forum['depth'] ?? 0),
            'parent_name' => (string)($forum['parent_name'] ?? ''),
        ];
    }

    private static function formatPosts(array $posts, array $users, array $ratedPids, int $page, int $pageSize): array {
        $result = [];
        foreach ($posts as $index => $post) {
            $pid = (int)($post['pid'] ?? 0);
            $uid = (int)($post['uid'] ?? 0);
            $result[] = [
                'pid' => $pid,
                'tid' => (int)($post['tid'] ?? 0),
                'fid' => (int)($post['fid'] ?? 0),
                'uid' => $uid,
                'author' => self::formatUser($users[$uid] ?? []),
                'message' => (string)($post['message'] ?? ''),
                'dateline' => (int)($post['dateline'] ?? 0),
                'is_thread' => (int)($post['is_thread'] ?? 0),
                'quote_pid' => (int)($post['quote_pid'] ?? 0),
                'quote_uid' => (int)($post['quote_uid'] ?? 0),
                'quote_floor' => (int)($post['quote_floor'] ?? 0),
                'rate_num' => (int)($post['rate_num'] ?? 0),
                'rated' => isset($ratedPids[$pid]),
                'floor' => (($page - 1) * $pageSize) + $index + 1,
            ];
        }
        return $result;
    }

    private static function collectRatePids(array $posts): array {
        $pids = [];
        foreach ($posts as $post) {
            if ((int)($post['rate_num'] ?? 0) > 0) {
                $pids[] = (int)($post['pid'] ?? 0);
            }
        }
        return array_values(array_filter(array_unique($pids)));
    }

    private static function isThreadFavorited(array $thread, int $tid): bool {
        if (!Session::isLoggedIn() || (int)($thread['fav_num'] ?? 0) <= 0) {
            return false;
        }
        return FavModel::isFavorite(Session::getUid(), $tid);
    }

    private static function formatForumsByThreadList(array $threads): array {
        $fids = [];
        foreach ($threads as $thread) {
            if (Permission::canViewForum((int)($thread['fid'] ?? 0))) {
                $fids[] = (int)$thread['fid'];
            }
        }
        $fids = array_values(array_unique($fids));
        return array_map([self::class, 'formatForum'], ForumModel::getForumsByIds($fids));
    }

    private static function formatUsersByThreadList(array $threads): array {
        $uids = array_unique(array_map('intval', array_column($threads, 'uid')));
        return self::formatUsers(MemberModel::getMembersByUids($uids));
    }

    private static function formatUsers(array $users): array {
        $result = [];
        foreach ($users as $uid => $user) {
            $result[(int)$uid] = self::formatUser($user);
        }
        return $result;
    }

    private static function formatCurrentUser(bool $withGroup = false): ?array {
        $user = Session::getUser();
        if (!$user) {
            return null;
        }
        $data = self::formatUser($user, true);
        if ($withGroup) {
            $group = UsergroupModel::get((int)($user['gid'] ?? 0));
            $data['group'] = $group ? [
                'gid' => (int)($group['gid'] ?? 0),
                'title' => (string)($group['title'] ?? ''),
            ] : null;
        }
        return $data;
    }

    private static function formatUser(array $user, bool $withStats = false): array {
        if (empty($user)) {
            return [];
        }
        $data = [
            'uid' => (int)($user['uid'] ?? 0),
            'username' => (string)($user['username'] ?? ''),
            'avatar' => (string)($user['avatar'] ?? ''),
        ];
        if ($withStats) {
            $data += [
                'thread_num' => (int)($user['thread_num'] ?? 0),
                'reply_num' => (int)($user['reply_num'] ?? 0),
                'fav_num' => (int)($user['fav_num'] ?? 0),
                'credit' => (int)($user['credit'] ?? 0),
                'notify_num' => (int)($user['notify_num'] ?? 0),
                'inbox_num' => (int)($user['inbox_num'] ?? 0),
            ];
        }
        return $data;
    }
}
?>
