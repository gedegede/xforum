<div class="card center-card auth-card">
    <div class="card-header">
        <div class="auth-card-title">
            <h2>登录</h2>
        </div>
    </div>
    <div class="card-body auth-card-body p-lg">
        <div class="flex flex-wrap gap-sm mb-lg">
            <span class="badge badge-blue">继续讨论</span>
            <span class="badge badge-gray">同步通知</span>
            <span class="badge badge-gray">管理收藏</span>
        </div>
        <?php if (!empty($template_error)): ?>
            <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?c=auth&a=login">
            <div class="form-group">
                <label for="username">用户名或邮箱</label>
                <input type="text" id="username" name="username" placeholder="请输入用户名或邮箱" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" placeholder="请输入密码" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary w-full">登录</button>
        </form>

        <div class="auth-card-tip">
            <p>
                还没有账号？<a href="index.php?c=auth&a=register">立即注册</a>
            </p>
        </div>
    </div>
</div>
