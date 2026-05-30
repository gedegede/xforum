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

    public static function getUserFavorites(int $uid, int $page = 1, int $pageSize = self::PAGE_SIZE): array {
        return Database::fetchFilteredPage(
            "SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY dateline DESC LIMIT :limit OFFSET :offset",
            ['uid' => $uid],
            static function (array $favorite): bool {
                return true;
            },
            $page,
            $pageSize,
            self::FILTER_BATCH_SIZE
        );
    }

    public static function getUserFavoriteCount(int $uid): int {
        $member = MemberModel::get($uid);
        return (int)($member['fav_num'] ?? 0);
    }

    public static function addFavorite(int $uid, int $tid): int {
        if ($uid <= 0 || $tid <= 0) {
            return 0;
        }

        $stmt = Database::query(
            "INSERT INTO " . self::TABLE . " (`uid`, `tid`, `dateline`) VALUES (:uid, :tid, :dateline)",
            ['uid' => $uid, 'tid' => $tid, 'dateline' => time()]
        );
        $inserted = $stmt->rowCount();
        if ($inserted > 0) {
            ThreadModel::incrementFavNum($tid);
            MemberModel::incrementFavNum($uid);
        }
        return $inserted;
    }

    public static function removeFavorite(int $uid, int $tid): bool {
        $deleted = Database::delete(
            self::TABLE,
            'uid = :uid AND tid = :tid',
            ['uid' => $uid, 'tid' => $tid]
        );
        if ($deleted > 0) {
            ThreadModel::decrementFavNum($tid);
            MemberModel::decrementFavNum($uid);
            return true;
        }
        return false;
    }

    public static function isFavorite(int $uid, int $tid): bool {
        $result = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE tid = :tid AND uid = :uid", ['tid' => $tid, 'uid' => $uid]);
        return !empty($result);
    }
}
?>
