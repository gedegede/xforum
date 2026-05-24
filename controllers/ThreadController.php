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
use Models\ThreadModel;
use Models\PostModel;
use Models\ForumModel;
use Models\MemberModel;
use Models\NotifyModel;
use Models\FavModel;
use Models\PmModel;
use Models\SettingModel;
use Models\UsergroupModel;
use Models\DataModel;
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

        $forum = ForumModel::get($thread['fid']);

        $user = Session::getUser();
        SessionModel::updateOnline(
            $user['uid'] ?? 0,
            $user['gid'] ?? 0,
            $user['invisible'] ?? 0,
            $thread['fid'],
            $tid
        );

        $isModerator = Permission::isModerator($thread['fid']);

        $targetPid = $pid > 0 ? $pid : Request::getInt('pid', 0);
        $page = Request::getInt('page', 1);
        if ($targetPid > 0) {
            $targetPost = PostModel::get($targetPid);
            if ($targetPost && (int)$targetPost['tid'] === $tid) {
                $targetPage = PostModel::getPostPage($targetPid);
                if ($targetPage > 0) {
                    $page = $targetPage;
                }
            } else {
                $targetPid = 0;
            }
        }
        $posts = PostModel::getPosts($tid, $page, $isModerator);
        $total = (int)($thread['reply_num'] ?? 0) + 1;
        
        ThreadModel::incrementView($tid);
        $pages = (int)ceil($total / 20);

        $uids = array_unique(array_merge([$thread['uid']], array_column($posts, 'uid')));
        
        $quoteUids = array_filter(array_unique(array_column($posts, 'quote_uid')));
        if (!empty($quoteUids)) {
            $uids = array_unique(array_merge($uids, $quoteUids));
        }
        
        $users = MemberModel::getMembersByUids($uids);

        $isFavorited = Session::isLoggedIn() && FavModel::isFavorite(Session::getUid(), $tid);

        Template::set('title', $thread['subject']);
        Template::set('thread', $thread);
        Template::set('forum', $forum);
        Template::set('posts', $posts);
        Template::set('users', $users);
        Template::set('page', $page);
        Template::set('pages', $pages);
        Template::set('targetPid', $targetPid);
        Template::set('user', Session::getUser());
        Template::set('isFavorited', $isFavorited);
        Template::set('isModerator', $isModerator);
        Template::display('thread/index');
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = Request::postString('subject');
            $message = Request::postString('message');

            if (empty($subject) || empty($message)) {
                $error = '请填写标题和内容';
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

                    $sortOrder = $needApprove ? -1 : 0;
                    $creditRule = CreditModel::getRule(CreditModel::ACTION_THREAD_CREATE);
                    $creditDid = 0;
                    $inTransaction = false;

                    if (!$error) {
                        try {
                            Database::beginTransaction();
                            $inTransaction = true;

                            if ((int)$creditRule['credit'] < 0) {
                                $creditDid = CreditModel::applyWithId(
                                    CreditModel::ACTION_THREAD_CREATE,
                                    Session::getUid(),
                                    '发布主题：' . $subject
                                );
                                if ($creditDid === 0) {
                                    throw new \RuntimeException(CreditModel::getInsufficientMessage(CreditModel::ACTION_THREAD_CREATE));
                                }
                            }

                            $tid = ThreadModel::create([
                                'fid' => $fid,
                                'uid' => Session::getUid(),
                                'subject' => $subject,
                                'sort_order' => $sortOrder,
                            ]);

                            PostModel::create([
                                'fid' => $fid,
                                'tid' => $tid,
                                'uid' => Session::getUid(),
                                'message' => $message,
                                'is_thread' => 1,
                                'sort_order' => $sortOrder,
                            ]);

                            ForumModel::incrementTodayNum($fid);

                            $creditUrl = "index.php?c=thread&a=index&tid={$tid}";
                            CreditModel::updateCreditUrl($creditDid, $creditUrl);
                            if (!$needApprove && (int)$creditRule['credit'] > 0) {
                                CreditModel::apply(CreditModel::ACTION_THREAD_CREATE, Session::getUid(), '发布主题：' . $subject, $creditUrl);
                            }

                            if ($needApprove) {
                                DataModel::updateCount('pending_threads', 1);
                            } else {
                                MemberModel::incrementThreadNum(Session::getUid());
                                ForumModel::incrementThreadNum($fid, $tid);
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
                            Template::set('title', '发布成功');
                            Template::set('message', '主题已提交，等待审核');
                            Template::set('user', Session::getUser());
                            Template::display('thread/pending');
                            exit;
                        }

                        if (!$error) {
                            // 发帖成功后更新在线状态
                            $user = Session::getUser();
                            SessionModel::updateOnline($user['uid'], $user['gid'], $user['invisible'], $fid, $tid);

                            Response::redirect("index.php?c=thread&a=index&tid={$tid}");
                        }
                    }
                }
            }
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = Request::postString('message');
            $quotePid = Request::postInt('quote_pid');
            $quoteUid = Request::postInt('quote_uid');

            if (empty($message)) {
                $errorMsg = '请填写回复内容';
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

            $sortOrder = $needApprove ? -1 : 0;
            $creditRule = CreditModel::getRule(CreditModel::ACTION_THREAD_REPLY);
            $creditDid = 0;

            $quoteFloor = 0;
            if ($quotePid > 0 && $quoteUid > 0) {
                $quotePost = PostModel::get($quotePid);
                if ($quotePost && $quotePost['tid'] == $tid) {
                    $quoteFloor = PostModel::getPostFloor($quotePid);
                } else {
                    $quotePid = 0;
                    $quoteUid = 0;
                }
            }

            $pid = 0;
            $errorMsg = '';
            $inTransaction = false;
            try {
                Database::beginTransaction();
                $inTransaction = true;

                if ((int)$creditRule['credit'] < 0) {
                $creditDid = CreditModel::applyWithId(
                    CreditModel::ACTION_THREAD_REPLY,
                    Session::getUid(),
                    '回复主题：' . ($thread['subject'] ?? ''),
                    "index.php?c=thread&a=index&tid={$tid}"
                );
                    if ($creditDid === 0) {
                        throw new \RuntimeException(CreditModel::getInsufficientMessage(CreditModel::ACTION_THREAD_REPLY));
                    }
                }

                $pid = PostModel::create([
                    'fid' => $thread['fid'],
                    'tid' => $tid,
                    'uid' => Session::getUid(),
                    'message' => $message,
                    'is_thread' => 0,
                    'quote_pid' => $quotePid,
                    'quote_uid' => $quoteUid,
                    'quote_floor' => $quoteFloor,
                    'sort_order' => $sortOrder,
                ]);
                CreditModel::updateCreditUrl($creditDid, "index.php?c=thread&a=index&tid={$tid}&pid={$pid}");

                if (!$needApprove) {
                    ForumModel::incrementTodayNum($thread['fid']);
                    ThreadModel::updateReply($tid, Session::getUid());
                    MemberModel::incrementReplyNum(Session::getUid());
                    ForumModel::incrementReplyNum($thread['fid'], $tid);
                } else {
                    DataModel::updateCount('pending_posts', 1);
                }

                if (!$needApprove && (int)$creditRule['credit'] > 0) {
                    CreditModel::apply(
                        CreditModel::ACTION_THREAD_REPLY,
                        Session::getUid(),
                        '回复主题：' . ($thread['subject'] ?? ''),
                        "index.php?c=thread&a=index&tid={$tid}&pid={$pid}"
                    );
                }

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

            if ($thread['uid'] != Session::getUid()) {
                NotifyModel::addNotify($thread['uid'], Session::getUid(), $tid, $pid, '回复了你的主题');
            }

            if ($quoteUid > 0 && $quoteUid != Session::getUid() && $quoteUid != $thread['uid']) {
                NotifyModel::addNotify($quoteUid, Session::getUid(), $tid, $pid, '引用了你的回复');
            }

            self::handleAtMentions($message, $tid, $pid);

            PmModel::markAsRead($thread['uid']);
            
            // 回复成功后更新在线状态
            $user = Session::getUser();
            SessionModel::updateOnline($user['uid'], $user['gid'], $user['invisible'], $thread['fid'], $tid);

            if (Response::isAjaxRequest()) {
                $currentUser = Session::getUser();
                $postIndex = (int)($thread['reply_num'] ?? 0) + 2;
                
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
                    'sort_order' => $sortOrder,
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
                
                $isMod = Permission::isModerator($thread['fid']);
                $html = PostHelper::renderPost($newPost, $users, $postIndex, false, $currentUser, $isMod);
                
                Response::json([
                    'success' => true,
                    'message' => '回复成功',
                    'html' => $html,
                    'postIndex' => $postIndex,
                ]);
            }

            Response::redirect("index.php?c=thread&a=index&tid={$tid}");
        }

        Template::set('title', '回复帖子');
        Template::set('thread', $thread);
        Template::set('error', '');
        Template::set('user', Session::getUser());
        Template::display('thread/reply');
    }

    private static function handleAtMentions(string $message, int $tid, int $pid): void {
        preg_match_all('/@(\w+)/', $message, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $username) {
                $member = MemberModel::getByUsername($username);
                if ($member && $member['uid'] != Session::getUid()) {
                    NotifyModel::addNotify($member['uid'], Session::getUid(), $tid, $pid, "@了你");
                }
            }
        }
    }

    public static function favorite(int $tid): void {
        Permission::requireLogin();

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

        $tid = $post['tid'];
        PostModel::delete($pid);

        if ($post['is_thread'] == 1) {
            PostModel::deleteByTid($tid);
            ThreadModel::delete($tid);
            MemberModel::decrementThreadNum($post['uid']);
            ForumModel::decrementThreadNum($post['fid']);
            if (Response::isAjaxRequest()) {
                Response::json(['success' => true, 'message' => '主题已删除', 'redirect' => 'index.php?c=forum&a=index&fid=' . $post['fid']]);
            }
            Response::redirect('index.php?c=forum&a=index&fid=' . $post['fid']);
        }

        MemberModel::decrementReplyNum($post['uid']);
        ForumModel::decrementReplyNum($post['fid']);

        if (Response::isAjaxRequest()) {
            Response::json(['success' => true, 'message' => '删除成功']);
        }

        Response::redirect("index.php?c=thread&a=index&tid={$tid}");
    }

    public static function report(int $pid): void {
        Permission::requireLogin();

        $post = PostModel::get($pid);
        if (!$post) {
            Response::error('帖子不存在');
        }

        $reason = Request::postString('reason');
        if (empty($reason)) {
            Response::error('请填写举报理由');
        }

        $reportFid = SettingModel::getReportForumFid();
        if ($reportFid <= 0) {
            Response::error('未设置举报版块');
        }

        $thread = ThreadModel::get($post['tid']);
        $reportSubject = "[举报] " . ($thread['subject'] ?? '未知主题');
        $reportMessage = "举报链接: index.php?c=thread&a=index&tid={$post['tid']}&pid={$pid}\n\n举报理由: {$reason}\n\n帖子作者ID: {$post['uid']}\n帖子ID: {$pid}";
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

            $tid = ThreadModel::create([
                'fid' => $reportFid,
                'uid' => Session::getUid(),
                'subject' => $reportSubject,
            ]);

            PostModel::create([
                'fid' => $reportFid,
                'tid' => $tid,
                'uid' => Session::getUid(),
                'message' => $reportMessage,
                'is_thread' => 1,
            ]);

            DataModel::updateCount('pending_reports', 1);
            ForumModel::incrementTodayNum($reportFid);
            ForumModel::incrementThreadNum($reportFid, $tid);
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
        $thread = ThreadModel::get($tid);
        if (!$thread || !Permission::canManageForum($thread['fid'])) {
            Response::redirect('index.php');
        }

        $wasPending = (int)($thread['sort_order'] ?? 0) < 0;
        ThreadModel::update($tid, ['sort_order' => 0]);
        PostModel::approveByTid($tid);

        if ($wasPending) {
            DataModel::updateCount('pending_threads', -1);
        }

        if ($thread && $wasPending) {
            MemberModel::incrementThreadNum($thread['uid']);
            ForumModel::incrementThreadNum($thread['fid'], $tid);
            ForumModel::incrementTodayNum($thread['fid']);
            if ((int)CreditModel::getRule(CreditModel::ACTION_THREAD_CREATE)['credit'] > 0) {
                CreditModel::apply(
                    CreditModel::ACTION_THREAD_CREATE,
                    (int)$thread['uid'],
                    '发布主题：' . ($thread['subject'] ?? ''),
                    "index.php?c=thread&a=index&tid={$tid}"
                );
            }
        }

        Response::redirect("index.php?c=thread&a=index&tid={$tid}");
    }

    public static function approvePost(int $pid): void {
        $post = PostModel::get($pid);
        if (!$post || !Permission::canManageForum($post['fid'])) {
            Response::redirect('index.php');
        }

        if ($post) {
            $wasPending = (int)($post['sort_order'] ?? 0) < 0;
            PostModel::update($pid, ['sort_order' => 0]);
            if ($post['is_thread'] == 0 && $wasPending) {
                DataModel::updateCount('pending_posts', -1);
                $thread = ThreadModel::get($post['tid']);
                if ($thread) {
                    MemberModel::incrementReplyNum($post['uid']);
                    ForumModel::incrementReplyNum($thread['fid'], $post['tid']);
                    ForumModel::incrementTodayNum($thread['fid']);
                    if ((int)CreditModel::getRule(CreditModel::ACTION_THREAD_REPLY)['credit'] > 0) {
                        CreditModel::apply(
                            CreditModel::ACTION_THREAD_REPLY,
                            (int)$post['uid'],
                            '回复主题：' . ($thread['subject'] ?? ''),
                            "index.php?c=thread&a=index&tid={$post['tid']}&pid={$pid}"
                        );
                    }
                }
            }
        }

        Response::redirect("index.php?c=thread&a=index&tid={$post['tid']}");
    }
}
?>
