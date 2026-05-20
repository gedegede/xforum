<?php
declare(strict_types=1);

namespace Controllers;

use Lib\Session;
use Lib\Template;
use Models\ForumModel;
use Models\ThreadModel;
use Models\MemberModel;

class ForumController {
    public static function index(int $fid = 0): void {
        Template::clear();
        if (!$fid) {
            $forums = ForumModel::getForumsFlat();
            $from = isset($_GET['from']) ? $_GET['from'] : '';

            Template::set('title', $from === 'create' ? '选择版块' : '论坛导航');
            Template::set('forums', $forums);
            Template::set('from', $from);
            Template::set('user', Session::getUser());
            Template::display('forum/list');
            return;
        }

        $forum = ForumModel::get($fid);
        if (!$forum) {
            header('Location: index.php?c=forum&a=index');
            exit;
        }

        $parentForum = null;
        if (!empty($forum['up_fid'])) {
            $parentForum = ForumModel::get($forum['up_fid']);
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $order = isset($_GET['order']) ? $_GET['order'] : 'reply_time';
        $allowedOrders = ['reply_time', 'dateline', 'reply_num', 'view_num'];
        if (!in_array($order, $allowedOrders)) {
            $order = 'reply_time';
        }
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        
        $threads = ThreadModel::getThreads($fid, $page, $order, $keyword);
        $total = ThreadModel::getThreadCount($fid, $keyword);
        $pages = (int)ceil($total / 20);

        $users = [];
        if (!empty($threads)) {
            $uids = array_unique(array_column($threads, 'uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        $orderOptions = [
            ['value' => 'reply_time', 'label' => '最后回复'],
            ['value' => 'dateline', 'label' => '最新发布'],
            ['value' => 'reply_num', 'label' => '回复数'],
            ['value' => 'view_num', 'label' => '查看数']
        ];

        Template::set('title', $forum['name']);
        Template::set('forum', $forum);
        Template::set('parentForum', $parentForum);
        Template::set('threads', $threads);
        Template::set('users', $users);
        Template::set('page', $page);
        Template::set('pages', $pages);
        Template::set('order', $order);
        Template::set('keyword', $keyword);
        Template::set('orderOptions', $orderOptions);
        Template::set('user', Session::getUser());
        Template::display('forum/index');
    }
}
?>