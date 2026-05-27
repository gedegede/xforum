<?php
define('ROOT_PATH', dirname(__FILE__));

$config = require ROOT_PATH . '/config/database.php';
$cfg = $config['connections']['mysql'];

try {
    $db = new PDO(
        "mysql:host={$cfg['host']};charset={$cfg['charset']}",
        $cfg['username'],
        $cfg['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['database']}` DEFAULT CHARACTER SET {$cfg['charset']} COLLATE {$cfg['collation']}");
    $db->exec("USE `{$cfg['database']}`");

    $db->exec("SET NAMES {$cfg['charset']} COLLATE {$cfg['collation']}");
} catch (PDOException $e) {
    exit("数据库连接失败: " . $e->getMessage() . "\n");
}

$dropTables = [
    'next_rate', 'next_thread_tag', 'next_threadtype', 'next_thread',
    'next_post', 'next_pm', 'next_notify', 'next_moderator', 'next_mod_log',
    'next_member', 'next_fav', 'next_credit', 'next_action', 'next_data',
    'next_setting', 'next_session', 'next_forum', 'next_usergroup'
];

echo "清理旧表...\n";
foreach ($dropTables as $name) {
    $db->exec("DROP TABLE IF EXISTS `{$name}`");
}

$tables = [
    'next_action' => "CREATE TABLE `next_action` (
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `action` CHAR(15) NOT NULL DEFAULT '',
        `num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (`uid`, `action`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",

    'next_credit' => "CREATE TABLE `next_credit` (
        `did` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `credit` INTEGER NOT NULL DEFAULT 0,
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `message` TEXT NOT NULL,
        `url` VARCHAR(255) NOT NULL DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",
    'next_credit_idx_uid_did' => "CREATE INDEX `idx_next_credit_uid_did` ON `next_credit`(`uid`, `did` DESC)",

    'next_data' => "CREATE TABLE `next_data` (
        `dkey` VARCHAR(32) NOT NULL DEFAULT '',
        `val` TEXT NOT NULL,
        PRIMARY KEY (`dkey`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",

    'next_fav' => "CREATE TABLE `next_fav` (
        `tid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (`tid`, `uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",
    'next_fav_idx_uid_dateline' => "CREATE INDEX `idx_next_fav_uid_dateline` ON `next_fav`(`uid`, `dateline` DESC)",

    'next_forum' => "CREATE TABLE `next_forum` (
        `fid` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `up_fid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `name` VARCHAR(50) NOT NULL DEFAULT '',
        `status` INTEGER NOT NULL DEFAULT 0,
        `sort_order` INTEGER NOT NULL DEFAULT 0,
        `thread_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `reply_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `today_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `today_time` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `last_tid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `json_data` TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",

    'next_member' => "CREATE TABLE `next_member` (
        `uid` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `username` VARCHAR(50) NOT NULL DEFAULT '',
        `gid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `avatar` VARCHAR(255) NOT NULL DEFAULT '',
        `password` VARCHAR(255) NOT NULL DEFAULT '',
        `auth_secret` VARCHAR(50) NOT NULL DEFAULT '',
        `auth_enabled` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `notify_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `credit` INTEGER NOT NULL DEFAULT 0,
        `reg_ip` VARCHAR(50) NOT NULL DEFAULT '',
        `reg_date` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `last_ip` VARCHAR(50) NOT NULL DEFAULT '',
        `last_visit` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `reply_time` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `reply_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `thread_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `fav_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `inbox_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `outbox_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `log_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `email` VARCHAR(80) NOT NULL DEFAULT '',
        `email_status` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `signin_time` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `invisible` INTEGER NOT NULL DEFAULT 0,
        `timeoffset` VARCHAR(10) NOT NULL DEFAULT '',
        `search_time` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `status` INTEGER NOT NULL DEFAULT 0,
        `json_data` TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",
    'next_member_idx_username' => "CREATE INDEX `idx_next_member_username` ON `next_member`(`username`)",
    'next_member_idx_email' => "CREATE INDEX `idx_next_member_email` ON `next_member`(`email`)",
    'next_member_idx_gid' => "CREATE INDEX `idx_next_member_gid` ON `next_member`(`gid`)",

    'next_mod_log' => "CREATE TABLE `next_mod_log` (
        `did` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `tid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `pid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `from_uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `message` TEXT NOT NULL,
        `post_message` TEXT NOT NULL,
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",

    'next_moderator' => "CREATE TABLE `next_moderator` (
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `fid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `end_date` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `sort_order` INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (`uid`, `fid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",

    'next_notify' => "CREATE TABLE `next_notify` (
        `did` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `from_uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `fid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `tid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `pid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `status` INTEGER NOT NULL DEFAULT 0,
        `message` VARCHAR(255) NOT NULL DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",
    'next_notify_idx_uid_did' => "CREATE INDEX `idx_next_notify_uid_did` ON `next_notify`(`uid`, `did` DESC)",
    'next_notify_idx_uid_tid' => "CREATE INDEX `idx_next_notify_uid_tid` ON `next_notify`(`uid`, `tid`)",

    'next_pm' => "CREATE TABLE `next_pm` (
        `pmid` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `to_uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `content` TEXT NOT NULL,
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `status` INTEGER NOT NULL DEFAULT 0,
        `is_read` INTEGER NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",
    'next_pm_idx_to_uid_pmid' => "CREATE INDEX `idx_next_pm_to_uid_pmid` ON `next_pm`(`to_uid`, `pmid` DESC)",
    'next_pm_idx_uid_pmid' => "CREATE INDEX `idx_next_pm_uid_pmid` ON `next_pm`(`uid`, `pmid` DESC)",
    'next_pm_idx_to_uid_is_read' => "CREATE INDEX `idx_next_pm_to_uid_is_read` ON `next_pm`(`to_uid`, `is_read`)",

    'next_post' => "CREATE TABLE `next_post` (
        `pid` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `fid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `tid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `is_thread` INTEGER NOT NULL DEFAULT 0,
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `edited` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `report_time` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `message` TEXT NOT NULL,
        `ip` VARCHAR(50) NOT NULL DEFAULT '',
        `rate_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `sort_order` INTEGER NOT NULL DEFAULT 0,
        `reply_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `quote_pid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `quote_uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `quote_floor` INTEGER UNSIGNED NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",
    'next_post_idx_tid_pid' => "CREATE INDEX `idx_next_post_tid_pid` ON `next_post`(`tid`, `pid` ASC)",
    'next_post_idx_uid_pid' => "CREATE INDEX `idx_next_post_uid_pid` ON `next_post`(`uid`, `pid` DESC)",

    'next_rate' => "CREATE TABLE `next_rate` (
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `pid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (`uid`, `pid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",

    'next_session' => "CREATE TABLE `next_session` (
        `id` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `session_key` VARCHAR(64) NOT NULL DEFAULT '',
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `gid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `invisible` INTEGER NOT NULL DEFAULT 0,
        `fid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `tid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `ip` VARCHAR(50) NOT NULL DEFAULT '',
        UNIQUE KEY `session_key` (`session_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",
    'next_session_idx_dateline' => "CREATE INDEX `idx_next_session_dateline` ON `next_session`(`dateline` DESC)",

    'next_setting' => "CREATE TABLE `next_setting` (
        `skey` VARCHAR(32) NOT NULL DEFAULT '',
        `val` TEXT NOT NULL,
        PRIMARY KEY (`skey`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",

    'next_thread' => "CREATE TABLE `next_thread` (
        `tid` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `fid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `pid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `typeid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `read_perm` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `pm_uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `subject` VARCHAR(255) NOT NULL DEFAULT '',
        `hash` VARCHAR(255) NOT NULL DEFAULT '',
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `sort_order` INTEGER NOT NULL DEFAULT 0,
        `highlight` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `digest` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `closed` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `reply_time` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `reply_uid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `reply_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `view_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `fav_num` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `log_num` INTEGER UNSIGNED NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",
    'next_thread_idx_fid_reply' => "CREATE INDEX `idx_next_thread_fid_reply` ON `next_thread`(`fid`, `tid` DESC)",
    'next_thread_idx_uid_tid' => "CREATE INDEX `idx_next_thread_uid_tid` ON `next_thread`(`uid`, `tid` DESC)",

    'next_thread_tag' => "CREATE TABLE `next_thread_tag` (
        `tid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `up_tid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `dateline` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (`tid`, `up_tid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",

    'next_threadtype' => "CREATE TABLE `next_threadtype` (
        `typeid` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `fid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
        `sort_order` INTEGER NOT NULL DEFAULT 0,
        `name` VARCHAR(50) NOT NULL DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs",

    'next_usergroup' => "CREATE TABLE `next_usergroup` (
        `gid` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `group_type` VARCHAR(20) NOT NULL DEFAULT 'member',
        `title` VARCHAR(50) NOT NULL DEFAULT '',
        `credit_lower` INTEGER NOT NULL DEFAULT 0,
        `json_data` TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs"
];

echo "开始创建数据表...\n";
foreach ($tables as $name => $sql) {
    try {
        $db->exec($sql);
        echo "  ✓ {$name}\n";
    } catch (PDOException $e) {
        echo "  ✗ {$name}: " . $e->getMessage() . "\n";
    }
}

echo "\n写入初始数据...\n";

$db->exec("INSERT INTO `next_usergroup` (`gid`, `group_type`, `title`, `credit_lower`, `json_data`) VALUES (1, 'system', '管理员', 0, '{\"can_manage\":1,\"thread_need_approve\":0,\"post_need_approve\":0}')");
$db->exec("INSERT INTO `next_usergroup` (`gid`, `group_type`, `title`, `credit_lower`, `json_data`) VALUES (2, 'system', '普通会员', 0, '{\"can_manage\":0,\"thread_need_approve\":0,\"post_need_approve\":0}')");

$db->exec("INSERT INTO `next_member` (`uid`, `username`, `gid`, `password`, `auth_secret`, `reg_ip`, `reg_date`, `status`, `json_data`) VALUES (1, 'admin', 1, '" . password_hash('admin123', PASSWORD_DEFAULT) . "', '" . md5(uniqid()) . "', '127.0.0.1', " . time() . ", 0, '{}')");

$db->exec("INSERT INTO `next_forum` (`fid`, `name`, `status`, `sort_order`, `json_data`) VALUES (1, '综合讨论区', 1, 1, '{}')");
$db->exec("INSERT INTO `next_forum` (`fid`, `name`, `status`, `sort_order`, `json_data`) VALUES (2, '技术交流', 1, 2, '{}')");
$db->exec("INSERT INTO `next_forum` (`fid`, `name`, `status`, `sort_order`, `json_data`) VALUES (3, '休闲娱乐', 1, 3, '{}')");
$db->exec("INSERT INTO `next_forum` (`fid`, `name`, `status`, `sort_order`, `json_data`) VALUES (4, '举报中心', 1, 4, '{}')");

$db->exec("INSERT INTO `next_setting` (`skey`, `val`) VALUES ('report_forum_fid', '4')");
$db->exec("INSERT INTO `next_setting` (`skey`, `val`) VALUES ('collapsed_fids', '')");
$db->exec("INSERT INTO `next_setting` (`skey`, `val`) VALUES ('register_default_gid', '2')");
$db->exec("INSERT INTO `next_setting` (`skey`, `val`) VALUES ('timezone', 'Asia/Shanghai')");
$db->exec("INSERT INTO `next_setting` (`skey`, `val`) VALUES ('approve_keywords', '')");
$db->exec("INSERT INTO `next_setting` (`skey`, `val`) VALUES ('block_keywords', '')");
$db->exec("INSERT INTO `next_setting` (`skey`, `val`) VALUES ('signin_credit_range', '1,5')");
$db->exec("INSERT INTO `next_setting` (`skey`, `val`) VALUES ('credit_rules', 'ThreadCreate,1,10\nThreadReply,1,10\nPmSend,0,0\nThreadReport,0,0')");

echo "\n安装成功！管理员账号: admin，密码: admin123\n";
