<?php
$template_adminAction = $_GET['a'] ?? 'index';
$template_isForumAdmin = in_array($template_adminAction, ['forums', 'forumAdd', 'forumEdit', 'forumDelete'], true);
$template_isThreadAdmin = in_array($template_adminAction, ['threads', 'threadDelete', 'threadBatch'], true);
$template_isAuditAdmin = in_array($template_adminAction, ['audits', 'auditHandle'], true);
$template_isUsergroupAdmin = in_array($template_adminAction, ['usergroups', 'usergroupAdd', 'usergroupEdit', 'usergroupDelete'], true);
$template_isUserAdmin = in_array($template_adminAction, ['users', 'userEdit', 'userDelete'], true);
$template_adminPermissions = [
    'settings' => \Lib\Permission::hasGroupPermission('admin_setting'),
    'forums' => \Lib\Permission::hasGroupPermission('admin_forum'),
    'threads' => \Lib\Permission::hasGroupPermission('admin_thread'),
    'usergroups' => \Lib\Permission::hasGroupPermission('admin_usergroup'),
    'users' => \Lib\Permission::hasGroupPermission('admin_user'),
    'logs' => \Lib\Permission::hasGroupPermission('admin_log'),
];
?>
<link rel="stylesheet" href="assets/css/admin.css">
<div class="card card-lg mb-4 card-clip">
    <nav class="pill-nav" aria-label="后台菜单">
        <div class="pill-nav-list">
            <a href="index.php?c=admin&a=index"
               class="pill-nav-item <?php echo $template_adminAction == 'index' ? 'active' : ''; ?>">
                首页
            </a>
            <?php if ($template_adminPermissions['settings']): ?><a href="index.php?c=admin&a=settings" class="pill-nav-item <?php echo $template_adminAction == 'settings' ? 'active' : ''; ?>">站点设置</a><?php endif; ?>
            <?php if ($template_adminPermissions['forums']): ?><a href="index.php?c=admin&a=forums" class="pill-nav-item <?php echo $template_isForumAdmin ? 'active' : ''; ?>">版块管理</a><?php endif; ?>
            <?php if ($template_adminPermissions['threads']): ?><a href="index.php?c=admin&a=threads" class="pill-nav-item <?php echo $template_isThreadAdmin ? 'active' : ''; ?>">主题管理</a><?php endif; ?>
            <?php if ($template_adminPermissions['threads']): ?><a href="index.php?c=admin&a=audits" class="pill-nav-item <?php echo $template_isAuditAdmin ? 'active' : ''; ?>">内容审核</a><?php endif; ?>
            <?php if ($template_adminPermissions['usergroups']): ?><a href="index.php?c=admin&a=usergroups" class="pill-nav-item <?php echo $template_isUsergroupAdmin ? 'active' : ''; ?>">用户组管理</a><?php endif; ?>
            <?php if ($template_adminPermissions['users']): ?><a href="index.php?c=admin&a=users" class="pill-nav-item <?php echo $template_isUserAdmin ? 'active' : ''; ?>">用户管理</a><?php endif; ?>
            <?php if ($template_adminPermissions['logs']): ?><a href="index.php?c=admin&a=logs" class="pill-nav-item <?php echo $template_adminAction == 'logs' ? 'active' : ''; ?>">管理日志</a><?php endif; ?>
        </div>
    </nav>
</div>
