<div class="card center-card">
    <div class="card-header">
        <h2>登录</h2>
    </div>
    <div class="card-body padded">
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
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
        
        <div class="mt-lg text-center">
            <p class="text-secondary">
                还没有账号？<a href="index.php?c=auth&a=register">立即注册</a>
            </p>
        </div>
    </div>
</div>
