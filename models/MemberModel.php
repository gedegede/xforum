<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;

class MemberModel {
    const TABLE = 'next_member';
    const PRIMARY_KEY = 'uid';
    private const PAGE_SIZE = 20;
    private const FILTER_BATCH_SIZE = 100;

    private static $memoryCache = [];

    public static function get(int $uid): ?array {
        if (!$uid) {
            return null;
        }

        if (isset(self::$memoryCache[$uid])) {
            return self::$memoryCache[$uid];
        }

        $result = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :uid", ['uid' => $uid]);
        if ($result) {
            self::$memoryCache[$uid] = $result;
        }
        return $result;
    }

    public static function getByUsername(string $username): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE username = :username", ['username' => $username]);
    }

    public static function getByEmail(string $email): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE email = :email", ['email' => $email]);
    }

    public static function checkPassword(string $username, string $password): bool {
        $member = self::getByUsername($username);
        if (!$member) {
            return false;
        }
        return password_verify($password, $member['password']);
    }

    public static function register(array $data): int {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['reg_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['reg_date'] = time();
        $data['auth_secret'] = md5(uniqid());
        $data['json_data'] = '{}';
        return Database::insert(self::TABLE, $data);
    }

    public static function search(string $keyword = '', int $gid = 0, int $page = 1): array {
        [$sql, $params] = self::buildIndexedListQuery($gid);

        return Database::fetchFilteredPage(
            $sql,
            $params,
            static function (array $member) use ($keyword, $gid): bool {
                if ($gid > 0 && (int)$member['gid'] !== $gid) {
                    return false;
                }
                return $keyword === ''
                    || stripos((string)$member['username'], $keyword) !== false
                    || stripos((string)$member['email'], $keyword) !== false;
            },
            $page,
            self::PAGE_SIZE,
            self::FILTER_BATCH_SIZE
        );
    }

    public static function searchCount(string $keyword = '', int $gid = 0): int {
        if ($keyword === '' && $gid === 0) {
            return self::count();
        }

        [$sql, $params] = self::buildIndexedListQuery($gid, 'uid, username, email, gid');

        return Database::countFiltered(
            $sql,
            $params,
            static function (array $member) use ($keyword, $gid): bool {
                if ($gid > 0 && (int)$member['gid'] !== $gid) {
                    return false;
                }
                return $keyword === ''
                    || stripos((string)$member['username'], $keyword) !== false
                    || stripos((string)$member['email'], $keyword) !== false;
            }
        );
    }

    public static function count(): int {
        return Database::count(self::TABLE);
    }

    private static function buildIndexedListQuery(int $gid, string $columns = '*'): array {
        if ($gid > 0) {
            return [
                "SELECT {$columns} FROM " . self::TABLE . " WHERE gid = :gid ORDER BY uid DESC LIMIT :limit OFFSET :offset",
                ['gid' => $gid],
            ];
        }

        return [
            "SELECT {$columns} FROM " . self::TABLE . " ORDER BY uid DESC LIMIT :limit OFFSET :offset",
            [],
        ];
    }

    public static function update(int $uid, array $data): int {
        if (isset($data['username'])) {
            $existing = self::getByUsername($data['username']);
            if ($existing && (int)$existing['uid'] !== $uid) {
                throw new \RuntimeException('用户名已存在');
            }
        }
        if (isset($data['email'])) {
            $existing = self::getByEmail($data['email']);
            if ($existing && (int)$existing['uid'] !== $uid) {
                throw new \RuntimeException('邮箱已被使用');
            }
        }
        
        $result = Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :uid", ['uid' => $uid]);
        unset(self::$memoryCache[$uid]);
        if (array_key_exists('credit', $data)) {
            self::syncMemberGroupByCredit($uid);
        }
        return $result;
    }

    public static function changeCredit(int $uid, int $credit): bool {
        if ($uid <= 0 || $credit === 0) return false;
        if ($credit < 0) {
            $stmt = Database::query(
                "UPDATE " . self::TABLE . " SET credit = credit + :credit WHERE uid = :uid AND credit >= :cost",
                ['credit' => $credit, 'uid' => $uid, 'cost' => abs($credit)]
            );
        } else {
            $stmt = Database::query("UPDATE " . self::TABLE . " SET credit = credit + :credit WHERE uid = :uid", ['credit' => $credit, 'uid' => $uid]);
        }
        unset(self::$memoryCache[$uid]);
        $changed = $stmt->rowCount() > 0;
        if ($changed) {
            self::syncMemberGroupByCredit($uid);
        }
        return $changed;
    }

    private static function syncMemberGroupByCredit(int $uid): void {
        $member = self::get($uid);
        if (!$member) {
            return;
        }

        $groups = UsergroupModel::getAll();
        $currentGid = (int)($member['gid'] ?? 0);
        $currentGroup = $groups[$currentGid] ?? null;
        if (($currentGroup['group_type'] ?? '') !== 'member') {
            return;
        }

        $targetGroup = UsergroupModel::getMemberGroupByCredit((int)($member['credit'] ?? 0));
        if (!$targetGroup || (int)$targetGroup['gid'] === (int)$member['gid']) {
            return;
        }

        Database::query(
            "UPDATE " . self::TABLE . " SET gid = :gid WHERE uid = :uid",
            ['gid' => (int)$targetGroup['gid'], 'uid' => $uid]
        );
        unset(self::$memoryCache[$uid]);
    }

    public static function updateSigninTime(int $uid, int $time): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET signin_time = :time WHERE uid = :uid", ['time' => $time, 'uid' => $uid]);
        unset(self::$memoryCache[$uid]);
    }

    public static function delete(int $uid): int {
        $result = Database::delete(self::TABLE, self::PRIMARY_KEY . " = :uid", ['uid' => $uid]);
        if ($result && isset(self::$memoryCache[$uid])) {
            unset(self::$memoryCache[$uid]);
        }
        return $result;
    }

    public static function rebuildContentStats(int $uid): void {
        if ($uid <= 0) {
            return;
        }

        $threadCount = Database::count(ThreadModel::TABLE, 'uid = :uid AND sort_order >= 0', ['uid' => $uid]);
        $replyCount = Database::count(PostModel::TABLE, 'uid = :uid AND is_thread = 0 AND sort_order >= 0', ['uid' => $uid]);
        Database::update(self::TABLE, [
            'thread_num' => $threadCount,
            'reply_num' => $replyCount,
        ], self::PRIMARY_KEY . ' = :uid', ['uid' => $uid]);
        unset(self::$memoryCache[$uid]);
    }

    public static function getJsonData(int $uid): array {
        $member = self::get($uid);
        if (!$member) {
            return [];
        }
        $jsonData = $member['json_data'] ?? '{}';
        return json_decode($jsonData, true) ?: [];
    }

    public static function setJsonData(int $uid, string $key, mixed $value): int {
        $jsonData = self::getJsonData($uid);
        $jsonData[$key] = $value;
        return self::update($uid, ['json_data' => json_encode($jsonData)]);
    }

    public static function getJsonField(int $uid, string $key, mixed $default = null): mixed {
        $jsonData = self::getJsonData($uid);
        return $jsonData[$key] ?? $default;
    }


    public static function getMembersByUids(array $uids): array {
        if (empty($uids)) {
            return [];
        }

        $uids = array_values(array_filter(array_unique($uids)));
        if (empty($uids)) {
            return [];
        }

        $result = [];
        $missedUids = [];

        foreach ($uids as $uid) {
            if (isset(self::$memoryCache[$uid])) {
                $result[$uid] = self::$memoryCache[$uid];
            } else {
                $missedUids[] = $uid;
            }
        }

        if (!empty($missedUids)) {
            $placeholders = implode(',', array_fill(0, count($missedUids), '?'));
            $sql = "SELECT uid, username, avatar FROM " . self::TABLE . " WHERE uid IN ($placeholders)";
            $members = Database::fetchAll($sql, $missedUids);

            foreach ($members as $member) {
                self::$memoryCache[$member['uid']] = $member;
                $result[$member['uid']] = $member;
            }
        }

        return $result;
    }
    
    public static function incrementThreadNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET thread_num = thread_num + 1 WHERE uid = :uid", ['uid' => $uid]);
    }
    
    public static function decrementThreadNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET thread_num = CASE WHEN thread_num > 0 THEN thread_num - 1 ELSE 0 END WHERE uid = :uid", ['uid' => $uid]);
    }
    
    public static function incrementReplyNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET reply_num = reply_num + 1 WHERE uid = :uid", ['uid' => $uid]);
    }
    
    public static function decrementReplyNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET reply_num = CASE WHEN reply_num > 0 THEN reply_num - 1 ELSE 0 END WHERE uid = :uid", ['uid' => $uid]);
    }
    
    public static function incrementFavNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET fav_num = fav_num + 1 WHERE uid = :uid", ['uid' => $uid]);
    }
    
    public static function decrementFavNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET fav_num = CASE WHEN fav_num > 0 THEN fav_num - 1 ELSE 0 END WHERE uid = :uid", ['uid' => $uid]);
    }
    
    public static function incrementInboxNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET inbox_num = inbox_num + 1 WHERE uid = :uid", ['uid' => $uid]);
    }
    
    public static function decrementInboxNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET inbox_num = CASE WHEN inbox_num > 0 THEN inbox_num - 1 ELSE 0 END WHERE uid = :uid", ['uid' => $uid]);
    }
    
    public static function incrementNotifyNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET notify_num = notify_num + 1 WHERE uid = :uid", ['uid' => $uid]);
    }
    
    public static function decrementNotifyNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET notify_num = CASE WHEN notify_num > 0 THEN notify_num - 1 ELSE 0 END WHERE uid = :uid", ['uid' => $uid]);
    }
    
    public static function resetNotifyNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET notify_num = 0 WHERE uid = :uid", ['uid' => $uid]);
    }

    public static function resetInboxNum(int $uid): void {
        if ($uid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET inbox_num = 0 WHERE uid = :uid", ['uid' => $uid]);
    }
}
?>
