<?php
declare(strict_types=1);

namespace Models;

use Lib\Database;

class UsergroupModel {
    const TABLE = 'next_usergroup';
    const PRIMARY_KEY = 'gid';

    public static function getAll(): array {
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY gid ASC");
    }

    public static function get(int $gid): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :gid", ['gid' => $gid]);
    }

    public static function getDefaultGroup(): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE group_type = :group_type ORDER BY gid ASC LIMIT 1", ['group_type' => 'member']);
    }

    public static function create(array $data): int {
        return Database::insert(self::TABLE, $data);
    }

    public static function update(int $gid, array $data): int {
        $data['gid'] = $gid;
        return Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :gid");
    }

    public static function delete(int $gid): int {
        return Database::delete(self::TABLE, self::PRIMARY_KEY . " = :gid", ['gid' => $gid]);
    }
}
?>