<div class="auth-shell">
    <div class="w-full max-w-md card">
        <div class="card-header">
            <h2>登录</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($template_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
            <?php endif; ?>

            <form method="post" action="index.php?c=auth&a=login">
                <div class="form-field">
                    <label for="username" class="form-label">用户名或邮箱</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="请输入用户名或邮箱" required autocomplete="username">
                </div>
                <div class="form-field">
                    <label for="password" class="form-label">密码</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="请输入密码" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-block btn-primary">登录</button>
            </form>

            <div class="mt-4 text-center text-muted text-sm">
                <p>还没有账号？<a href="index.php?c=auth&a=register" class="text-primary hover:underline">立即注册</a></p>
            </div>
        </div>
    </div>
</div>
