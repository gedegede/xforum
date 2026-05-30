<?php
declare(strict_types=1);

namespace Controllers;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\Template;
use Lib\Session;
use Lib\Response;
use Lib\Request;
use Lib\PostHelper;
use Lib\Permission;
use Lib\ThreadHelper;
use Models\ThreadModel;
use Models\PostModel;
use Models\ForumModel;
use Models\MemberModel;
use Models\ModLogModel;
use Models\NotifyModel;
use Models\FavModel;
use Models\RateModel;
use Models\SettingModel;
use Models\UsergroupModel;
use Models\AuditModel;
use Models\SessionModel;
use Models\CreditModel;

class ThreadController {
    public static function index(int $tid, int $pid = 0): void {
        Template::clear();
        if (!$tid) {
            Response::redirect('index.php');
        }

        $thread = ThreadModel::get($tid);
        if (!$thread) {
            Response::redirect('index.php');
        }

        $fid = (int)$thread['fid'];
        $forum = ForumModel::get($fid);
        $canAuditForum = Permission::canAuditForum($fid);
        if (!Permission::canViewForum($fid)) {
            Response::redirect('index.php');
        }

        $user = Session::getUser();
        SessionModel::updateOnline(
            $user['uid'] ?? 0,
            $user['gid'] ?? 0,
            $user['invisible'] ?? 0,
            $fid,
            $tid
        );

        $targetPid = $pid > 0 ? $pid : Request::getInt('pid', 0);
        $page = Request::getInt('page', 1);
        $postsPerPage = (int)SettingModel::get('posts_per_page', '20');
        if ($targetPid > 0) {
            $targetPost = PostModel::get($targetPid);
            if ($targetPost && (int)$targetPost['tid'] === $tid) {
                $targetPage = PostModel::getPostPage($targetPid, $postsPerPage);
                if ($targetPage > 0) {
                    $page = $targetPage;
                }
            } else {
                $targetPid = 0;
            }
        }
        $posts = self::sortCurrentPagePostsByRate(PostModel::getPosts($tid, $page, $canAuditForum, $postsPerPage), $page);
        $total = PostModel::getPostCount($tid, $canAuditForum);
        
        ThreadModel::incrementView($tid);
        $pages = (int)ceil($total / $postsPerPage);

        $uids = array_unique(array_merge([$thread['uid']], array_column($posts, 'uid')));
        
        $quoteUids = array_filter(array_unique(array_column($posts, 'quote_uid')));
        if (!empty($quoteUids)) {
            $uids = array_unique(array_merge($uids, $quoteUids));
        }
        
        $users = MemberModel::getMembersByUids($uids);

        $isFavorited = false;
        if (Session::isLoggedIn() && (int)($thread['fav_num'] ?? 0) > 0) {
            $isFavorited = FavModel::isFavorite(Session::getUid(), $tid);
        }
        $ratePids = self::collectRatePids($posts);
        $ratedPids = [];
        if (Session::isLoggedIn() && !empty($ratePids)) {
            $ratedPids = RateModel::getRatedPids(Session::getUid(), $ratePids);
        }

        $displayThread = ThreadHelper::maskUnauthorizedSubject($thread);
        Template::set('title', $displayThread['subject']);
        Template::set('thread', $displayThread);
        Template::set('forum', $forum);
        Template::set('posts', $posts);
        Template::set('users', $users);
        Template::set('page', $page);
        Template::set('pages', $pages);
        Template::set('targetPid', $targetPid);
        Template::set('user', Session::getUser());
        Template::set('isFavorited', $isFavorited);
        Template::set('ratedPids', $ratedPids);
        Template::set('isModerator', $canAuditForum);
        Template::set('canAuditForum', $canAuditForum);
        Template::set('hotThreads', ThreadHelper::maskUnauthorizedSubjects(ThreadModel::getHotThreadsByFid($fid, 5, $tid)));

        $creditChange = Session::get('credit_change');
        if ($creditChange) {
            Template::set('creditChange', $creditChange);
            Session::delete('credit_change');
        }

        Template::display('thread/index');
    }

