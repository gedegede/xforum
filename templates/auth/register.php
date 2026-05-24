<div class="card center-card auth-card">
    <div class="card-header">
        <div class="auth-card-title">
            <h2>注册</h2>
        </div>
    </div>
    <div class="card-body auth-card-body p-lg">
        <div class="flex flex-wrap gap-sm mb-lg">
            <span class="badge badge-blue">创建身份</span>
            <span class="badge badge-gray">发布主题</span>
            <span class="badge badge-gray">积累个人记录</span>
        </div>
        <?php if (!empty($template_error)): ?>
            <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?c=auth&a=register">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" placeholder="请输入用户名" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="email">邮箱</label>
                <input type="email" id="email" name="email" placeholder="请输入邮箱地址" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" placeholder="请输入密码（至少6位）" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="confirm_password">确认密码</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="请再次输入密码" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary w-full">注册</button>
        </form>

        <div class="auth-card-tip">
            <p>
                已有账号？<a href="index.php?c=auth&a=login">立即登录</a>
            </p>
        </div>
    </div>
</div>
