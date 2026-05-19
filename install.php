<?php
define('ROOT_PATH', dirname(__FILE__));

$dbFile = ROOT_PATH . '/database/forum.db';

if (file_exists($dbFile)) {
    echo "数据库已存在，请先删除数据库文件后再运行安装脚本。";
    exit;
}

$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = [
    'next_action' => "CREATE TABLE next_action (
        uid INTEGER NOT NULL DEFAULT 0,
        action CHAR(15) NOT NULL DEFAULT '',
        num INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (uid, action)
    )",
    'next_attach' => "CREATE TABLE next_attach (
        aid INTEGER PRIMARY KEY AUTOINCREMENT,
        tid INTEGER NOT NULL DEFAULT 0,
        pid INTEGER NOT NULL DEFAULT 0,
        uid INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        filename VARCHAR(150) NOT NULL DEFAULT '',
        filesize INTEGER NOT NULL DEFAULT 0,
        filepath VARCHAR(150) NOT NULL DEFAULT '',
        filetype VARCHAR(10) NOT NULL DEFAULT '',
        down_num INTEGER NOT NULL DEFAULT 0,
        sort_order INTEGER NOT NULL DEFAULT 0
    )",
    'next_credit' => "CREATE TABLE next_credit (
        did INTEGER PRIMARY KEY AUTOINCREMENT,
        uid INTEGER NOT NULL DEFAULT 0,
        credit INTEGER NOT NULL DEFAULT 0,
        credit_key VARCHAR(32) NOT NULL DEFAULT '',
        dateline INTEGER NOT NULL DEFAULT 0,
        message TEXT NOT NULL,
        url VARCHAR(255) NOT NULL DEFAULT ''
    )",
    'next_data' => "CREATE TABLE next_data (
        dkey VARCHAR(32) NOT NULL DEFAULT '',
        val TEXT NOT NULL,
        PRIMARY KEY (dkey)
    )",
    'next_fav' => "CREATE TABLE next_fav (
        tid INTEGER NOT NULL DEFAULT 0,
        uid INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (tid, uid)
    )",
    'next_forum' => "CREATE TABLE next_forum (
        fid INTEGER PRIMARY KEY AUTOINCREMENT,
        up_fid INTEGER NOT NULL DEFAULT 0,
        name VARCHAR(50) NOT NULL DEFAULT '',
        status INTEGER NOT NULL DEFAULT 0,
        sort_order INTEGER NOT NULL DEFAULT 0,
        thread_num INTEGER NOT NULL DEFAULT 0,
        reply_num INTEGER NOT NULL DEFAULT 0,
        today_num INTEGER NOT NULL DEFAULT 0,
        today_time INTEGER NOT NULL DEFAULT 0,
        last_tid INTEGER NOT NULL DEFAULT 0,
        json_data TEXT NOT NULL
    )",
    'next_forum_idx_up_fid' => "CREATE INDEX idx_next_forum_up_fid ON next_forum(up_fid, sort_order)",
    'next_guest' => "CREATE TABLE next_guest (
        ip CHAR(50) NOT NULL DEFAULT '',
        fid INTEGER NOT NULL DEFAULT 0,
        tid INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (ip)
    )",
    'next_guest_action' => "CREATE TABLE next_guest_action (
        ip CHAR(50) NOT NULL DEFAULT '',
        action CHAR(15) NOT NULL DEFAULT '',
        num INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (ip, action)
    )",
    'next_invite' => "CREATE TABLE next_invite (
        did INTEGER PRIMARY KEY AUTOINCREMENT,
        code CHAR(10) NOT NULL DEFAULT '',
        uid INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        reg_uid INTEGER NOT NULL DEFAULT 0,
        reg_dateline INTEGER NOT NULL DEFAULT 0,
        pay_credit INTEGER NOT NULL DEFAULT 0,
        add_credit INTEGER NOT NULL DEFAULT 0
    )",
    'next_member' => "CREATE TABLE next_member (
        uid INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50) NOT NULL DEFAULT '',
        gid INTEGER NOT NULL DEFAULT 0,
        avatar INTEGER NOT NULL DEFAULT 0,
        password VARCHAR(255) NOT NULL DEFAULT '',
        auth_secret VARCHAR(50) NOT NULL DEFAULT '',
        auth_enabled INTEGER NOT NULL DEFAULT 0,
        notify_num INTEGER NOT NULL DEFAULT 0,
        credit INTEGER NOT NULL DEFAULT 0,
        reg_ip VARCHAR(50) NOT NULL DEFAULT '',
        reg_date INTEGER NOT NULL DEFAULT 0,
        last_ip VARCHAR(50) NOT NULL DEFAULT '',
        last_visit INTEGER NOT NULL DEFAULT 0,
        reply_time INTEGER NOT NULL DEFAULT 0,
        reply_num INTEGER NOT NULL DEFAULT 0,
        thread_num INTEGER NOT NULL DEFAULT 0,
        fav_num INTEGER NOT NULL DEFAULT 0,
        inbox_num INTEGER NOT NULL DEFAULT 0,
        outbox_num INTEGER NOT NULL DEFAULT 0,
        log_num INTEGER NOT NULL DEFAULT 0,
        email VARCHAR(80) NOT NULL DEFAULT '',
        email_status INTEGER NOT NULL DEFAULT 0,
        signin_time INTEGER NOT NULL DEFAULT 0,
        invisible INTEGER NOT NULL DEFAULT 0,
        timeoffset VARCHAR(10) NOT NULL DEFAULT '',
        search_time INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 0
    )",
    'next_member_idx_username' => "CREATE INDEX idx_next_member_username ON next_member(username)",
    'next_member_idx_email' => "CREATE INDEX idx_next_member_email ON next_member(email)",
    'next_member_idx_gid' => "CREATE INDEX idx_next_member_gid ON next_member(gid)",
    'next_mod_log' => "CREATE TABLE next_mod_log (
        did INTEGER PRIMARY KEY AUTOINCREMENT,
        tid INTEGER NOT NULL DEFAULT 0,
        pid INTEGER NOT NULL DEFAULT 0,
        uid INTEGER NOT NULL DEFAULT 0,
        from_uid INTEGER NOT NULL DEFAULT 0,
        message TEXT NOT NULL,
        post_message TEXT NOT NULL,
        dateline INTEGER NOT NULL DEFAULT 0
    )",
    'next_mod_log_idx_uid_did' => "CREATE INDEX idx_next_mod_log_uid_did ON next_mod_log(uid, did DESC)",
    'next_moderator' => "CREATE TABLE next_moderator (
        uid INTEGER NOT NULL DEFAULT 0,
        fid INTEGER NOT NULL DEFAULT 0,
        end_date INTEGER NOT NULL DEFAULT 0,
        sort_order INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (uid, fid)
    )",
    'next_notify' => "CREATE TABLE next_notify (
        did INTEGER PRIMARY KEY AUTOINCREMENT,
        uid INTEGER NOT NULL DEFAULT 0,
        from_uid INTEGER NOT NULL DEFAULT 0,
        fid INTEGER NOT NULL DEFAULT 0,
        tid INTEGER NOT NULL DEFAULT 0,
        pid INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 0,
        message VARCHAR(255) NOT NULL DEFAULT ''
    )",
    'next_notify_idx_uid_did' => "CREATE INDEX idx_next_notify_uid_did ON next_notify(uid, did DESC)",
    'next_notify_idx_uid_status' => "CREATE INDEX idx_next_notify_uid_status ON next_notify(uid, status)",
    'next_notify_idx_uid_tid' => "CREATE INDEX idx_next_notify_uid_tid ON next_notify(uid, tid)",
    'next_pm' => "CREATE TABLE next_pm (
        pmid INTEGER PRIMARY KEY AUTOINCREMENT,
        uid INTEGER NOT NULL DEFAULT 0,
        to_uid INTEGER NOT NULL DEFAULT 0,
        content TEXT NOT NULL,
        dateline INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 0,
        is_read INTEGER NOT NULL DEFAULT 0
    )",
    'next_pm_idx_to_uid_pmid' => "CREATE INDEX idx_next_pm_to_uid_pmid ON next_pm(to_uid, pmid DESC)",
    'next_pm_idx_uid_pmid' => "CREATE INDEX idx_next_pm_uid_pmid ON next_pm(uid, pmid DESC)",
    'next_pm_idx_to_uid_is_read' => "CREATE INDEX idx_next_pm_to_uid_is_read ON next_pm(to_uid, is_read)",
    'next_post' => "CREATE TABLE next_post (
        pid INTEGER PRIMARY KEY AUTOINCREMENT,
        fid INTEGER NOT NULL DEFAULT 0,
        tid INTEGER NOT NULL DEFAULT 0,
        is_thread INTEGER NOT NULL DEFAULT 0,
        uid INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        report_time INTEGER NOT NULL DEFAULT 0,
        message TEXT NOT NULL,
        ip VARCHAR(50) NOT NULL DEFAULT '',
        rate_num INTEGER NOT NULL DEFAULT 0,
        sort_order INTEGER NOT NULL DEFAULT 0,
        reply_num INTEGER NOT NULL DEFAULT 0,
        quote_pid INTEGER NOT NULL DEFAULT 0,
        quote_uid INTEGER NOT NULL DEFAULT 0,
        quote_floor INTEGER NOT NULL DEFAULT 0
    )",
    'next_post_idx_tid_pid' => "CREATE INDEX idx_next_post_tid_pid ON next_post(tid, pid ASC)",
    'next_post_idx_uid_is_thread_pid' => "CREATE INDEX idx_next_post_uid_is_thread_pid ON next_post(uid, is_thread, pid DESC)",
    'next_rate' => "CREATE TABLE next_rate (
        uid INTEGER NOT NULL DEFAULT 0,
        pid INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (uid, pid)
    )",
    'next_session' => "CREATE TABLE next_session (
        uid INTEGER NOT NULL DEFAULT 0,
        gid INTEGER NOT NULL DEFAULT 0,
        invisible INTEGER NOT NULL DEFAULT 0,
        fid INTEGER NOT NULL DEFAULT 0,
        tid INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (uid)
    )",
    'next_setting' => "CREATE TABLE next_setting (
        skey VARCHAR(32) NOT NULL DEFAULT '',
        val TEXT NOT NULL,
        PRIMARY KEY (skey)
    )",
    'next_thread' => "CREATE TABLE next_thread (
        tid INTEGER PRIMARY KEY AUTOINCREMENT,
        fid INTEGER NOT NULL DEFAULT 0,
        pid INTEGER NOT NULL DEFAULT 0,
        typeid INTEGER NOT NULL DEFAULT 0,
        read_perm INTEGER NOT NULL DEFAULT 0,
        uid INTEGER NOT NULL DEFAULT 0,
        pm_uid INTEGER NOT NULL DEFAULT 0,
        subject VARCHAR(255) NOT NULL DEFAULT '',
        hash VARCHAR(255) NOT NULL DEFAULT '',
        dateline INTEGER NOT NULL DEFAULT 0,
        sort_order INTEGER NOT NULL DEFAULT 0,
        highlight INTEGER NOT NULL DEFAULT 0,
        digest INTEGER NOT NULL DEFAULT 0,
        closed INTEGER NOT NULL DEFAULT 0,
        reply_time INTEGER NOT NULL DEFAULT 0,
        reply_uid INTEGER NOT NULL DEFAULT 0,
        reply_num INTEGER NOT NULL DEFAULT 0,
        view_num INTEGER NOT NULL DEFAULT 0,
        fav_num INTEGER NOT NULL DEFAULT 0,
        log_num INTEGER NOT NULL DEFAULT 0
    )",
    'next_thread_idx_fid_reply' => "CREATE INDEX idx_next_thread_fid_reply ON next_thread(fid, reply_time DESC)",
    'next_thread_idx_uid_tid' => "CREATE INDEX idx_next_thread_uid_tid ON next_thread(uid, tid DESC)",
    'next_thread_idx_tid' => "CREATE INDEX idx_next_thread_tid ON next_thread(tid DESC)",
    'next_thread_tag' => "CREATE TABLE next_thread_tag (
        tid INTEGER NOT NULL DEFAULT 0,
        up_tid INTEGER NOT NULL DEFAULT 0,
        dateline INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (tid, up_tid)
    )",
    'next_threadtype' => "CREATE TABLE next_threadtype (
        typeid INTEGER PRIMARY KEY AUTOINCREMENT,
        fid INTEGER NOT NULL DEFAULT 0,
        sort_order INTEGER NOT NULL DEFAULT 0,
        name VARCHAR(50) NOT NULL DEFAULT ''
    )",
    'next_usergroup' => "CREATE TABLE next_usergroup (
        gid INTEGER PRIMARY KEY AUTOINCREMENT,
        group_type VARCHAR(20) NOT NULL DEFAULT 'member',
        title VARCHAR(50) NOT NULL DEFAULT '',
        credit_lower INTEGER NOT NULL DEFAULT 0,
        json_data TEXT NOT NULL
    )",
    'next_view_log' => "CREATE TABLE next_view_log (
        tid INTEGER NOT NULL DEFAULT 0,
        uid_ip CHAR(50) NOT NULL DEFAULT '',
        dateline INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (tid, uid_ip)
    )"
];

foreach ($tables as $name => $sql) {
    $db->exec($sql);
}

$db->exec("INSERT INTO next_usergroup (gid, group_type, title, credit_lower, json_data) VALUES (1, 'system', '管理员', 0, '{}')");
$db->exec("INSERT INTO next_usergroup (gid, group_type, title, credit_lower, json_data) VALUES (2, 'system', '普通会员', 0, '{}')");

$db->exec("INSERT INTO next_member (uid, username, gid, password, auth_secret, reg_ip, reg_date, status) VALUES (1, 'admin', 1, '" . password_hash('admin123', PASSWORD_DEFAULT) . "', '".md5(uniqid())."', '127.0.0.1', ".time().", 1)");

$db->exec("INSERT INTO next_forum (fid, name, status, sort_order, json_data) VALUES (1, '综合讨论区', 1, 1, '{}')");
$db->exec("INSERT INTO next_forum (fid, name, status, sort_order, json_data) VALUES (2, '技术交流', 1, 2, '{}')");
$db->exec("INSERT INTO next_forum (fid, name, status, sort_order, json_data) VALUES (3, '休闲娱乐', 1, 3, '{}')");

echo "安装成功！管理员账号: admin，密码: admin123";
?>
