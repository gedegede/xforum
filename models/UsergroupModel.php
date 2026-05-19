<?php
class UsergroupModel {
    const TABLE = 'next_usergroup';
    const PRIMARY_KEY = 'gid';

    public static function getAll() {
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY gid ASC");
    }

    public static function get($gid) {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :gid", ['gid' => $gid]);
    }

    public static function getDefaultGroup() {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE group_type = :group_type ORDER BY gid ASC LIMIT 1", ['group_type' => 'member']);
    }

    public static function create($data) {
        return Database::insert(self::TABLE, $data);
    }

    public static function update($gid, $data) {
        $data['gid'] = $gid;
        return Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :gid");
    }

    public static function delete($gid) {
        return Database::delete(self::TABLE, self::PRIMARY_KEY . " = :gid", ['gid' => $gid]);
    }
}
?>
