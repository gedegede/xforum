<?php
declare(strict_types=1);

namespace Models;

use Lib\Database;

class PostModel {
    const TABLE = 'next_post';
    const PRIMARY_KEY = 'pid';

    public static function getPosts(int $tid, int $page = 1): array {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE tid = :tid ORDER BY pid ASC LIMIT 20 OFFSET :offset", ['tid' => $tid, 'offset' => $offset]);
    }

    public static function get(int $pid): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :pid", ['pid' => $pid]);
    }

    public static function getPostCount(int $tid): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE tid = :tid", ['tid' => $tid]);
        return (int)($result['count'] ?? 0);
    }

    public static function getUserPosts(int $uid, int $page = 1): array {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE uid = :uid AND is_thread = 0 ORDER BY pid DESC LIMIT 20 OFFSET :offset", ['uid' => $uid, 'offset' => $offset]);
    }

    public static function getUserPostCount(int $uid): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = :uid AND is_thread = 0", ['uid' => $uid]);
        return (int)($result['count'] ?? 0);
    }

    public static function create(array $data): int {
        $data['dateline'] = time();
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        return Database::insert(self::TABLE, $data);
    }

    public static function deleteByTid(int $tid): void {
        Database::query("DELETE FROM " . self::TABLE . " WHERE tid = :tid", ['tid' => $tid]);
    }

    public static function getLastPostByTid(int $tid): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE tid = :tid ORDER BY pid DESC LIMIT 1", ['tid' => $tid]);
    }

    public static function getPostFloor(int $pid): int {
        $post = self::get($pid);
        if (!$post) {
            return 0;
        }

        $count = Database::fetch(
            "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE tid = :tid AND pid <= :pid",
            ['tid' => $post['tid'], 'pid' => $pid]
        );

        return (int)($count['count'] ?? 0);
    }

    public static function update(int $pid, array $data): int {
        $data['pid'] = $pid;
        $data['edited'] = time();
        return Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :pid");
    }

    public static function delete(int $pid): int {
        return Database::delete(self::TABLE, self::PRIMARY_KEY . " = :pid", ['pid' => $pid]);
    }
}
?>