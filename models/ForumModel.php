<?php
class ForumModel {
    const TABLE = 'next_forum';
    const PRIMARY_KEY = 'fid';

    public static function getForums($upFid = null) {
        if ($upFid !== null) {
            $forums = Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE up_fid = ? ORDER BY sort_order ASC", [$upFid]);
        } else {
            $forums = Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY up_fid ASC, sort_order ASC");
        }

        if (empty($forums)) {
            return [];
        }

        $parentFids = array_unique(array_column($forums, 'up_fid'));
        $parentFids = array_filter($parentFids, function($fid) { return $fid !== null && $fid !== ''; });
        $parentNames = [];
        if (!empty($parentFids)) {
            $parentNames = self::getForumNamesByFids($parentFids);
        }

        foreach ($forums as &$forum) {
            $forum['parent_name'] = $parentNames[$forum['up_fid']] ?? '';
        }

        if ($upFid === null) {
            $tree = self::buildTree($forums);
            self::addDepth($tree, 0);
            return $tree;
        }

        return $forums;
    }

    private static function getForumNamesByFids($fids) {
        if (empty($fids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($fids), '?'));
        $sql = "SELECT fid, name FROM " . self::TABLE . " WHERE fid IN ($placeholders)";
        $results = Database::fetchAll($sql, $fids);

        return array_column($results, 'name', 'fid');
    }

    private static function buildTree($forums) {
        $tree = [];
        $map = [];

        foreach ($forums as $forum) {
            $map[$forum['fid']] = $forum;
            $map[$forum['fid']]['children'] = [];
        }

        foreach ($forums as $forum) {
            $fid = $forum['fid'];
            $upFid = $forum['up_fid'];

            if ($upFid && isset($map[$upFid])) {
                $map[$upFid]['children'][] = &$map[$fid];
            } else {
                $tree[] = &$map[$fid];
            }
        }

        return $tree;
    }

    private static function addDepth(&$forums, $depth) {
        if (!is_array($forums)) {
            return;
        }
        foreach ($forums as &$forum) {
            $forum['depth'] = $depth;
            if (!empty($forum['children'])) {
                self::addDepth($forum['children'], $depth + 1);
            }
        }
    }

    public static function get($fid) {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE fid = ?", [$fid]);
    }

    public static function getForumName($fid) {
        $result = Database::fetch("SELECT name FROM " . self::TABLE . " WHERE fid = ?", [$fid]);
        return $result['name'] ?? '';
    }

    public static function create($data) {
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = 0;
        }
        if (!isset($data['json_data'])) {
            $data['json_data'] = '{}';
        }
        return Database::insert(self::TABLE, $data);
    }

    public static function update($fid, $data) {
        return Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = ?", [$fid]);
    }

    public static function delete($fid) {
        return Database::delete(self::TABLE, self::PRIMARY_KEY . " = ?", [$fid]);
    }

    public static function count() {
        return Database::count(self::TABLE);
    }
}
?>