<?php
class ThreadModel {
    const TABLE = 'next_thread';
    const PRIMARY_KEY = 'tid';

    public static function getThreads($fid, $page = 1, $order = 'reply_time', $keyword = '') {
        $offset = ($page - 1) * 20;
        
        $orderMap = [
            'reply_time' => 'reply_time DESC',
            'dateline' => 'dateline DESC',
            'reply_num' => 'reply_num DESC',
            'view_num' => 'view_num DESC'
        ];
        
        $orderBy = isset($orderMap[$order]) ? $orderMap[$order] : 'reply_time DESC';
        
        if (!empty($keyword)) {
            return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE fid = ? AND subject LIKE ? ORDER BY $orderBy LIMIT 20 OFFSET ?", [$fid, '%' . $keyword . '%', $offset]);
        }
        
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE fid = ? ORDER BY $orderBy LIMIT 20 OFFSET ?", [$fid, $offset]);
    }
    
    public static function getThreadCount($fid, $keyword = '') {
        if (!empty($keyword)) {
            $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE fid = ? AND subject LIKE ?", [$fid, '%' . $keyword . '%']);
        } else {
            $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE fid = ?", [$fid]);
        }
        return $result['count'] ?? 0;
    }

    public static function get($tid) {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = ?", [$tid]);
    }

    public static function getUserThreads($uid, $page = 1) {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE uid = ? ORDER BY tid DESC LIMIT 20 OFFSET ?", [$uid, $offset]);
    }

    public static function getUserThreadCount($uid) {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = ?", [$uid]);
        return $result['count'] ?? 0;
    }

    public static function create($data) {
        $data['dateline'] = time();
        $data['reply_time'] = time();
        $data['hash'] = md5(uniqid());
        return Database::insert(self::TABLE, $data);
    }

    public static function update($tid, $data) {
        return Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = ?", [$tid]);
    }

    public static function delete($tid) {
        return Database::delete(self::TABLE, self::PRIMARY_KEY . " = ?", [$tid]);
    }

    public static function count() {
        return Database::count(self::TABLE);
    }

    public static function updateReply($tid, $uid) {
        Database::query("UPDATE " . self::TABLE . " SET reply_time = ?, reply_uid = ?, reply_num = reply_num + 1 WHERE tid = ?", [time(), $uid, $tid]);
    }

    public static function incrementView($tid) {
        Database::query("UPDATE " . self::TABLE . " SET view_num = view_num + 1 WHERE tid = ?", [$tid]);
    }

    public static function search($whereStr = '', $params = [], $page = 1) {
        $offset = ($page - 1) * 20;
        $sql = "SELECT * FROM " . self::TABLE . " $whereStr ORDER BY dateline DESC LIMIT 20 OFFSET ?";
        $params[] = $offset;
        return Database::fetchAll($sql, $params);
    }

    public static function searchCount($whereStr = '', $params = []) {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " $whereStr", $params);
        return $result['count'] ?? 0;
    }

    public static function getHomeThreads($limit = 30) {
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY tid DESC LIMIT ?", [$limit]);
    }

    public static function getHomeThreadsWithFilter($page = 1, $order = 'reply_time', $keyword = '') {
        $offset = ($page - 1) * 20;
        
        $orderMap = [
            'reply_time' => 'reply_time DESC',
            'dateline' => 'dateline DESC',
            'reply_num' => 'reply_num DESC',
            'view_num' => 'view_num DESC'
        ];
        
        $orderBy = isset($orderMap[$order]) ? $orderMap[$order] : 'reply_time DESC';
        
        if (!empty($keyword)) {
            return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE subject LIKE ? ORDER BY $orderBy LIMIT 20 OFFSET ?", ['%' . $keyword . '%', $offset]);
        }
        
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY $orderBy LIMIT 20 OFFSET ?", [$offset]);
    }

    public static function getHomeThreadCount($keyword = '') {
        if (!empty($keyword)) {
            $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE subject LIKE ?", ['%' . $keyword . '%']);
        } else {
            $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE);
        }
        return $result['count'] ?? 0;
    }

    public static function getThreadsByTids($tids) {
        $tids = array_values(array_filter(array_unique(array_map('intval', $tids))));
        if (empty($tids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($tids), '?'));
        $sql = "SELECT tid, subject, fid, reply_num, view_num, dateline FROM " . self::TABLE . " WHERE tid IN ($placeholders)";
        $threads = Database::fetchAll($sql, $tids);

        return array_column($threads, null, 'tid');
    }
}
?>
