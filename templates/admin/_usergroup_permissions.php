<?php
$template_permissionGroups = [
    '普通权限' => [
        'deny_access' => '禁止访问',
        'deny_thread' => '禁止发主题',
        'thread_need_approve' => '发主题需要审核',
        'deny_reply' => '禁止发回帖',
        'post_need_approve' => '发回帖需要审核',
        'deny_pm' => '禁止发私信',
        'deny_search' => '禁止搜索',
        'deny_edit' => '禁止编辑',
        'deny_favorite' => '禁止收藏',
        'deny_rate' => '禁止点赞',
        'deny_report' => '禁止举报',
    ],
    '管理后台权限' => [
        'admin_thread' => '允许主题管理',
        'admin_setting' => '允许站点设置',
        'admin_forum' => '允许版块管理',
        'admin_usergroup' => '允许用户组管理',
        'admin_user' => '允许用户管理',
        'admin_log' => '允许管理日志',
    ],
];
?>
<?php foreach ($template_permissionGroups as $groupTitle => $permissions): ?>
    <div class="mb-6">
        <label class="form-label form-label-block"><?php echo $groupTitle; ?></label>
        <div class="check-grid">
            <?php foreach ($permissions as $key => $label): ?>
                <label class="check-row">
                    <input type="checkbox" name="<?php echo $key; ?>" value="1" class="rounded" <?php echo !empty($template_group[$key] ?? 0) ? 'checked' : ''; ?>>
                    <span><?php echo $label; ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
