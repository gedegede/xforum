<?php
declare(strict_types=1);

namespace Controllers;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\Permission;
use Lib\Request;
use Lib\Response;
use Models\ForumModel;
use Models\PostModel;
use Models\ThreadModel;

class MiniappController {
    public static function forums(): void {
        $forums = [];
        foreach (ForumModel::getForumsFlat() as $forum) {
            $fid = (int)($forum['fid'] ?? 0);
            if (!$fid || !Permission::canViewForum($fid)) {
                continue;
            }
            $forums[] = [
                'fid' => $fid,
                'name' => (string)($forum['name'] ?? ''),
                'thread_num' => (int)($forum['thread_num'] ?? 0),
            ];
        }
        Response::json(['success' => true, 'forums' => $forums]);
    }

    public static function threads(): void {
        $fid = Request::getInt('fid');
        $page = max(1, Request::getInt('page', 1));
        $pageSize = min(30, max(1, Request::getInt('page_size', 20)));
        if ($fid <= 0 || !Permission::canViewForum($fid)) {
            Response::error('无权访问', 403);
        }

        $threads = ThreadModel::getThreads($fid, $page, 'dateline', '', $pageSize);
        Response::json(['success' => true, 'threads' => self::formatThreads($threads)]);
    }

    public static function thread(): void {
        $tid = Request::getInt('tid');
        $thread = ThreadModel::get($tid);
        if (!$thread || (int)($thread['sort_order'] ?? 0) < 0 || !Permission::canViewForum((int)$thread['fid'])) {
            Response::error('主题不存在', 404);
        }

        $post = PostModel::getThreadPost($tid);
        Response::json([
            'success' => true,
            'thread' => [
                'tid' => (int)$thread['tid'],
                'fid' => (int)$thread['fid'],
                'subject' => (string)$thread['subject'],
                'reply_num' => (int)($thread['reply_num'] ?? 0),
                'view_num' => (int)($thread['view_num'] ?? 0),
            ],
            'content' => self::plainText((string)($post['message'] ?? '')),
        ]);
    }

    public static function home(): void {
        $fids = [];
        foreach (ForumModel::getForumsFlat() as $forum) {
            $fid = (int)($forum['fid'] ?? 0);
            if ($fid > 0 && Permission::canViewForum($fid)) {
                $fids[] = $fid;
            }
        }
        if (empty($fids)) {
            Response::json(['success' => true, 'threads' => []]);
        }

        $placeholders = implode(',', array_fill(0, count($fids), '?'));
        $threads = Database::fetchAll(
            'SELECT tid, fid, uid, subject, reply_num, view_num, dateline, sort_order FROM ' . ThreadModel::TABLE . " WHERE fid IN ($placeholders) AND sort_order >= 0 ORDER BY tid DESC LIMIT 20",
            $fids
        );
        Response::json(['success' => true, 'threads' => self::formatThreads($threads)]);
    }

    private static function formatThreads(array $threads): array {
        $result = [];
        foreach ($threads as $thread) {
            if ((int)($thread['sort_order'] ?? 0) < 0) {
                continue;
            }
            $result[] = [
                'tid' => (int)$thread['tid'],
                'fid' => (int)$thread['fid'],
                'subject' => (string)$thread['subject'],
                'reply_num' => (int)($thread['reply_num'] ?? 0),
                'view_num' => (int)($thread['view_num'] ?? 0),
            ];
        }
        return $result;
    }

    private static function plainText(string $text): string {
        $text = preg_replace('/```.*?```/s', '', $text) ?? '';
        $text = preg_replace('/!\[[^\]]*]\([^)]+\)/', '', $text) ?? $text;
        $text = preg_replace('/\[([^\]]+)]\([^)]+\)/', '$1', $text) ?? $text;
        $text = preg_replace('/[#>*_`~\-\[\]]+/', '', $text) ?? $text;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
        return trim((string)preg_replace('/\s+/u', "\n", $text));
    }
}
?>
