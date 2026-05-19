<?php
class MemberModel {
    const TABLE = 'next_member';
    const PRIMARY_KEY = 'uid';

    public static function get($uid) {
        if (!$uid) {
            return null;
        }
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :uid", ['uid' => $uid]);
    }

    public static function getByUsername($username) {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE username = :username", ['username' => $username]);
    }

    public static function getByEmail($email) {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE email = :email", ['email' => $email]);
    }

    public static function checkPassword($username, $password) {
        $member = self::getByUsername($username);
        if (!$member) {
            return false;
        }
        return password_verify($password, $member['password']);
    }

    public static function register($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['reg_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['reg_date'] = time();
        $data['auth_secret'] = md5(uniqid());
        return Database::insert(self::TABLE, $data);
    }

    public static function search($keyword = '', $gid = 0, $page = 1) {
        $offset = ($page - 1) * 20;
        $where = [];
        $params = [];

        if ($keyword) {
            $where[] = '(username LIKE :keyword1 OR email LIKE :keyword2)';
            $params['keyword1'] = "%$keyword%";
            $params['keyword2'] = "%$keyword%";
        }
        if ($gid) {
            $where[] = 'gid = :gid';
            $params['gid'] = $gid;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params['offset'] = $offset;

        return Database::fetchAll("SELECT * FROM " . self::TABLE . " $whereStr ORDER BY uid DESC LIMIT 20 OFFSET :offset", $params);
    }

    public static function searchCount($keyword = '', $gid = 0) {
        $where = [];
        $params = [];

        if ($keyword) {
            $where[] = '(username LIKE :keyword1 OR email LIKE :keyword2)';
            $params['keyword1'] = "%$keyword%";
            $params['keyword2'] = "%$keyword%";
        }
        if ($gid) {
            $where[] = 'gid = :gid';
            $params['gid'] = $gid;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " $whereStr", $params);
        return $result['count'] ?? 0;
    }

    public static function count() {
        return Database::count(self::TABLE);
    }

    public static function update($uid, $data) {
        $data['uid'] = $uid;
        return Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :uid");
    }

    public static function delete($uid) {
        return Database::delete(self::TABLE, self::PRIMARY_KEY . " = :uid", ['uid' => $uid]);
    }

    public static function getJsonData($uid) {
        $member = self::get($uid);
        if (!$member) {
            return [];
        }
        $jsonData = $member['json_data'] ?? '{}';
        return json_decode($jsonData, true) ?: [];
    }

    public static function setJsonData($uid, $key, $value) {
        $jsonData = self::getJsonData($uid);
        $jsonData[$key] = $value;
        return self::update($uid, ['json_data' => json_encode($jsonData)]);
    }

    public static function getJsonField($uid, $key, $default = null) {
        $jsonData = self::getJsonData($uid);
        return $jsonData[$key] ?? $default;
    }


    public static function getMembersByUids($uids) {
        if (empty($uids)) {
            return [];
        }

        $uids = array_values(array_filter(array_unique($uids)));
        if (empty($uids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($uids), '?'));
        $sql = "SELECT uid, username, avatar FROM " . self::TABLE . " WHERE uid IN ($placeholders)";
        $members = Database::fetchAll($sql, $uids);

        $result = [];
        foreach ($members as $member) {
            $result[$member['uid']] = $member;
        }

        return $result;
    }
}
?>
