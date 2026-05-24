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
</head>
<body>
    <?php
    $isIndex = !isset($_GET['c']) || (isset($_GET['c']) && $_GET['c'] == 'home' && (!isset($_GET['a']) || $_GET['a'] == 'index'));
    $isForum = isset($_GET['c']) && $_GET['c'] == 'forum' && (!isset($_GET['a']) || $_GET['a'] == 'index');
    $isNotify = isset($_GET['c']) && $_GET['c'] == 'notify';
    $isPm = isset($_GET['c']) && $_GET['c'] == 'pm';
    $isProfile = isset($_GET['c']) && $_GET['c'] == 'member';
    $notifyCount = (int)($template_user['notify_num'] ?? 0);
    $inboxCount = (int)($template_user['inbox_num'] ?? 0);
    ?>
    <header>
        <div class="container">
            <a href="index.php" class="site-logo">
                <span class="site-logo-icon">X</span>
                <span>XForum</span>
            </a>
            <nav>
                <a href="index.php" class="<?php echo $isIndex ? 'active' : ''; ?>">首页</a>
                <a href="index.php?c=forum&a=index" class="<?php echo $isForum ? 'active' : ''; ?>">论坛</a>
                <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
                    <a href="index.php?c=notify&a=index" class="<?php echo $isNotify ? 'active' : ''; ?>">
                        通知<?php if ($notifyCount > 0): ?><span class="badge badge-blue ml-sm"><?php echo min(99, $notifyCount); ?></span><?php endif; ?>
                    </a>
                    <a href="index.php?c=pm&a=inbox" class="<?php echo $isPm ? 'active' : ''; ?>">
                        私信<?php if ($inboxCount > 0): ?><span class="badge badge-blue ml-sm"><?php echo min(99, $inboxCount); ?></span><?php endif; ?>
                    </a>
                    <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid'] ?? 0; ?>" class="<?php echo $isProfile ? 'active' : ''; ?>">我的</a>
                <?php else: ?>
                    <a href="index.php?c=auth&a=login">登录</a>
                    <a href="index.php?c=auth&a=register">注册</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container">
        <?php echo $content; ?>
    </main>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>XForum</h4>
                    <p style="color:var(--footer-text);font-size:12px;line-height:1.8;">
                        以主题、回复和持续讨论为核心的社区界面，强调清晰的信息流与稳定的内容沉淀。
                    </p>
                </div>
                <div class="footer-section">
                    <h4>社区入口</h4>
                    <ul>
                        <li><a href="index.php">社区首页</a></li>
                        <li><a href="index.php?c=forum&a=index">论坛导航</a></li>
                        <li><a href="index.php?order=reply_time">最新讨论</a></li>
                        <li><a href="index.php?order=view_num">热门浏览</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>账号与消息</h4>
                    <ul>
                        <li><a href="index.php?c=auth&a=login">用户登录</a></li>
                        <li><a href="index.php?c=auth&a=register">注册账号</a></li>
                        <li><a href="index.php?c=notify&a=index">通知中心</a></li>
                        <li><a href="index.php?c=pm&a=inbox">站内私信</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>创作与管理</h4>
                    <ul>
                        <li><a href="index.php?c=forum&a=index">版块列表</a></li>
                        <li><a href="index.php?c=forum&a=index&from=create">发布主题</a></li>
                        <li><a href="index.php?c=member&a=settings">个人设置</a></li>
                        <li><a href="index.php?c=admin&a=index">管理后台</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-links">
                    <a href="index.php">首页</a>
                    <a href="index.php?c=forum&a=index">论坛</a>
                    <a href="index.php?order=view_num">热门</a>
                    <a href="index.php?order=reply_time">最新</a>
                </div>
                <div>Copyright © 2024-<?php echo date('Y'); ?> XForum All Rights Reserved</div>
            </div>
            <?php
            $queryLog = \Lib\Database::getQueryLog();
            if (!empty($queryLog)):
            ?>
            <div style="margin-top:24px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.1);font-size:12px;">
                <h4 style="margin-bottom:12px;color:#9ca3af;font-size:13px;">SQL查询日志 (共 <?php echo count($queryLog); ?> 条)</h4>
                <?php foreach ($queryLog as $index => $query): ?>
                <div style="margin-bottom:16px;padding:12px;background:rgba(0,0,0,0.2);border-radius:4px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <span class="badge" style="font-size:10px;padding:2px 6px;">#<?php echo $index + 1; ?></span>
                        <span style="color:#9ca3af;font-size:11px;"><?php echo number_format($query['duration'], 2); ?>ms</span>
                    </div>
                    <div><code style="display:block;padding:8px;background:rgba(0,0,0,0.3);border-radius:4px;color:#e5e7eb;font-family:'Consolas','Monaco',monospace;font-size:11px;line-height:1.5;overflow-x:auto;"><?php echo htmlspecialchars($query['sql']); ?></code></div>
                    <?php if (!empty($query['params'])): ?>
                    <div style="margin-top:6px;padding:6px 8px;background:rgba(0,0,0,0.2);border-radius:4px;color:#9ca3af;font-size:11px;">参数: <?php echo htmlspecialchars(json_encode($query['params'])); ?></div>
                    <?php endif; ?>
                    <?php if ($query['explain']): ?>
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,0.08);">
                        <div style="margin-bottom:8px;color:#fbbf24;font-size:11px;font-weight:600;">EXPLAIN QUERY PLAN:</div>
                        <table style="width:100%;border-collapse:collapse;font-size:11px;">
                            <thead>
                                <tr>
                                    <th style="padding:6px 8px;border:1px solid rgba(255,255,255,0.1);text-align:left;background:rgba(0,0,0,0.2);color:#9ca3af;font-weight:600;">id</th>
                                    <th style="padding:6px 8px;border:1px solid rgba(255,255,255,0.1);text-align:left;background:rgba(0,0,0,0.2);color:#9ca3af;font-weight:600;">parent</th>
                                    <th style="padding:6px 8px;border:1px solid rgba(255,255,255,0.1);text-align:left;background:rgba(0,0,0,0.2);color:#9ca3af;font-weight:600;">notused</th>
                                    <th style="padding:6px 8px;border:1px solid rgba(255,255,255,0.1);text-align:left;background:rgba(0,0,0,0.2);color:#9ca3af;font-weight:600;">detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($query['explain'] as $row): ?>
                                <tr>
                                    <td style="padding:6px 8px;border:1px solid rgba(255,255,255,0.1);text-align:left;color:#d1d5db;font-family:'Consolas','Monaco',monospace;"><?php echo $row['id'] ?? '-'; ?></td>
                                    <td style="padding:6px 8px;border:1px solid rgba(255,255,255,0.1);text-align:left;color:#d1d5db;font-family:'Consolas','Monaco',monospace;"><?php echo $row['parent'] ?? '-'; ?></td>
                                    <td style="padding:6px 8px;border:1px solid rgba(255,255,255,0.1);text-align:left;color:#d1d5db;font-family:'Consolas','Monaco',monospace;"><?php echo $row['notused'] ?? '-'; ?></td>
                                    <td style="padding:6px 8px;border:1px solid rgba(255,255,255,0.1);text-align:left;color:#d1d5db;font-family:'Consolas','Monaco',monospace;"><?php echo htmlspecialchars($row['detail'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </footer>
    
    <nav class="mobile-nav">
        <div class="mobile-nav-items">
            <a href="index.php" class="mobile-nav-item <?php echo $isIndex ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                <span>首页</span>
            </a>
            <a href="index.php?c=forum&a=index" class="mobile-nav-item <?php echo $isForum ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                <span>论坛</span>
            </a>
            <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid'] ?? 0; ?>" class="mobile-nav-item <?php echo $isProfile ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <span>我的</span>
            </a>
            <?php else: ?>
            <a href="index.php?c=auth&a=login" class="mobile-nav-item">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <span>登录</span>
            </a>
            <?php endif; ?>
        </div>
    </nav>
</body>
</html>
