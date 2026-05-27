<div class="auth-shell">
    <div class="w-full max-w-md card">
        <div class="card-header">
            <h2>注册</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($template_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
            <?php endif; ?>

            <form method="post" action="index.php?c=auth&a=register">
                <div class="form-field">
                    <label for="username" class="form-label">用户名</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="请输入用户名" required autocomplete="username">
                </div>
                <div class="form-field">
                    <label for="email" class="form-label">邮箱</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="请输入邮箱地址" required autocomplete="email">
                </div>
                <div class="form-field">
                    <label for="password" class="form-label">密码</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="请输入密码（至少6位）" required autocomplete="new-password">
                </div>
                <div class="form-field">
                    <label for="confirm_password" class="form-label">确认密码</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="请再次输入密码" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-block btn-primary">注册</button>
            </form>

            <div class="mt-4 text-center text-muted text-sm">
                <p>已有账号？<a href="index.php?c=auth&a=login" class="text-primary hover:underline">立即登录</a></p>
            </div>
        </div>
    </div>
</div>