    private static function sortCurrentPagePostsByRate(array $posts, int $page): array {
        $indexedPosts = [];
        $postsPerPage = (int)\Models\SettingModel::get('posts_per_page', '20');
        $floorOffset = (max(1, $page) - 1) * $postsPerPage;
        foreach ($posts as $index => $post) {
            $post['_floor'] = $floorOffset + $index + 1;
            $indexedPosts[] = [
                'index' => $index,
                'post' => $post,
            ];
        }

        if (count($indexedPosts) <= 1) {
            return array_column($indexedPosts, 'post');
        }

        usort($indexedPosts, static function (array $a, array $b): int {
            $postA = $a['post'];
            $postB = $b['post'];
            $isThreadA = (int)($postA['is_thread'] ?? 0) === 1;
            $isThreadB = (int)($postB['is_thread'] ?? 0) === 1;

            if ($isThreadA !== $isThreadB) {
                return $isThreadA ? -1 : 1;
            }

            if (!$isThreadA) {
                $rateCompare = (int)($postB['rate_num'] ?? 0) <=> (int)($postA['rate_num'] ?? 0);
                if ($rateCompare !== 0) {
                    return $rateCompare;
                }
            }

            return $a['index'] <=> $b['index'];
        });

        return array_column($indexedPosts, 'post');
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

    public static function create(?int $fid = null): void {
        Template::clear();
        Permission::requireLogin();

        if (!$fid) {
            Response::redirect('index.php?c=forum&a=index&from=create');
        }

        $forum = ForumModel::get($fid);
        if (!$forum) {
            Response::redirect('index.php');
        }

        $error = '';
        $user = Session::getUser();
        if (!Permission::canPostThread($fid)) {
            $error = '无权限发布主题';
        }
        if (empty($error) && AuditModel::hasPendingByUid('thread', (int)$user['uid'])) {
            $error = '有待审核的主题，不能发布新主题';
        }
        
        $newbieWaitHours = (int)SettingModel::get('newbie_wait_hours', '0');
        if ($newbieWaitHours > 0) {
            $regTime = (int)($user['reg_date'] ?? 0);
            if (time() - $regTime < $newbieWaitHours * 3600) {
                $remaining = $newbieWaitHours * 3600 - (time() - $regTime);
                $error = '新用户需要等待 ' . self::formatTime($remaining) . ' 后才能发帖';
            }
        }

        $postInterval = (int)SettingModel::get('post_interval', '30');
        if (empty($error) && $postInterval > 0) {
            $lastPostTime = Session::get('last_post_time_' . $user['uid'], 0);
            if (time() - $lastPostTime < $postInterval) {
                $remaining = $postInterval - (time() - $lastPostTime);
                $error = '发帖过于频繁，请等待 ' . $remaining . ' 秒';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = Request::postString('subject');
            $message = Request::postString('message');

            if (empty($subject) || empty($message)) {
                $error = '请填写标题和内容';
            } elseif (!empty($error)) {
                // 保留之前的错误
            } else {
                $blockKeywords = SettingModel::getBlockKeywords();
                foreach ($blockKeywords as $keyword) {
                    if (stripos($subject, $keyword) !== false || stripos($message, $keyword) !== false) {
                        $error = '内容包含禁止发布的关键词';
                        break;
                    }
                }

                if (!$error) {
                    $needApprove = UsergroupModel::threadNeedApprove((int)Session::getUser()['gid']);
                    $approveKeywords = SettingModel::getApproveKeywords();
                    foreach ($approveKeywords as $keyword) {
                        if (stripos($subject, $keyword) !== false || stripos($message, $keyword) !== false) {
                            $needApprove = true;
                            break;
                        }
                    }

                    $creditChange = 0;
                    $tid = 0;
                    $pid = 0;
                    $auditDid = 0;
                    $inTransaction = false;

                    if (!$error) {
                        try {
                            Database::beginTransaction();
                            $inTransaction = true;

                            if ($needApprove) {
                                $auditDid = AuditModel::create('thread', 0, 0, [
                                    'fid' => $fid,
                                    'uid' => Session::getUid(),
                                    'subject' => $subject,
                                    'message' => $message,
                                    'ip' => self::getClientIp(),
                                ], $fid, Session::getUid());
                            } else {
                                $published = self::publishThreadContent([
                                    'fid' => $fid,
                                    'uid' => Session::getUid(),
                                    'subject' => $subject,
                                    'message' => $message,
                                    'ip' => self::getClientIp(),
                                ]);
                                $tid = (int)$published['tid'];
                                $pid = (int)$published['pid'];
                                $creditChange = (int)($published['credit_change'] ?? 0);
                            }

                            Session::set('last_post_time_' . $user['uid'], time());
                            if ($tid > 0) {
                                SessionModel::updateOnline($user['uid'], $user['gid'], $user['invisible'], $fid, $tid);
                            }

                            Database::commit();
                            $inTransaction = false;
                        } catch (\Throwable $e) {
                            if ($inTransaction) {
                                Database::rollBack();
                            }
                            $error = $e instanceof \RuntimeException ? $e->getMessage() : '发布失败，请稍后重试';
                        }

                        if (!$error && $needApprove) {
                            if (Response::isAjaxRequest()) {
                                Response::json([
                                    'success' => true,
                                    'message' => '主题已提交，等待审核',
                                    'pending' => true,
                                    'did' => $auditDid,
                                ]);
                            }
                            Template::set('title', '发布成功');
                            Template::set('message', '主题已提交，等待审核');
                            Template::set('user', Session::getUser());
                            Template::display('thread/pending');
                            exit;
                        }

                        if (!$error) {
                            if ($creditChange !== 0) {
                                Session::set('credit_change', $creditChange);
                            }

                            if (Response::isAjaxRequest()) {
                                Response::json([
                                    'success' => true,
                                    'message' => '发布成功',
                                    'tid' => $tid,
                                    'pid' => $pid,
                                    'credit_change' => $creditChange,
                                ]);
                            }

                            Response::redirect("index.php?c=thread&a=index&tid={$tid}");
                        }
                    }
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Response::isAjaxRequest() && $error !== '') {
            Response::error($error, 400);
        }

        Template::set('title', '发布新帖');
        Template::set('forum', $forum);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('thread/create');
    }

    public static function reply(int $tid): void {
        Template::clear();
        Permission::requireLogin();

        $thread = ThreadModel::get($tid);
        if (!$thread) {
            if (Response::isAjaxRequest()) {
                Response::error('主题不存在');
            }
            Response::redirect('index.php');
        }

        $user = Session::getUser();
        $errorMsg = '';
        if (!Permission::canReplyThread((int)$thread['fid'])) {
            $errorMsg = '无权限回复主题';
        }
        if (empty($errorMsg) && AuditModel::hasPendingByUid('post', (int)$user['uid'])) {
            $errorMsg = '有待审核的回复，不能发布新回复';
        }
        
        $newbieWaitHours = (int)SettingModel::get('newbie_wait_hours', '0');
        if ($newbieWaitHours > 0) {
            $regTime = (int)($user['reg_date'] ?? 0);
            if (time() - $regTime < $newbieWaitHours * 3600) {
                $remaining = $newbieWaitHours * 3600 - (time() - $regTime);
                $errorMsg = '新用户需要等待 ' . self::formatTime($remaining) . ' 后才能回帖';
            }
        }

        $postInterval = (int)SettingModel::get('post_interval', '30');
        if (empty($errorMsg) && $postInterval > 0) {
            $lastPostTime = Session::get('last_post_time_' . $user['uid'], 0);
            if (time() - $lastPostTime < $postInterval) {
                $remaining = $postInterval - (time() - $lastPostTime);
                $errorMsg = '发帖过于频繁，请等待 ' . $remaining . ' 秒';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = Request::postString('message');
            $quotePid = Request::postInt('quote_pid');
            $quoteUid = Request::postInt('quote_uid');

            if (!empty($errorMsg)) {
                if (Response::isAjaxRequest()) {
                    Response::error($errorMsg);
                }
                Template::set('title', '回复帖子');
                Template::set('thread', $thread);
                Template::set('error', $errorMsg);
                Template::set('user', $user);
                Template::display('thread/reply');
                exit;
            }

            if (empty($message)) {
                $errorMsg = '请填写回复内容';
                if (Response::isAjaxRequest()) {
                    Response::error($errorMsg);
                }
                Template::set('title', '回复帖子');
                Template::set('thread', $thread);
                Template::set('error', $errorMsg);
                Template::set('user', $user);
                Template::display('thread/reply');
                exit;
            }

            $blockKeywords = SettingModel::getBlockKeywords();
            foreach ($blockKeywords as $keyword) {
                if (stripos($message, $keyword) !== false) {
                    $errorMsg = '内容包含禁止发布的关键词';
                    if (Response::isAjaxRequest()) {
                        Response::error($errorMsg);
                    }
                    Template::set('title', '回复帖子');
                    Template::set('thread', $thread);
                    Template::set('error', $errorMsg);
                    Template::set('user', Session::getUser());
                    Template::display('thread/reply');
                    exit;
                }
            }

            $needApprove = UsergroupModel::postNeedApprove((int)Session::getUser()['gid']);
            $approveKeywords = SettingModel::getApproveKeywords();
            foreach ($approveKeywords as $keyword) {
                if (stripos($message, $keyword) !== false) {
                    $needApprove = true;
                    break;
                }
            }

            $quoteFloor = 0;
            if ($quotePid > 0 && $quoteUid > 0) {
                $quotePost = PostModel::get($quotePid);
                if ($quotePost && (int)$quotePost['tid'] === $tid) {
                    $quoteFloor = PostModel::getPostFloor($quotePid);
                } else {
                    $quotePid = 0;
                    $quoteUid = 0;
                }
            }

            $pid = 0;
            $auditDid = 0;
            $errorMsg = '';
            $inTransaction = false;
            $creditChange = 0;
            try {
                Database::beginTransaction();
                $inTransaction = true;

                if ($needApprove) {
                    $auditDid = AuditModel::create('post', $tid, 0, [
                        'fid' => (int)$thread['fid'],
                        'tid' => $tid,
                        'uid' => Session::getUid(),
                        'message' => $message,
                        'quote_pid' => $quotePid,
                        'quote_uid' => $quoteUid,
                        'quote_floor' => $quoteFloor,
                        'ip' => self::getClientIp(),
                    ], (int)$thread['fid'], Session::getUid());
                } else {
                    $published = self::publishReplyContent($thread, [
                        'tid' => $tid,
                        'uid' => Session::getUid(),
                        'message' => $message,
                        'quote_pid' => $quotePid,
                        'quote_uid' => $quoteUid,
                        'quote_floor' => $quoteFloor,
                        'ip' => self::getClientIp(),
                    ]);
                    $pid = (int)$published['pid'];
                    $creditChange = (int)($published['credit_change'] ?? 0);
                }

                Session::set('last_post_time_' . $user['uid'], time());

                Database::commit();
                $inTransaction = false;
            } catch (\Throwable $e) {
                if ($inTransaction) {
                    Database::rollBack();
                }
                $errorMsg = $e instanceof \RuntimeException ? $e->getMessage() : '回复失败，请稍后重试';
            }

            if ($errorMsg !== '') {
                if (Response::isAjaxRequest()) {
                    Response::error($errorMsg);
                }
                Template::set('title', '回复帖子');
                Template::set('thread', $thread);
                Template::set('error', $errorMsg);
                Template::set('user', Session::getUser());
                Template::display('thread/reply');
                exit;
            }

            if ($needApprove) {
                if (Response::isAjaxRequest()) {
                    Response::json([
                        'success' => true,
                        'message' => '回复已提交，等待审核',
                        'pending' => true,
                        'did' => $auditDid,
                    ]);
                }
                Template::set('title', '回复成功');
                Template::set('message', '回复已提交，等待审核');
                Template::set('type', 'post');
                Template::set('thread', $thread);
                Template::set('user', Session::getUser());
                Template::display('thread/pending');
                exit;
            }

            SessionModel::updateOnline($user['uid'], $user['gid'], $user['invisible'], $thread['fid'], $tid);

            if (Response::isAjaxRequest()) {
                $currentUser = Session::getUser();
                $postIndex = (int)($thread['reply_num'] ?? 0) + 1;

                $newPost = [
                    'pid' => $pid,
                    'fid' => $thread['fid'],
                    'tid' => $tid,
                    'uid' => Session::getUid(),
                    'message' => $message,
                    'dateline' => time(),
                    'is_thread' => 0,
                    'quote_pid' => $quotePid,
                    'quote_uid' => $quoteUid,
                    'quote_floor' => $quoteFloor,
                ];

                $users = [
                    Session::getUid() => $currentUser,
                ];

                if ($quoteUid > 0) {
                    $quoteUser = MemberModel::get($quoteUid);
                    if ($quoteUser) {
                        $users[$quoteUid] = $quoteUser;
                    }
                }

                $isMod = Permission::isModerator((int)$thread['fid']);
                $html = PostHelper::renderPost($newPost, $users, $postIndex, false, $currentUser, $isMod);

                $response = [
                    'success' => true,
                    'message' => '回复成功',
                    'html' => $html,
                    'pid' => $pid,
                    'postIndex' => $postIndex,
                ];

                if ($creditChange !== 0) {
                    $response['credit_change'] = $creditChange;
                }

                Response::json($response);
            }

            Response::redirect("index.php?c=thread&a=index&tid={$tid}");
        }

        Template::set('title', '回复帖子');
        Template::set('thread', $thread);
        Template::set('error', '');
        Template::set('user', Session::getUser());
        Template::display('thread/reply');
    }

    public static function handleAtMentions(string $message, int $tid, int $pid, ?int $fromUid = null): void {
        preg_match_all('/@([^\s@,，:：;；]+)/u', $message, $matches);
        $fromUid = $fromUid ?? Session::getUid();
        $usernames = array_values(array_unique(array_filter(array_map('trim', $matches[1] ?? []))));

        if (empty($usernames)) {
            return;
        }

        $thread = ThreadModel::get($tid);
        $subject = (string)($thread['subject'] ?? '主题');
        foreach ($usernames as $username) {
            $member = MemberModel::getByUsername($username);
            if ($member && (int)$member['uid'] !== $fromUid) {
                NotifyModel::addNotify((int)$member['uid'], $fromUid, $tid, $pid, '在 ' . $subject . ' 中@了你');
            }
        }
    }

    public static function favorite(int $tid): void {
        Permission::requireLogin();
        self::requirePost();
        if (!Permission::canFavorite()) {
            Response::error('无权限收藏', 403);
        }

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

        if (Response::isAjaxRequest()) {
            Response::json([
                'success' => true,
                'favorited' => !$isFavorited,
            ]);
        }

        Response::redirect("index.php?c=thread&a=index&tid={$tid}");
    }

    public static function rate(int $pid): void {
        Permission::requireLogin();
        self::requirePost();
        if (!Permission::canRate()) {
            Response::error('无权限点赞', 403);
        }

        $post = PostModel::get($pid);
        if (!$post) {
            if (Response::isAjaxRequest()) {
                Response::error('帖子不存在');
            }
            Response::redirect('index.php');
        }

        if (!Permission::canViewForum((int)$post['fid']) || !Permission::canViewPost($post)) {
            if (Response::isAjaxRequest()) {
                Response::error('无权限访问', 403);
            }
            Response::redirect('index.php?c=thread&a=index&tid=' . $post['tid']);
        }

        $uid = Session::getUid();
        $isRated = RateModel::isRated($uid, $pid);
        if ($isRated) {
            RateModel::removeRate($uid, $pid);
        } else {
            RateModel::addRate($uid, $pid);
        }

        $post = PostModel::get($pid) ?? $post;
        if (Response::isAjaxRequest()) {
            Response::json([
                'success' => true,
                'rated' => !$isRated,
                'rate_num' => (int)($post['rate_num'] ?? 0),
            ]);
        }

        Response::redirect("index.php?c=thread&a=index&tid={$post['tid']}&pid={$pid}");
    }

    public static function creditPost(int $pid): void {
        Permission::requireLogin();
        self::requirePost();

        $post = PostModel::get($pid);
        if (!$post) {
            Response::error('帖子不存在');
        }
        if (!Permission::canViewForum((int)$post['fid']) || !Permission::canViewPost($post) || !Permission::canCreditPost($post)) {
            Response::error('无权限评分', 403);
        }
        if ((int)$post['uid'] === Session::getUid()) {
            Response::error('不能给自己评分');
        }

        $credit = Request::postInt('credit');
        $reason = Request::postString('reason');
        if ($credit === 0) {
            Response::error('请填写金币数量');
        }
        if ($reason === '') {
            Response::error('请填写评分理由');
        }

        $operator = Session::getUser();
        $thread = ThreadModel::get((int)$post['tid']);
        $url = "index.php?c=thread&a=index&tid={$post['tid']}&pid={$pid}";
        $message = '帖子评分：' . ($thread['subject'] ?? '') . " (PID: {$pid})";

        Database::beginTransaction();
        try {
            $did = CreditModel::changeByModerator((int)$post['uid'], $credit, $message . ' ' . $reason, $url);
            if ($did <= 0) {
                throw new \RuntimeException('评分失败');
            }

            $creditLog = [
                'uid' => Session::getUid(),
                'username' => (string)($operator['username'] ?? ''),
                'credit' => $credit,
                'reason' => $reason,
                'time' => time(),
            ];
            PostModel::addCreditLog($pid, $creditLog);
            self::logPostAction(
                'post_credit',
                $post,
                "帖子评分: TID: {$post['tid']}, PID: {$pid}, 金币: {$credit}, 理由: {$reason}",
                ''
            );
            if ((int)$post['uid'] !== Session::getUid()) {
                NotifyModel::addNotify(
                    (int)$post['uid'],
                    Session::getUid(),
                    (int)$post['tid'],
                    $pid,
                    '给你的帖子评分 ' . ($credit > 0 ? '+' . $credit : (string)$credit) . ' 金币，理由：' . $reason
                );
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            Response::error($e instanceof \RuntimeException ? $e->getMessage() : '评分失败');
        }

        Response::json(['success' => true, 'message' => '评分成功']);
    }

    public static function edit(int $pid): void {
        Template::clear();
        Permission::requireLogin();

        $post = PostModel::get($pid);
        if (!$post) {
            Response::redirect('index.php');
        }

        if (!Permission::canEditPost($post)) {
            Response::redirect('index.php?c=thread&a=index&tid=' . $post['tid']);
        }

        $thread = ThreadModel::get($post['tid']);
        $forum = ForumModel::get($post['fid']);
        $user = Session::getUser();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = Request::postString('message');

            if (empty($message)) {
                $error = '请填写内容';
            } else {
                PostModel::update($pid, ['message' => $message]);
                if ($thread) {
                    $isThreadPost = (int)($post['is_thread'] ?? 0) === 1;
                    self::logPostAction(
                        $isThreadPost ? 'thread_edit' : 'post_edit',
                        $post,
                        ($isThreadPost ? '编辑主题: ' . ($thread['subject'] ?? '') : '编辑帖子') . " (TID: {$post['tid']}, PID: {$pid})",
                        self::buildPostArchive($post)
                    );
                }
                Response::redirect("index.php?c=thread&a=index&tid={$post['tid']}");
            }
        }

        Template::set('title', '编辑帖子');
        Template::set('post', $post);
        Template::set('thread', $thread);
        Template::set('forum', $forum);
        Template::set('error', $error);
        Template::set('user', $user);
        Template::display('thread/edit');
    }

    public static function deletePost(int $pid): void {
        Permission::requireLogin();
        self::requirePost();

        $post = PostModel::get($pid);
        if (!$post) {
            if (Response::isAjaxRequest()) {
                Response::error('帖子不存在');
            }
            Response::redirect('index.php');
        }

        if (!Permission::canDeletePost($post)) {
            if (Response::isAjaxRequest()) {
                Response::error('没有权限删除');
            }
            Response::redirect('index.php?c=thread&a=index&tid=' . $post['tid']);
        }

        $tid = (int)$post['tid'];
        if ((int)($post['is_thread'] ?? 0) === 1) {
            self::deleteThreadOnly($tid);
            if (Response::isAjaxRequest()) {
                Response::json(['success' => true, 'message' => '主题已删除', 'redirect' => 'index.php?c=forum&a=index&fid=' . $post['fid']]);
            }
            Response::redirect('index.php?c=forum&a=index&fid=' . $post['fid']);
        }

        self::logPostAction(
            'post_delete',
            $post,
            "删除帖子: TID: {$tid}, PID: {$pid}",
            self::buildPostArchive($post)
        );
        PostModel::delete($pid);

        ThreadModel::decrementReplyNum($tid);
        MemberModel::decrementReplyNum((int)$post['uid']);
        ForumModel::decrementReplyNum((int)$post['fid']);
        if ((int)($post['dateline'] ?? 0) >= strtotime(date('Y-m-d'))) {
            ForumModel::decrementTodayNum((int)$post['fid']);
        }
        AuditModel::finishPendingByTarget('report', (int)$post['tid'], $pid, -1, Session::getUid());

        if (Response::isAjaxRequest()) {
            Response::json(['success' => true, 'message' => '删除成功']);
        }

        Response::redirect("index.php?c=thread&a=index&tid={$tid}");
    }

    private static function formatTime(int $seconds): string {
        if ($seconds < 60) {
            return $seconds . '秒';
        } elseif ($seconds < 3600) {
            return (int)($seconds / 60) . '分钟';
        } else {
            return (int)($seconds / 3600) . '小时';
        }
    }

    private static function getClientIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public static function report(int $pid): void {
        Permission::requireLogin();
        self::requirePost();
        if (!Permission::canReport()) {
            Response::error('无权限举报', 403);
        }

        $post = PostModel::get($pid);
        if (!$post) {
            Response::error('帖子不存在');
        }
        if (!Permission::canViewForum((int)$post['fid']) || !Permission::canViewPost($post)) {
            Response::error('无权限访问', 403);
        }

        $reason = Request::postString('reason');
        if (empty($reason)) {
            Response::error('请填写举报理由');
        }
        if (AuditModel::hasPending('report', (int)$post['tid'], $pid)) {
            Response::error('已经举报待处理，不需要重复举报');
        }

        $thread = ThreadModel::get($post['tid']);
        $creditRule = CreditModel::getRule(CreditModel::ACTION_THREAD_REPORT);
        $creditMessage = '举报主题：' . ($thread['subject'] ?? '未知主题');
        $creditUrl = "index.php?c=thread&a=index&tid={$post['tid']}&pid={$pid}";

        $inTransaction = false;
        try {
            Database::beginTransaction();
            $inTransaction = true;

            if ((int)$creditRule['credit'] < 0) {
                $creditChanged = CreditModel::apply(
                    CreditModel::ACTION_THREAD_REPORT,
                    Session::getUid(),
                    $creditMessage,
                    $creditUrl
                );
                if ($creditChanged === 0) {
                    throw new \RuntimeException(CreditModel::getInsufficientMessage(CreditModel::ACTION_THREAD_REPORT));
                }
            }

            AuditModel::create('report', (int)$post['tid'], $pid, [
                'report_uid' => Session::getUid(),
                'report_reason' => $reason,
            ], (int)$post['fid'], Session::getUid());
            if ((int)$creditRule['credit'] > 0) {
                CreditModel::apply(CreditModel::ACTION_THREAD_REPORT, Session::getUid(), $creditMessage, $creditUrl);
            }

            Database::commit();
            $inTransaction = false;
        } catch (\Throwable $e) {
            if ($inTransaction) {
                Database::rollBack();
            }
            $message = $e instanceof \RuntimeException ? $e->getMessage() : '举报失败，请稍后重试';
            Response::error($message);
        }

        Response::json(['success' => true, 'message' => '举报已提交']);
    }

    public static function approve(int $tid): void {
        self::requirePost();
        Response::redirect('index.php?c=admin&a=audits');
    }

    public static function approvePost(int $pid): void {
        self::requirePost();
        Response::redirect('index.php?c=admin&a=audits');
    }

    public static function auditThread(int $tid): void {
        self::requirePost();
        self::auditDenied();
    }

    public static function auditPost(int $pid): void {
        self::requirePost();
        self::auditDenied();
    }

    public static function publishThreadAudit(array $audit): array {
        $data = self::decodeAuditData($audit);
        $data['fid'] = (int)($data['fid'] ?? $audit['fid'] ?? 0);
        $data['uid'] = (int)($data['uid'] ?? $audit['uid'] ?? 0);
        $data['subject'] = trim((string)($data['subject'] ?? ''));
        $data['message'] = trim((string)($data['message'] ?? ''));
        if ((int)$data['fid'] <= 0 || (int)$data['uid'] <= 0 || $data['subject'] === '' || $data['message'] === '') {
            throw new \RuntimeException('审核数据不完整');
        }

        $published = self::publishThreadContent($data);
        NotifyModel::addNotify((int)$published['uid'], Session::getUid(), (int)$published['tid'], (int)$published['pid'], '《' . $published['subject'] . '》已审核发布');
        return $published;
    }

    public static function publishPostAudit(array $audit): array {
        $data = self::decodeAuditData($audit);
        $tid = (int)($data['tid'] ?? $audit['tid'] ?? 0);
        $thread = ThreadModel::get($tid);
        if (!$thread) {
            throw new \RuntimeException('主题不存在');
        }

        $data['tid'] = $tid;
        $data['uid'] = (int)($data['uid'] ?? $audit['uid'] ?? 0);
        $data['message'] = trim((string)($data['message'] ?? ''));
        if ((int)$data['uid'] <= 0 || $data['message'] === '') {
            throw new \RuntimeException('审核数据不完整');
        }

        $published = self::publishReplyContent($thread, $data);
        NotifyModel::addNotify((int)$published['uid'], Session::getUid(), (int)$published['tid'], (int)$published['pid'], '《' . $published['subject'] . '》的回帖已审核发布');
        return $published;
    }

    private static function publishThreadContent(array $data): array {
        $fid = (int)($data['fid'] ?? 0);
        $uid = (int)($data['uid'] ?? 0);
        $subject = trim((string)($data['subject'] ?? ''));
        $message = trim((string)($data['message'] ?? ''));
        if ($fid <= 0 || $uid <= 0 || $subject === '' || $message === '') {
            throw new \RuntimeException('发布数据不完整');
        }

        $creditRule = CreditModel::getRule(CreditModel::ACTION_THREAD_CREATE);
        $creditDid = 0;
        if ((int)$creditRule['credit'] < 0) {
            $creditDid = CreditModel::applyWithId(
                CreditModel::ACTION_THREAD_CREATE,
                $uid,
                '发布主题：' . $subject
            );
            if ($creditDid === 0) {
                throw new \RuntimeException(CreditModel::getInsufficientMessage(CreditModel::ACTION_THREAD_CREATE));
            }
        }

        $tid = ThreadModel::create([
            'fid' => $fid,
            'uid' => $uid,
            'subject' => $subject,
        ]);

        $pid = PostModel::create([
            'fid' => $fid,
            'tid' => $tid,
            'uid' => $uid,
            'message' => $message,
            'is_thread' => 1,
            'ip' => (string)($data['ip'] ?? ''),
        ]);

        $url = "index.php?c=thread&a=index&tid={$tid}";
        CreditModel::updateCreditUrl($creditDid, $url);
        $creditChange = 0;
        if ((int)$creditRule['credit'] > 0) {
            $creditDid = CreditModel::apply(CreditModel::ACTION_THREAD_CREATE, $uid, '发布主题：' . $subject, $url);
            if ($creditDid > 0) {
                $creditChange = (int)$creditRule['credit'];
            }
        } elseif ((int)$creditRule['credit'] < 0 && $creditDid > 0) {
            $creditChange = (int)$creditRule['credit'];
        }

        MemberModel::incrementThreadNum($uid);
        ForumModel::incrementThreadNum($fid, $tid);
        ForumModel::incrementTodayNum($fid);
        self::handleAtMentions($message, $tid, $pid, $uid);

        return [
            'tid' => $tid,
            'pid' => $pid,
            'fid' => $fid,
            'uid' => $uid,
            'subject' => $subject,
            'message' => $message,
            'credit_change' => $creditChange,
        ];
    }

    private static function publishReplyContent(array $thread, array $data): array {
        $tid = (int)($data['tid'] ?? $thread['tid'] ?? 0);
        $fid = (int)($thread['fid'] ?? 0);
        $uid = (int)($data['uid'] ?? 0);
        $message = trim((string)($data['message'] ?? ''));
        if ($tid <= 0 || $fid <= 0 || $uid <= 0 || $message === '') {
            throw new \RuntimeException('发布数据不完整');
        }

        $quotePid = (int)($data['quote_pid'] ?? 0);
        $quoteUid = (int)($data['quote_uid'] ?? 0);
        $quoteFloor = (int)($data['quote_floor'] ?? 0);
        $creditRule = CreditModel::getRule(CreditModel::ACTION_THREAD_REPLY);
        $creditDid = 0;
        if ((int)$creditRule['credit'] < 0) {
            $creditDid = CreditModel::applyWithId(
                CreditModel::ACTION_THREAD_REPLY,
                $uid,
                '回复主题：' . ($thread['subject'] ?? ''),
                "index.php?c=thread&a=index&tid={$tid}"
            );
            if ($creditDid === 0) {
                throw new \RuntimeException(CreditModel::getInsufficientMessage(CreditModel::ACTION_THREAD_REPLY));
            }
        }

        $pid = PostModel::create([
            'fid' => $fid,
            'tid' => $tid,
            'uid' => $uid,
            'message' => $message,
            'is_thread' => 0,
            'quote_pid' => $quotePid,
            'quote_uid' => $quoteUid,
            'quote_floor' => $quoteFloor,
            'ip' => (string)($data['ip'] ?? ''),
        ]);

        $url = "index.php?c=thread&a=index&tid={$tid}&pid={$pid}";
        CreditModel::updateCreditUrl($creditDid, $url);
        $creditChange = 0;
        if ((int)$creditRule['credit'] > 0) {
            $creditDid = CreditModel::apply(CreditModel::ACTION_THREAD_REPLY, $uid, '回复主题：' . ($thread['subject'] ?? ''), $url);
            if ($creditDid > 0) {
                $creditChange = (int)$creditRule['credit'];
            }
        } elseif ((int)$creditRule['credit'] < 0 && $creditDid > 0) {
            $creditChange = (int)$creditRule['credit'];
        }

        ForumModel::incrementTodayNum($fid);
        ThreadModel::updateReply($tid, $uid);
        MemberModel::incrementReplyNum($uid);
        ForumModel::incrementReplyNum($fid, $tid);
        if ((int)$thread['uid'] !== $uid) {
            NotifyModel::addNotify((int)$thread['uid'], $uid, $tid, $pid, '回复了你的主题');
        }
        if ($quoteUid > 0 && $quoteUid !== $uid && $quoteUid !== (int)$thread['uid']) {
            NotifyModel::addNotify($quoteUid, $uid, $tid, $pid, '在 ' . ($thread['subject'] ?? '主题') . ' 中引用了你的回复');
        }
        self::handleAtMentions($message, $tid, $pid, $uid);

        return [
            'tid' => $tid,
            'pid' => $pid,
            'fid' => $fid,
            'uid' => $uid,
            'subject' => (string)($thread['subject'] ?? ''),
            'message' => $message,
            'credit_change' => $creditChange,
        ];
    }

    public static function decodeAuditData(array $audit): array {
        $data = json_decode((string)($audit['json_data'] ?? '{}'), true);
        return is_array($data) ? $data : [];
    }

    private static function deleteThreadOnly(int $tid): void {
        $thread = ThreadModel::get($tid);
        if (!$thread) {
            return;
        }
        $posts = PostModel::getPostsByTid($tid);
        $replyCountsByUid = [];
        $replyCount = 0;
        $todayDecrement = (int)($thread['dateline'] ?? 0) >= strtotime(date('Y-m-d')) ? 1 : 0;
        foreach ($posts as $post) {
            if ((int)($post['is_thread'] ?? 0) !== 0) {
                continue;
            }
            $replyCount++;
            if ((int)($post['dateline'] ?? 0) >= strtotime(date('Y-m-d'))) {
                $todayDecrement++;
            }
            $uid = (int)($post['uid'] ?? 0);
            if ($uid > 0) {
                $replyCountsByUid[$uid] = ($replyCountsByUid[$uid] ?? 0) + 1;
            }
        }
        ThreadModel::delete($tid);
        PostModel::deleteByTid($tid);
        self::closeThreadAudits($tid);
        MemberModel::decrementThreadNum((int)$thread['uid']);
        ForumModel::decrementThreadNumForTid((int)$thread['fid'], $tid);
        ForumModel::decrementReplyNum((int)$thread['fid'], $replyCount);
        ForumModel::decrementTodayNum((int)$thread['fid'], $todayDecrement);
        foreach ($replyCountsByUid as $uid => $count) {
            MemberModel::decrementReplyNum((int)$uid, (int)$count);
        }
        self::logThreadAction(
            'thread_delete',
            $thread,
            '删除主题: ' . ($thread['subject'] ?? '') . " (TID: {$tid})",
            0,
            self::buildThreadArchive($thread, $posts)
        );
    }

    private static function logPostAction(string $action, array $post, string $message, string $archiveData): void {
        ModLogModel::addLog(
            Session::getUid(),
            $action,
            $message,
            (int)($post['tid'] ?? 0),
            (int)($post['pid'] ?? 0),
            (int)($post['uid'] ?? 0),
            $archiveData
        );
    }

    private static function buildPostArchive(array $post): string {
        return json_encode($post, JSON_UNESCAPED_UNICODE);
    }

    private static function buildThreadArchive(array $thread, array $posts = []): string {
        if (!empty($posts)) {
            $thread['_posts'] = $posts;
        }
        return json_encode($thread, JSON_UNESCAPED_UNICODE);
    }

    private static function logThreadAction(string $action, array $thread, string $message, int $pid = 0, string $archiveData = ''): void {
        ModLogModel::addLog(
            Session::getUid(),
            $action,
            $message,
            (int)($thread['tid'] ?? 0),
            $pid,
            (int)($thread['uid'] ?? 0),
            $archiveData
        );
    }

    private static function closeThreadAudits(int $tid): void {
        AuditModel::finishPendingByThread($tid, -1, Session::getUid());
    }

    private static function auditDenied(): void {
        if (Response::isAjaxRequest()) {
            Response::error('无权限访问', 403);
        }
        Response::redirect('index.php');
    }

    private static function auditDone(string $redirect): void {
        if (Response::isAjaxRequest()) {
            Response::json(['success' => true, 'redirect' => $redirect]);
        }
        Response::redirect($redirect);
    }

    private static function requirePost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (Response::isAjaxRequest()) {
                Response::error('Method not allowed', 405);
            }
            Response::redirect('index.php');
        }
    }
}
?>
