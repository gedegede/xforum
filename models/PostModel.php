<?php
class PostModel {
    const TABLE = 'next_post';
    const PRIMARY_KEY = 'pid';

    public static function getPosts($tid, $page = 1) {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE tid = ? ORDER BY pid ASC LIMIT 20 OFFSET ?", [$tid, $offset]);
    }

    public static function get($pid) {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = ?", [$pid]);
    }

    public static function getPostCount($tid) {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE tid = ?", [$tid]);
        return $result['count'] ?? 0;
    }

    public static function getUserPosts($uid, $page = 1) {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE uid = ? AND is_thread = 0 ORDER BY pid DESC LIMIT 20 OFFSET ?", [$uid, $offset]);
    }

    public static function getUserPostCount($uid) {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = ? AND is_thread = 0", [$uid]);
        return $result['count'] ?? 0;
    }

    public static function create($data) {
        $data['dateline'] = time();
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        return Database::insert(self::TABLE, $data);
    }

    public static function deleteByTid($tid) {
        Database::query("DELETE FROM " . self::TABLE . " WHERE tid = ?", [$tid]);
    }

    public static function getLastPostByTid($tid) {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE tid = ? ORDER BY pid DESC LIMIT 1", [$tid]);
    }

    /**
     * 获取帖子的楼层号（根据 pid 小于当前帖子 pid 的数量计算）
     *
     * @param int $pid 帖子 ID
     * @return int 楼层号
     */
    public static function getPostFloor($pid) {
        $post = self::get($pid);
        if (!$post) {
            return 0;
        }

        $count = Database::fetch(
            "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE tid = ? AND pid <= ?",
            [$post['tid'], $pid]
        );

        return $count['count'] ?? 0;
    }
}
?>