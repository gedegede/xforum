<!DOCTYPE html>
<html lang="zh-CN" class="<?php 
    $theme = 'light';
    if (isset($user) && isset($user['json_data'])) {
        $jsonData = json_decode($user['json_data'], true);
        $theme = $jsonData['theme'] ?? 'light';
    }
    echo $theme;
?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'NodeSeek'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="site-logo">
                <span class="site-logo-icon">N</span>
                <span>NodeSeek</span>
            </a>
            <nav>
                <?php $isIndex = !isset($_GET['c']) || (isset($_GET['c']) && $_GET['c'] == 'home' && (!isset($_GET['a']) || $_GET['a'] == 'index')); ?>
                <?php $isForum = isset($_GET['c']) && $_GET['c'] == 'forum' && (!isset($_GET['a']) || $_GET['a'] == 'index'); ?>
                <?php $isProfile = isset($_GET['c']) && $_GET['c'] == 'member' && isset($_GET['a']) && $_GET['a'] == 'profile'; ?>
                <a href="index.php" class="<?php echo $isIndex ? 'active' : ''; ?>">首页</a>
                <a href="index.php?c=forum&a=index" class="<?php echo $isForum ? 'active' : ''; ?>">论坛</a>
                <?php if (isset($user) && is_array($user) && !empty($user)): ?>
                    <a href="index.php?c=member&a=profile&uid=<?php echo $user['uid'] ?? 0; ?>" class="<?php echo $isProfile ? 'active' : ''; ?>">我的</a>
                <?php else: ?>
                    <a href="index.php?c=auth&a=login">登录</a>
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
                    <h4>相关链接</h4>
                    <ul>
                        <li><a href="#">关于我们</a></li>
                        <li><a href="#">联系我们</a></li>
                        <li><a href="#">服务条款</a></li>
                        <li><a href="#">隐私政策</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>合作伙伴</h4>
                    <ul>
                        <li><a href="#">技术支持</a></li>
                        <li><a href="#">赞助商</a></li>
                        <li><a href="#">友情链接</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>社区规则</h4>
                    <ul>
                        <li><a href="#">社区规范</a></li>
                        <li><a href="#">发帖指南</a></li>
                        <li><a href="#">版主申请</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>关注我们</h4>
                    <ul>
                        <li><a href="#">GitHub</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Discord</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-links">
                    <a href="#">首页</a>
                    <a href="#">论坛</a>
                    <a href="#">发现</a>
                    <a href="#">标签</a>
                </div>
                <div>Copyright © 2024-2025 NodeSeek All Rights Reserved</div>
            </div>
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
            <?php if (isset($user) && is_array($user) && !empty($user)): ?>
            <a href="index.php?c=member&a=profile&uid=<?php echo $user['uid'] ?? 0; ?>" class="mobile-nav-item <?php echo $isProfile ? 'active' : ''; ?>">
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
