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
use Models\CreditModel;
use Models\UsergroupModel;
use Lib\Permission;

class PmController {
    public static function inbox(): void {
        Template::clear();
        Permission::requireLogin();

        $page = Request::getInt('page', 1);
        $messages = PmModel::getConversations(Session::getUid(), $page);
        $total = PmModel::getConversationCount(Session::getUid());

        $users = [];
        if (!empty($messages)) {
            $uids = array_unique(array_column($messages, 'partner_uid'));
            $users = MemberModel::getMembersByUids($uids);
        }

        Template::set('title', '私信');
        Template::set('messages', $messages);
        Template::set('users', $users);
        Template::set('page', $page);
        Template::set('pages', (int)ceil($total / PmModel::PAGE_SIZE));
        Template::set('user', Session::getUser());
        Template::set('member', Session::getUser());
        Template::set('memberGroup', UsergroupModel::get((int)(Session::getUser()['gid'] ?? 0)));
        Template::set('isSelf', true);
        Template::display('pm/inbox');
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
            $username = Request::postString('username');
            $content = Request::postString('content');
            $receiver = $username !== '' ? MemberModel::getByUsername($username) : null;
            $toUid = (int)($receiver['uid'] ?? 0);

            if ($error !== '') {
                // 保留无权限错误
            } elseif (!$toUid) {
                $error = '收件人不存在';
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
                    $creditChange = 0;
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
                        CreditModel::updateCreditUrl($creditDid, "index.php?c=pm&a=view&uid={$toUid}");
                        if ((int)$creditRule['credit'] > 0) {
                            CreditModel::apply(
                                CreditModel::ACTION_PM_SEND,
                                Session::getUid(),
                                $creditMessage,
                                "index.php?c=pm&a=view&uid={$toUid}"
                            );
                            $creditChange = (int)$creditRule['credit'];
                        } elseif ((int)$creditRule['credit'] < 0 && $creditDid > 0) {
                            $creditChange = (int)$creditRule['credit'];
                        }

                        Database::commit();
                        $inTransaction = false;
                    } catch (\Throwable $e) {
                        if ($inTransaction) {
                            Database::rollBack();
                        }
                        $error = $e instanceof \RuntimeException ? $e->getMessage() : '私信发送失败，请稍后重试';
                    }

                    if ($error) {
                        if (Response::isAjaxRequest()) {
                            Response::json(['success' => false, 'message' => $error], 400);
                        }
                        Template::set('title', '发送私信');
                        Template::set('toUid', $toUid);
                        Template::set('receiver', $receiver);
                        Template::set('username', $username);
                        Template::set('error', $error);
                        Template::set('user', Session::getUser());
                        Template::set('member', Session::getUser());
                        Template::set('memberGroup', UsergroupModel::get((int)(Session::getUser()['gid'] ?? 0)));
                        Template::set('isSelf', true);
                        Template::display('pm/send');
                        return;
                    }

                    if (Response::isAjaxRequest()) {
                        Response::json([
                            'success' => true,
                            'message' => [
                                'pmid' => $pmid,
                                'uid' => Session::getUid(),
                                'content' => $content,
                                'dateline' => time(),
                            ],
                            'credit_change' => $creditChange,
                        ]);
                    }
                    Response::redirect('index.php?c=pm&a=view&uid=' . $toUid);
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error !== '' && Response::isAjaxRequest()) {
            Response::json(['success' => false, 'message' => $error], 400);
        }

        Template::set('title', '发送私信');
        Template::set('toUid', $toUid);
        Template::set('receiver', $receiver);
        Template::set('username', $receiver['username'] ?? '');
        Template::set('error', $error);
        Template::set('user', Session::getUser());
        Template::set('member', Session::getUser());
        Template::set('memberGroup', UsergroupModel::get((int)(Session::getUser()['gid'] ?? 0)));
        Template::set('isSelf', true);
        Template::display('pm/send');
    }

    public static function view(): void {
        Template::clear();
        Permission::requireLogin();

        $partnerUid = Request::getInt('uid');
        if (!$partnerUid || $partnerUid === Session::getUid()) {
            Response::redirect('index.php?c=pm&a=inbox');
        }

        $partner = MemberModel::get($partnerUid);
        if (!$partner) {
            Response::redirect('index.php?c=pm&a=inbox');
        }

        $uid = Session::getUid();
        $page = Request::getInt('page');
        $dialog = PmModel::getDialog($uid, $partnerUid);
        $total = (int)($dialog['pm_num'] ?? 0);
        $pages = max(1, (int)ceil($total / PmModel::PAGE_SIZE));
        $page = $page > 0 ? min($page, $pages) : $pages;
        $messages = PmModel::getConversation($uid, $partnerUid, $page);
        $unreadNum = Request::getInt('unread', (int)($dialog['unread_num'] ?? 0));
        PmModel::markConversationAsRead(Session::getUid(), $partnerUid);

        Template::set('title', '私信');
        Template::set('messages', $messages);
        Template::set('unreadNum', $unreadNum);
        Template::set('total', $total);
        Template::set('page', $page);
        Template::set('pages', $pages);
        Template::set('pageSize', PmModel::PAGE_SIZE);
        Template::set('lastPmid', (int)($dialog['last_pmid'] ?? 0));
        Template::set('partner', $partner);
        Template::set('user', Session::getUser());
        Template::set('member', Session::getUser());
        Template::set('memberGroup', UsergroupModel::get((int)(Session::getUser()['gid'] ?? 0)));
        Template::set('isSelf', true);
        Template::display('pm/view', 'layout/plain');
    }

    public static function poll(int $uid): void {
        Permission::requireLogin();
        $after = Request::getInt('after');
        $messages = PmModel::getConversationAfter(Session::getUid(), $uid, $after);
        if (!empty($messages)) {
            PmModel::markConversationAsRead(Session::getUid(), $uid);
        }
        Response::json(['success' => true, 'messages' => $messages]);
    }
}
?>
