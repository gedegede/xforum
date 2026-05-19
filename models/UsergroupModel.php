<?php
class UsergroupModel {
    const TABLE = 'next_usergroup';
    const PRIMARY_KEY = 'gid';

    public static function getAll() {
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY gid ASC");
    }

    public static function get($gid) {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE gid = ?", [$gid]);
    }

    public static function getDefaultGroup() {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE group_type = 'member' ORDER BY gid ASC LIMIT 1");
    }

    public static function create($data) {
        return Database::insert(self::TABLE, $data);
    }

    public static function update($gid, $data) {
        return Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = ?", [$gid]);
    }

    public static function delete($gid) {
        return Database::delete(self::TABLE, self::PRIMARY_KEY . " = ?", [$gid]);
    }
}
?>