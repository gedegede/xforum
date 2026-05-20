<?php
declare(strict_types=1);

namespace Controllers;

use Lib\Template;
use Lib\Session;
use Lib\MarkdownHelper;
use Lib\PostHelper;
use Models\ThreadModel;
use Models\PostModel;
use Models\ForumModel;
use Models\MemberModel;
use Models\NotifyModel;
use Models\FavModel;
use Models\PmModel;

class ThreadController {
    public static function index(int $tid): void {
        Template::clear();
        if (!$tid) {
            header('Location: index.php');
            exit;
        }

        $thread = ThreadModel::get($tid);
        if (!$thread) {
            header('Location: index.php');
            exit;
        }

        $forum = ForumModel::get($thread['fid']);

        ThreadModel::incrementView($tid);

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $posts = PostModel::getPosts($tid, $page);
        $total = PostModel::getPostCount($tid);
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
        Template::set('user', Session::getUser());
        Template::set('isFavorited', $isFavorited);
        Template::display('thread/index');
    }

    public static function create(?int $fid = null): void {
        Template::clear();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        if (!$fid) {
            header('Location: index.php?c=forum&a=index&from=create');
            exit;
        }

        $forum = ForumModel::get($fid);
        if (!$forum) {
            header('Location: index.php');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if (empty($subject) || empty($message)) {
                $error = '请填写标题和内容';
            } else {
                $tid = ThreadModel::create([
                    'fid' => $fid,
                    'uid' => Session::getUid(),
                    'subject' => $subject,
                ]);

                $messageHtml = MarkdownHelper::parse($message);
                PostModel::create([
                    'fid' => $fid,
                    'tid' => $tid,
                    'uid' => Session::getUid(),
                    'message' => $message,
                    'message_html' => $messageHtml,
                    'is_thread' => 1,
                ]);

                header("Location: index.php?c=thread&a=index&tid={$tid}");
                exit;
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
        if (!Session::isLoggedIn()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '请先登录']);
                exit;
            }
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $thread = ThreadModel::get($tid);
        if (!$thread) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '主题不存在']);
                exit;
            }
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = trim($_POST['message'] ?? '');
            $quotePid = isset($_POST['quote_pid']) ? (int)$_POST['quote_pid'] : 0;
            $quoteUid = isset($_POST['quote_uid']) ? (int)$_POST['quote_uid'] : 0;

            if (empty($message)) {
                $errorMsg = '请填写回复内容';
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $errorMsg]);
                    exit;
                }
                Template::set('title', '回复帖子');
                Template::set('thread', $thread);
                Template::set('error', $errorMsg);
                Template::set('user', Session::getUser());
                Template::display('thread/reply');
                exit;
            }

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

            $messageHtml = MarkdownHelper::parse($message);
            $pid = PostModel::create([
                'fid' => $thread['fid'],
                'tid' => $tid,
                'uid' => Session::getUid(),
                'message' => $message,
                'message_html' => $messageHtml,
                'is_thread' => 0,
                'quote_pid' => $quotePid,
                'quote_uid' => $quoteUid,
                'quote_floor' => $quoteFloor,
            ]);

            ThreadModel::updateReply($tid, Session::getUid());

            if ($thread['uid'] != Session::getUid()) {
                NotifyModel::addNotify($thread['uid'], Session::getUid(), $tid, $pid, '回复了你的主题');
            }

            if ($quoteUid > 0 && $quoteUid != Session::getUid() && $quoteUid != $thread['uid']) {
                NotifyModel::addNotify($quoteUid, Session::getUid(), $tid, $pid, '引用了你的回复');
            }

            self::handleAtMentions($message, $tid, $pid);

            PmModel::markAsRead($thread['uid']);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                $currentUser = Session::getUser();
                $postIndex = PostModel::getPostCount($tid);
                
                $newPost = [
                    'pid' => $pid,
                    'uid' => Session::getUid(),
                    'message' => $message,
                    'message_html' => $messageHtml,
                    'dateline' => time(),
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
                
                $html = PostHelper::renderPost($newPost, $users, $postIndex, false, $currentUser);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => '回复成功',
                    'html' => $html,
                    'postIndex' => $postIndex,
                ]);
                exit;
            }

            header("Location: index.php?c=thread&a=index&tid={$tid}");
            exit;
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
        if (!Session::isLoggedIn()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '请先登录']);
                exit;
            }
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $isFavorited = FavModel::isFavorite(Session::getUid(), $tid);
        if ($isFavorited) {
            FavModel::removeFavorite(Session::getUid(), $tid);
        } else {
            FavModel::addFavorite(Session::getUid(), $tid);
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'favorited' => !$isFavorited]);
            exit;
        }

        header("Location: index.php?c=thread&a=index&tid={$tid}");
        exit;
    }

    public static function edit(int $pid): void {
        Template::clear();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $post = PostModel::get($pid);
        if (!$post) {
            header('Location: index.php');
            exit;
        }

        $user = Session::getUser();
        if ($post['uid'] != $user['uid'] && $user['gid'] != 1) {
            header('Location: index.php?c=thread&a=index&tid=' . $post['tid']);
            exit;
        }

        $thread = ThreadModel::get($post['tid']);
        $forum = ForumModel::get($post['fid']);

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = trim($_POST['message'] ?? '');

            if (empty($message)) {
                $error = '请填写内容';
            } else {
                $messageHtml = MarkdownHelper::parse($message);
                PostModel::update($pid, ['message' => $message, 'message_html' => $messageHtml]);
                header("Location: index.php?c=thread&a=index&tid={$post['tid']}");
                exit;
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
        if (!Session::isLoggedIn()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '请先登录']);
                exit;
            }
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $post = PostModel::get($pid);
        if (!$post) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '帖子不存在']);
                exit;
            }
            header('Location: index.php');
            exit;
        }

        $user = Session::getUser();
        if ($post['uid'] != $user['uid'] && $user['gid'] != 1) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '没有权限删除']);
                exit;
            }
            header('Location: index.php?c=thread&a=index&tid=' . $post['tid']);
            exit;
        }

        $tid = $post['tid'];
        PostModel::delete($pid);

        if ($post['is_thread'] == 1) {
            PostModel::deleteByTid($tid);
            ThreadModel::delete($tid);
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => '主题已删除', 'redirect' => 'index.php?c=forum&a=index&fid=' . $post['fid']]);
                exit;
            }
            header('Location: index.php?c=forum&a=index&fid=' . $post['fid']);
            exit;
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => '删除成功']);
            exit;
        }

        header("Location: index.php?c=thread&a=index&tid={$tid}");
        exit;
    }
}
?>