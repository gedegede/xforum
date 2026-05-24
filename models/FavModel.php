<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Models\MemberModel;
use Models\ThreadModel;

class FavModel {
    const TABLE = 'next_fav';
    const PRIMARY_KEY = 'tid';
    private const PAGE_SIZE = 20;
    private const FILTER_BATCH_SIZE = 100;

    public static function getUserFavorites(int $uid, int $page = 1): array {
        return Database::fetchFilteredPage(
            "SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY dateline DESC LIMIT :limit OFFSET :offset",
            ['uid' => $uid],
            static function (array $favorite): bool {
                return true;
            },
            $page,
            self::PAGE_SIZE,
            self::FILTER_BATCH_SIZE
        );
    }

    public static function getUserFavoriteCount(int $uid): int {
        $member = MemberModel::get($uid);
        return (int)($member['fav_num'] ?? 0);
    }

    public static function addFavorite(int $uid, int $tid): int {
        $result = Database::insert(self::TABLE, [
            'uid' => $uid,
            'tid' => $tid,
            'dateline' => time(),
        ]);
        if ($result) {
            ThreadModel::incrementFavNum($tid);
            MemberModel::incrementFavNum($uid);
        }
        return $result;
    }

    public static function removeFavorite(int $uid, int $tid): void {
        Database::query("DELETE FROM " . self::TABLE . " WHERE uid = :uid AND tid = :tid", ['uid' => $uid, 'tid' => $tid]);
        ThreadModel::decrementFavNum($tid);
        MemberModel::decrementFavNum($uid);
    }

    public static function isFavorite(int $uid, int $tid): bool {
        $result = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE tid = :tid AND uid = :uid", ['tid' => $tid, 'uid' => $uid]);
        return !empty($result);
    }
}
?>
