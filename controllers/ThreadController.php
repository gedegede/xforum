<?php

class ThreadController {

    public static function index($tid) {
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

        // 增加浏览次数
        ThreadModel::incrementView($tid);

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $posts = PostModel::getPosts($tid, $page);
        $total = PostModel::getPostCount($tid);
        $pages = ceil($total / 20);

        // 收集所有需要的用户 ID（包括帖子作者和引用帖子的作者）
        $uids = array_unique(array_merge([$thread['uid']], array_column($posts, 'uid')));
        
        // 收集引用帖子的用户 ID
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

    public static function create($fid) {
        Template::clear();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?c=auth&a=login');
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

                PostModel::create([
                    'fid' => $fid,
                    'tid' => $tid,
                    'uid' => Session::getUid(),
                    'message' => $message,
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

    public static function reply($tid) {
        Template::clear();
        if (!Session::isLoggedIn()) {
            // AJAX 请求返回 JSON
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
                // AJAX 请求返回 JSON
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $errorMsg]);
                    exit;
                }
                // 传统表单提交
                Template::set('title', '回复帖子');
                Template::set('thread', $thread);
                Template::set('error', $errorMsg);
                Template::set('user', Session::getUser());
                Template::display('thread/reply');
                exit;
            }

            // 处理引用回复
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

            $pid = PostModel::create([
                'fid' => $thread['fid'],
                'tid' => $tid,
                'uid' => Session::getUid(),
                'message' => $message,
                'is_thread' => 0,
                'quote_pid' => $quotePid,
                'quote_uid' => $quoteUid,
                'quote_floor' => $quoteFloor,
            ]);

            ThreadModel::updateReply($tid, Session::getUid());

            if ($thread['uid'] != Session::getUid()) {
                NotifyModel::addNotify($thread['uid'], Session::getUid(), $tid, $pid, '回复了你的主题');
            }

            // 如果引用了其他用户的帖子，发送通知
            if ($quoteUid > 0 && $quoteUid != Session::getUid() && $quoteUid != $thread['uid']) {
                NotifyModel::addNotify($quoteUid, Session::getUid(), $tid, $pid, '引用了你的回复');
            }

            self::handleAtMentions($message, $tid, $pid);

            PmModel::markAsRead($thread['uid']);

            // AJAX 请求返回渲染好的 HTML
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                // 获取当前用户信息
                $currentUser = Session::getUser();
                
                // 计算帖子索引（当前回复数）
                $postIndex = PostModel::getPostCount($tid);
                
                // 准备帖子数据
                $newPost = [
                    'pid' => $pid,
                    'uid' => Session::getUid(),
                    'message' => $message,
                    'dateline' => time(),
                    'quote_pid' => $quotePid,
                    'quote_uid' => $quoteUid,
                    'quote_floor' => $quoteFloor,
                ];
                
                // 准备用户数据
                $users = [
                    Session::getUid() => $currentUser,
                ];
                
                // 如果有引用，也需要获取被引用用户的信息
                if ($quoteUid > 0) {
                    $quoteUser = MemberModel::get($quoteUid);
                    if ($quoteUser) {
                        $users[$quoteUid] = $quoteUser;
                    }
                }
                
                // 使用辅助函数渲染 HTML
                $html = PostHelper::renderPost($newPost, $users, $postIndex, false);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => '回复成功',
                    'html' => $html,
                    'postIndex' => $postIndex,
                ]);
                exit;
            }

            // 传统表单提交重定向
            header("Location: index.php?c=thread&a=index&tid={$tid}");
            exit;
        }

        Template::set('title', '回复帖子');
        Template::set('thread', $thread);
        Template::set('error', '');
        Template::set('user', Session::getUser());
        Template::display('thread/reply');
    }

    private static function handleAtMentions($message, $tid, $pid) {
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

    public static function favorite($tid) {
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
}
?>
