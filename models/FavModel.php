<?php
class FavModel {
    const TABLE = 'next_fav';
    const PRIMARY_KEY = 'tid';

    public static function getUserFavorites($uid, $page = 1) {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY dateline DESC LIMIT 20 OFFSET :offset", ['uid' => $uid, 'offset' => $offset]);
    }

    public static function getUserFavoriteCount($uid) {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = :uid", ['uid' => $uid]);
        return $result['count'] ?? 0;
    }

    public static function addFavorite($uid, $tid) {
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

    public static function removeFavorite($uid, $tid) {
        Database::query("DELETE FROM " . self::TABLE . " WHERE uid = :uid AND tid = :tid", ['uid' => $uid, 'tid' => $tid]);
        Database::query("UPDATE next_thread SET fav_num = fav_num - 1 WHERE tid = :tid", ['tid' => $tid]);
    }

    public static function isFavorite($uid, $tid) {
        $result = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE uid = :uid AND tid = :tid", ['uid' => $uid, 'tid' => $tid]);
        return !empty($result);
    }
}
?>
