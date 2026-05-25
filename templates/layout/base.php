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
                    <?php if ($query['explain']): ?>
                    <div class="mt-3 pt-3 border-t border-white/8">
                        <div class="mb-2 text-sm font-semibold text-amber-300">EXPLAIN QUERY PLAN:</div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead>
                                    <tr>
                                        <th class="text-left px-2 py-1.5 bg-black/20 text-muted font-semibold border border-white/10">id</th>
                                        <th class="text-left px-2 py-1.5 bg-black/20 text-muted font-semibold border border-white/10">parent</th>
                                        <th class="text-left px-2 py-1.5 bg-black/20 text-muted font-semibold border border-white/10">notused</th>
                                        <th class="text-left px-2 py-1.5 bg-black/20 text-muted font-semibold border border-white/10">detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($query['explain'] as $row): ?>
                                    <tr>
                                        <td class="px-2 py-1.5 text-muted border border-white/10 font-mono"><?php echo $row['id'] ?? '-'; ?></td>
                                        <td class="px-2 py-1.5 text-muted border border-white/10 font-mono"><?php echo $row['parent'] ?? '-'; ?></td>
                                        <td class="px-2 py-1.5 text-muted border border-white/10 font-mono"><?php echo $row['notused'] ?? '-'; ?></td>
                                        <td class="px-2 py-1.5 text-muted border border-white/10 font-mono"><?php echo htmlspecialchars($row['detail'] ?? '-'); ?></td>
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
</body>
</html>
