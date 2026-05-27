<?php
declare(strict_types=1);

namespace Controllers;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\Session;
use Lib\Template;
use Lib\Response;
use Lib\Request;
use Models\PmModel;
use Models\MemberModel;
use Models\NotifyModel;
use Models\CreditModel;
use Lib\Permission;

class PmController {
    public static function inbox(): void {
        Template::clear();
        Permission::requireLogin();

        $page = Request::getInt('page', 1);
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
        Permission::requireLogin();

        $page = Request::getInt('page', 1);
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
        Permission::requireLogin();

        $error = Permission::canSendPm() ? '' : '无权限发送私信';
        $receiver = null;

        if ($toUid) {
            $receiver = MemberModel::get($toUid);
            if (!$receiver) {
                $toUid = 0;
                $receiver = null;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $toUid = Request::postInt('to_uid');
            $content = Request::postString('content');

            if ($error !== '') {
                // 保留无权限错误
            } elseif (!$toUid) {
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
                    $creditRule = CreditModel::getRule(CreditModel::ACTION_PM_SEND);
                    $creditMessage = '发送私信给：' . ($receiver['username'] ?? $toUid);
                    $creditDid = 0;
                    $pmid = 0;
                    $inTransaction = false;
                    try {
                        Database::beginTransaction();
                        $inTransaction = true;

                        if ((int)$creditRule['credit'] < 0) {
                            $creditDid = CreditModel::applyWithId(
                                CreditModel::ACTION_PM_SEND,
                                Session::getUid(),
                                $creditMessage
                            );
                            if ($creditDid === 0) {
                                throw new \RuntimeException(CreditModel::getInsufficientMessage(CreditModel::ACTION_PM_SEND));
                            }
                        }

                        $pmid = PmModel::send(Session::getUid(), $toUid, $content);
                        CreditModel::updateCreditUrl($creditDid, "index.php?c=pm&a=view&pmid={$pmid}");
                        if ((int)$creditRule['credit'] > 0) {
                            CreditModel::apply(
                                CreditModel::ACTION_PM_SEND,
                                Session::getUid(),
                                $creditMessage,
                                "index.php?c=pm&a=view&pmid={$pmid}"
                            );
                        }

                        NotifyModel::addPMNotify($toUid, Session::getUid());
                        Database::commit();
                        $inTransaction = false;
                    } catch (\Throwable $e) {
                        if ($inTransaction) {
                            Database::rollBack();
                        }
                        $error = $e instanceof \RuntimeException ? $e->getMessage() : '私信发送失败，请稍后重试';
                    }

                    if ($error) {
                        Template::set('title', '发送私信');
                        Template::set('toUid', $toUid);
                        Template::set('receiver', $receiver);
                        Template::set('error', $error);
                        Template::set('user', Session::getUser());
                        Template::display('pm/send');
                        return;
                    }

                    Response::redirect('index.php?c=pm&a=outbox');
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
        Permission::requireLogin();

        $message = PmModel::get($pmid);
        if (!$message) {
            Response::redirect('index.php?c=pm&a=inbox');
        }

        if ($message['uid'] != Session::getUid() && $message['to_uid'] != Session::getUid()) {
            Response::redirect('index.php?c=pm&a=inbox');
        }

        if ((int)$message['to_uid'] === Session::getUid()) {
            PmModel::markSingleAsRead($pmid, Session::getUid());
        }

        $sender = MemberModel::get($message['uid']);

        Template::set('title', '查看私信');
        Template::set('message', $message);
        Template::set('sender', $sender);
        Template::set('user', Session::getUser());
        Template::display('pm/view');
    }
}
?>
