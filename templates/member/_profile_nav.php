<?php
$template_profileNavType = $template_type ?? '';
$template_isMemberProfile = isset($_GET['c'], $_GET['a']) && $_GET['c'] == 'member' && $_GET['a'] == 'profile';
$template_isMemberSettings = isset($_GET['c'], $_GET['a']) && $_GET['c'] == 'member' && $_GET['a'] == 'settings';
$template_isAdminSection = isset($_GET['c']) && $_GET['c'] == 'admin';
$template_isNotify = isset($_GET['c']) && $_GET['c'] == 'notify';
$template_isPm = isset($_GET['c']) && $_GET['c'] == 'pm';
$template_canAccessAdmin = \Lib\Permission::isAdmin();
?>
<div class="card card-lg my-4 card-clip">
    <nav class="pill-nav" aria-label="个人中心菜单">
        <div class="pill-nav-list">
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=threads"
               class="pill-nav-item <?php echo ($template_isMemberProfile && $template_profileNavType == 'threads') ? 'active' : ''; ?>">
                <span><?php echo !empty($template_isSelf) ? '我的主题' : 'Ta 的主题'; ?></span>
                <span class="pill-nav-count"><?php echo (int)$template_member['thread_num']; ?></span>
            </a>
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=replies"
               class="pill-nav-item <?php echo ($template_isMemberProfile && $template_profileNavType == 'replies') ? 'active' : ''; ?>">
                <span><?php echo !empty($template_isSelf) ? '我的回复' : 'Ta 的回复'; ?></span>
                <span class="pill-nav-count"><?php echo (int)$template_member['reply_num']; ?></span>
            </a>
            <?php if (!empty($template_isSelf)): ?>
                <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=credits"
                   class="pill-nav-item <?php echo ($template_isMemberProfile && $template_profileNavType == 'credits') ? 'active' : ''; ?>">
                    <span>金币</span>
                    <span class="pill-nav-count"><?php echo (int)($template_member['credit'] ?? 0); ?></span>
                </a>
                <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=favorites"
                   class="pill-nav-item <?php echo ($template_isMemberProfile && $template_profileNavType == 'favorites') ? 'active' : ''; ?>">
                    <span>收藏</span>
                </a>
                <a href="index.php?c=notify&a=index"
                   class="pill-nav-item <?php echo $template_isNotify ? 'active' : ''; ?>">
                    <span>通知</span>
                </a>
                <a href="index.php?c=pm&a=inbox"
                   class="pill-nav-item <?php echo $template_isPm ? 'active' : ''; ?>">
                    <span>私信</span>
                </a>
                <a href="index.php?c=member&a=settings"
                   class="pill-nav-item <?php echo $template_isMemberSettings ? 'active' : ''; ?>">
                    个人设置
                </a>
                <?php if ($template_canAccessAdmin): ?>
                    <a href="index.php?c=admin&a=index"
                       class="pill-nav-item <?php echo $template_isAdminSection ? 'active' : ''; ?>">
                        站点设置
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </nav>
</div>
