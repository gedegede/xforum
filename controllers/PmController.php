<?php
declare(strict_types=1);

namespace Controllers;

use Lib\Session;
use Lib\Template;
use Models\PmModel;
use Models\MemberModel;
use Models\NotifyModel;

class PmController {
    public static function inbox(): void {
        Template::clear();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $messages = PmModel::getInbox(Session::getUid(), $page);
        $total = PmModel::getInboxCount(Session::getUid());

        PmModel::markAsRead(Session::getUid());

        $users = [];
        if (!empty($messages)) {
            $uids = array_unique(array_column($messages, 'uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        Template::set('title', '收件箱');
        Template::set('messages', $messages);
        Template::set('users', $users);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('pm/inbox');
    }

    public static function outbox(): void {
        Template::clear();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $messages = PmModel::getOutbox(Session::getUid(), $page);
        $total = PmModel::getOutboxCount(Session::getUid());

        $users = [];
        if (!empty($messages)) {
            $uids = array_unique(array_column($messages, 'to_uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        Template::set('title', '发件箱');
        Template::set('messages', $messages);
        Template::set('users', $users);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / 20));
        Template::set('user', Session::getUser());
        Template::display('pm/outbox');
    }

    public static function send(int $toUid = 0): void {
        Template::clear();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $error = '';
        $receiver = null;

        if ($toUid) {
            $receiver = MemberModel::get($toUid);
            if (!$receiver) {
                $toUid = 0;
                $receiver = null;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $toUid = (int)($_POST['to_uid'] ?? 0);
            $content = trim($_POST['content'] ?? '');

            if (!$toUid) {
                $error = '请选择收件人';
            } elseif (empty($content)) {
                $error = '请输入消息内容';
            } elseif ($toUid == Session::getUid()) {
                $error = '不能给自己发送私信';
            } else {
                $receiver = MemberModel::get($toUid);
                if (!$receiver) {
                    $error = '收件人不存在';
                } else {
                    PmModel::send(Session::getUid(), $toUid, $content);
                    NotifyModel::addPMNotify($toUid, Session::getUid());
                    header('Location: index.php?c=pm&a=outbox');
                    exit;
                }
            }
        }

        Template::set('title', '发送私信');
        Template::set('toUid', $toUid);
        Template::set('receiver', $receiver);
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::display('pm/send');
    }

    public static function view(int $pmid): void {
        Template::clear();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $message = PmModel::get($pmid);
        if (!$message) {
            header('Location: index.php?c=pm&a=inbox');
            exit;
        }

        if ($message['uid'] != Session::getUid() && $message['to_uid'] != Session::getUid()) {
            header('Location: index.php?c=pm&a=inbox');
            exit;
        }

        PmModel::markSingleAsRead($pmid);

        $sender = MemberModel::get($message['uid']);

        Template::set('title', '查看私信');
        Template::set('message', $message);
        Template::set('sender', $sender);
        Template::set('user', Session::getUser());
        Template::display('pm/view');
    }
}
?>