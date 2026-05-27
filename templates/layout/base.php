<!DOCTYPE html>
<html lang="zh-CN" class="<?php
    $theme = 'light';
    if (isset($template_user) && is_array($template_user) && !empty($template_user) && isset($template_user['json_data'])) {
        $jsonData = json_decode($template_user['json_data'], true);
        $theme = $jsonData['theme'] ?? 'light';
    }
    echo $theme;
?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(isset($template_title) ? $template_title : \Models\SettingModel::get('site_name', 'XForum')); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php
    $customCss = '';
    if (isset($template_user) && is_array($template_user) && !empty($template_user) && isset($template_user['json_data'])) {
        $jsonData = json_decode($template_user['json_data'], true);
        $customCss = $jsonData['custom_css'] ?? '';
    }
    if (!empty($customCss)):
    ?>
    <style id="user-custom-css"><?php echo $customCss; ?></style>
    <?php endif; ?>
    <?php echo \Models\SettingModel::get('head_code', ''); ?>
</head>
<body class="flex flex-col min-h-screen">
    <?php
    $isIndex = !isset($_GET['c']) || (isset($_GET['c']) && $_GET['c'] == 'home' && (!isset($_GET['a']) || $_GET['a'] == 'index'));
    $isForum = isset($_GET['c']) && $_GET['c'] == 'forum' && (!isset($_GET['a']) || $_GET['a'] == 'index');
    $isNotify = isset($_GET['c']) && $_GET['c'] == 'notify';
    $isPm = isset($_GET['c']) && $_GET['c'] == 'pm';
    $isProfile = isset($_GET['c']) && $_GET['c'] == 'member';
    $notifyCount = (int)($template_user['notify_num'] ?? 0);
    $inboxCount = (int)($template_user['inbox_num'] ?? 0);
    $themeToggleUrl = $_SERVER['REQUEST_URI'] ?? 'index.php';
    $themeToggleUrl = str_replace(["\r", "\n"], '', $themeToggleUrl);
    $nextTheme = $theme === 'dark' ? 'light' : 'dark';
    $nextThemeText = $theme === 'dark' ? '日间模式' : '夜间模式';
    ?>

    <!-- Header -->
    <header class="site-header">
        <div class="container h-full flex items-center justify-between gap-4 px-3">
            <a href="index.php" class="site-brand">
                <span class="site-brand-mark"><?php echo htmlspecialchars(mb_substr(\Models\SettingModel::get('site_name', 'XForum'), 0, 1)); ?></span>
                <span><?php echo htmlspecialchars(\Models\SettingModel::get('site_name', 'XForum')); ?></span>
            </a>
            <nav class="site-nav">
                <a href="index.php" class="site-nav-link <?php echo $isIndex ? 'active' : ''; ?>">首页</a>
                <a href="index.php?c=forum&a=index" class="site-nav-link <?php echo $isForum ? 'active' : ''; ?>">版块</a>
                <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
                    <?php if ($notifyCount > 0): ?>
                    <a href="index.php?c=notify&a=index" class="site-nav-link <?php echo $isNotify ? 'active' : ''; ?>">
                        通知<span class="nav-count"><?php echo min(99, $notifyCount); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if ($inboxCount > 0): ?>
                    <a href="index.php?c=pm&a=inbox" class="site-nav-link <?php echo $isPm ? 'active' : ''; ?>">
                        私信<span class="nav-count"><?php echo min(99, $inboxCount); ?></span>
                    </a>
                    <?php endif; ?>
                    <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid'] ?? 0; ?>" class="site-nav-link <?php echo $isProfile ? 'active' : ''; ?>">我的</a>
                    <form method="post" action="index.php?c=member&a=theme" class="flex">
                        <input type="hidden" name="theme" value="<?php echo $nextTheme; ?>">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($themeToggleUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-ghost btn-icon" title="切换<?php echo $nextThemeText; ?>" aria-label="切换<?php echo $nextThemeText; ?>">
                            <?php if ($theme === 'dark'): ?>
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"></circle>
                                <path d="M12 2v2"></path>
                                <path d="M12 20v2"></path>
                                <path d="m4.93 4.93 1.41 1.41"></path>
                                <path d="m17.66 17.66 1.41 1.41"></path>
                                <path d="M2 12h2"></path>
                                <path d="M20 12h2"></path>
                                <path d="m6.34 17.66-1.41 1.41"></path>
                                <path d="m19.07 4.93-1.41 1.41"></path>
                            </svg>
                            <?php else: ?>
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3a6 6 0 0 0 9 7.5 9 9 0 1 1-9-7.5z"></path>
                            </svg>
                            <?php endif; ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="index.php?c=auth&a=login" class="site-nav-link">登录</a>
                    <a href="index.php?c=auth&a=register" class="site-nav-link">注册</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 container p-3">
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="hide-mobile mt-1 bg-text text-white/70">
        <?php echo \Models\SettingModel::get('footer_code', ''); ?>
        <div class="container px-3">
            <div class="flex flex-wrap justify-between items-center gap-4 pt-6 text-xs">
                <div class="flex flex-wrap gap-4">
                    <a href="index.php" class="hover:text-white transition-colors">首页</a>
                    <a href="index.php?c=forum&a=index" class="hover:text-white transition-colors">版块</a>
                    <a href="index.php?order=view_num" class="hover:text-white transition-colors">热门</a>
                    <a href="index.php?order=reply_time" class="hover:text-white transition-colors">最新</a>
                </div>
                <div>Copyright © 2024-<?php echo date('Y'); ?> <?php echo htmlspecialchars(\Models\SettingModel::get('site_name', 'XForum')); ?> All Rights Reserved</div>
            </div>

            <!-- SQL Query Log -->
            <?php
            $queryLog = \Lib\Database::getQueryLog();
            if (!empty($queryLog)):
            ?>
            <div class="mt-6 pt-5 border-t border-white/10">
                <h4 class="mb-3 text-sm font-semibold text-gray-400">SQL查询日志 (共 <?php echo count($queryLog); ?> 条)</h4>
                <?php foreach ($queryLog as $index => $query): ?>
                <div class="mb-4 p-3 rounded bg-black/20">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center justify-center px-2 py-1 rounded text-[10px] font-medium bg-text text-white">#<?php echo $index + 1; ?></span>
                        <span class="text-sm text-gray-400"><?php echo number_format($query['duration'], 2); ?>ms</span>
                    </div>
                    <div>
                        <code class="block mb-2 p-3 bg-text text-gray-400 font-mono text-sm leading-normal rounded table-wrap"><?php echo htmlspecialchars($query['sql']); ?></code>
                    </div>
                    <?php if (!empty($query['params'])): ?>
                    <div class="mt-2 p-2 rounded text-sm bg-black/20 text-gray-400">
                        参数: <?php echo htmlspecialchars(json_encode($query['params'])); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($query['explain'])): ?>
                    <div class="mt-3 pt-3 border-t border-white/8">
                        <div class="mb-2 text-sm font-semibold text-amber-300">EXPLAIN:</div>
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <?php foreach (array_keys($query['explain'][0]) as $col): ?>
                                        <th class="text-left px-2 py-1.5 bg-black/20 text-muted font-semibold border border-white/10"><?php echo htmlspecialchars($col); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($query['explain'] as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $val): ?>
                                        <td class="px-2 py-1.5 text-muted border border-white/10 font-mono"><?php echo htmlspecialchars((string)($val ?? '-')); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </footer>

    <!-- Mobile Bottom Nav -->
    <nav class="mobile-only mobile-nav">
        <div class="mobile-nav-list">
            <a href="index.php" class="mobile-nav-link <?php echo $isIndex ? 'active' : ''; ?>">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                <span class="text-[10px]">首页</span>
            </a>
            <a href="index.php?c=forum&a=index" class="mobile-nav-link <?php echo $isForum ? 'active' : ''; ?>">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                <span class="text-[10px]">论坛</span>
            </a>
            <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid'] ?? 0; ?>" class="mobile-nav-link <?php echo $isProfile ? 'active' : ''; ?>">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <span class="text-[10px]">我的</span>
            </a>
            <?php else: ?>
            <a href="index.php?c=auth&a=login" class="mobile-nav-link">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <span class="text-[10px]">登录</span>
            </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Credit Tips Toast -->
    <div id="credit-toast" style="display:none; position:fixed; top:24px; left:50%; transform:translateX(-50%) translateY(-20px); z-index:9999; opacity:0; transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <div style="display:flex; align-items:center; gap:8px; padding:12px 20px; border-radius:12px; background:linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow:0 10px 40px rgba(16, 185, 129, 0.3), 0 2px 8px rgba(0, 0, 0, 0.1); color:white; font-size:14px; font-weight:600;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v8M8 12h8"/>
            </svg>
            <span id="credit-toast-text"></span>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="message-modal" data-modal-overlay class="modal hidden" onclick="closeMessageModal()">
        <div class="modal-panel" onclick="event.stopPropagation()">
            <div class="modal-header">
                <span id="message-modal-title" class="font-semibold">提示</span>
                <button onclick="closeMessageModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p id="message-modal-content" class="text-text leading-relaxed"></p>
            </div>
            <div id="message-modal-footer" class="modal-footer hidden">
                <button onclick="closeMessageModal()" class="cancel-btn btn btn-soft">取消</button>
                <button onclick="closeMessageModal()" class="confirm-btn btn btn-primary">确定</button>
            </div>
        </div>
    </div>

    <script src="assets/js/base.js"></script>
</body>
</html>
