<?php
declare(strict_types=1);

namespace Models;

use Lib\Database;

class FavModel {
    const TABLE = 'next_fav';
    const PRIMARY_KEY = 'tid';

    public static function getUserFavorites(int $uid, int $page = 1): array {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY dateline DESC LIMIT 20 OFFSET :offset", ['uid' => $uid, 'offset' => $offset]);
    }

    public static function getUserFavoriteCount(int $uid): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = :uid", ['uid' => $uid]);
        return (int)($result['count'] ?? 0);
    }

    public static function addFavorite(int $uid, int $tid): int {
        $result = Database::insert(self::TABLE, [
            'uid' => $uid,
            'tid' => $tid,
            'dateline' => time(),
        ]);
        if ($result) {
            Database::query("UPDATE next_thread SET fav_num = fav_num + 1 WHERE tid = :tid", ['tid' => $tid]);
        }
        return $result;
    }

    public static function removeFavorite(int $uid, int $tid): void {
        Database::query("DELETE FROM " . self::TABLE . " WHERE uid = :uid AND tid = :tid", ['uid' => $uid, 'tid' => $tid]);
        Database::query("UPDATE next_thread SET fav_num = fav_num - 1 WHERE tid = :tid", ['tid' => $tid]);
    }

    public static function isFavorite(int $uid, int $tid): bool {
        $result = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE uid = :uid AND tid = :tid", ['uid' => $uid, 'tid' => $tid]);
        return !empty($result);
    }
}
?>