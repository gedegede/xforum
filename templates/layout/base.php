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
    <title><?php echo htmlspecialchars(isset($template_title) ? $template_title : 'XForum'); ?></title>
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
    <header class="h-header bg-panel border-b border-border">
        <div class="container h-full flex items-center justify-between gap-4 px-3">
            <a href="index.php" class="flex items-center gap-2 font-bold text-lg text-primary">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded bg-primary text-white text-sm">X</span>
                <span>XForum</span>
            </a>
            <nav class="flex items-center gap-1">
                <a href="index.php" class="flex items-center gap-1 px-3 py-1.5 rounded text-sub hover:bg-hover hover:text-text transition-colors <?php echo $isIndex ? 'bg-primary-light text-primary' : ''; ?>">首页</a>
                <a href="index.php?c=forum&a=index" class="flex items-center gap-1 px-3 py-1.5 rounded text-sub hover:bg-hover hover:text-text transition-colors <?php echo $isForum ? 'bg-primary-light text-primary' : ''; ?>">论坛</a>
                <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
                    <?php if ($notifyCount > 0): ?>
                    <a href="index.php?c=notify&a=index" class="flex items-center gap-1 px-3 py-1.5 rounded text-sub hover:bg-hover hover:text-text transition-colors <?php echo $isNotify ? 'bg-primary-light text-primary' : ''; ?>">
                        通知<span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-primary text-white text-xs ml-1.5"><?php echo min(99, $notifyCount); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if ($inboxCount > 0): ?>
                    <a href="index.php?c=pm&a=inbox" class="flex items-center gap-1 px-3 py-1.5 rounded text-sub hover:bg-hover hover:text-text transition-colors <?php echo $isPm ? 'bg-primary-light text-primary' : ''; ?>">
                        私信<span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-primary text-white text-xs ml-1.5"><?php echo min(99, $inboxCount); ?></span>
                    </a>
                    <?php endif; ?>
                    <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid'] ?? 0; ?>" class="flex items-center gap-1 px-3 py-1.5 rounded text-sub hover:bg-hover hover:text-text transition-colors <?php echo $isProfile ? 'bg-primary-light text-primary' : ''; ?>">我的</a>
                    <form method="post" action="index.php?c=member&a=theme" class="flex">
                        <input type="hidden" name="theme" value="<?php echo $nextTheme; ?>">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($themeToggleUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="inline-flex items-center justify-center w-control h-control border-0 rounded bg-transparent text-sub hover:bg-hover hover:text-text cursor-pointer transition-colors" title="切换<?php echo $nextThemeText; ?>" aria-label="切换<?php echo $nextThemeText; ?>">
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
                    <a href="index.php?c=auth&a=login" class="flex items-center gap-1 px-3 py-1.5 rounded text-sub hover:bg-hover hover:text-text transition-colors">登录</a>
                    <a href="index.php?c=auth&a=register" class="flex items-center gap-1 px-3 py-1.5 rounded text-sub hover:bg-hover hover:text-text transition-colors">注册</a>
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
        <div class="container px-3">
            <div class="flex flex-wrap justify-between items-center gap-4 pt-6 text-xs">
                <div class="flex flex-wrap gap-4">
                    <a href="index.php" class="hover:text-white transition-colors">首页</a>
                    <a href="index.php?c=forum&a=index" class="hover:text-white transition-colors">论坛</a>
                    <a href="index.php?order=view_num" class="hover:text-white transition-colors">热门</a>
                    <a href="index.php?order=reply_time" class="hover:text-white transition-colors">最新</a>
                </div>
                <div>Copyright © 2024-<?php echo date('Y'); ?> XForum All Rights Reserved</div>
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
                        <code class="block mb-2 p-3 bg-text text-gray-400 font-mono text-sm leading-normal rounded overflow-x-auto"><?php echo htmlspecialchars($query['sql']); ?></code>
                    </div>
                    <?php if (!empty($query['params'])): ?>
                    <div class="mt-2 p-2 rounded text-sm bg-black/20 text-gray-400">
                        参数: <?php echo htmlspecialchars(json_encode($query['params'])); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($query['explain'])): ?>
                    <div class="mt-3 pt-3 border-t border-white/8">
                        <div class="mb-2 text-sm font-semibold text-amber-300">EXPLAIN:</div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
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
    <nav class="mobile-only fixed left-0 right-0 bottom-0 bg-panel border-t border-border z-50">
        <div class="flex justify-around h-14">
            <a href="index.php" class="flex flex-1 flex-col items-center justify-center gap-0.5 <?php echo $isIndex ? 'text-primary' : 'text-muted hover:text-primary'; ?> transition-colors">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                <span class="text-[10px]">首页</span>
            </a>
            <a href="index.php?c=forum&a=index" class="flex flex-1 flex-col items-center justify-center gap-0.5 <?php echo $isForum ? 'text-primary' : 'text-muted hover:text-primary'; ?> transition-colors">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                <span class="text-[10px]">论坛</span>
            </a>
            <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid'] ?? 0; ?>" class="flex flex-1 flex-col items-center justify-center gap-0.5 <?php echo $isProfile ? 'text-primary' : 'text-muted hover:text-primary'; ?> transition-colors">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <span class="text-[10px]">我的</span>
            </a>
            <?php else: ?>
            <a href="index.php?c=auth&a=login" class="flex flex-1 flex-col items-center justify-center gap-0.5 text-muted hover:text-primary transition-colors">
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

    <script>
    (function() {
        var toast = document.getElementById('credit-toast');
        var toastText = document.getElementById('credit-toast-text');
        var toastTimer = null;

        window.showCreditToast = function(amount) {
            if (!toast || !toastText || !amount) return;

            var isPositive = amount > 0;
            var prefix = isPositive ? '+' : '';
            var color = isPositive ? '#ffffff' : '#ffffff';
            var bg = isPositive
                ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)'
                : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
            var shadow = isPositive
                ? '0 10px 40px rgba(16, 185, 129, 0.3), 0 2px 8px rgba(0, 0, 0, 0.1)'
                : '0 10px 40px rgba(239, 68, 68, 0.3), 0 2px 8px rgba(0, 0, 0, 0.1)';

            toastText.textContent = '金币 ' + prefix + amount;
            toast.firstElementChild.style.background = bg;
            toast.firstElementChild.style.boxShadow = shadow;

            toast.style.display = 'block';
            requestAnimationFrame(function() {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(-50%) translateY(0)';
            });

            if (toastTimer) clearTimeout(toastTimer);
            toastTimer = setTimeout(function() {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(-20px)';
                setTimeout(function() {
                    toast.style.display = 'none';
                }, 400);
            }, 2000);
        };
    })();
    </script>
</body>
</html>
