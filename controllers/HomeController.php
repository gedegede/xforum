<?php

class HomeController {

    public static function index() {
        Template::clear();
        
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page = $page < 1 ? 1 : $page;
        
        $order = isset($_GET['order']) ? trim($_GET['order']) : 'reply_time';
        $orderMap = ['reply_time', 'dateline', 'reply_num', 'view_num'];
        if (!in_array($order, $orderMap)) {
            $order = 'reply_time';
        }
        
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        
        $forums = ForumModel::getForums();
        $threads = ThreadModel::getHomeThreadsWithFilter($page, $order, $keyword);
        $total = ThreadModel::getHomeThreadCount($keyword);
        $pages = ceil($total / 20);

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

        Template::set('title', 'XForum');
        Template::set('forums', $forums);
        Template::set('threads', $threads);
        Template::set('users', $users);
        Template::set('user', Session::getUser());
        Template::set('order', $order);
        Template::set('keyword', $keyword);
        Template::set('orderOptions', $orderOptions);
        Template::set('page', $page);
        Template::set('pages', $pages);
        Template::display('home/index');
    }
}
?>
