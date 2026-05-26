<div class="flex items-center justify-center min-h-screen py-8">
    <div class="w-full max-w-md bg-panel border border-border rounded shadow-sm">
        <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
            <h2>登录</h2>
        </div>
        <div class="p-4">
            <?php if (!empty($template_error)): ?>
            <div class="p-3 rounded bg-danger-light text-danger mb-4 text-sm"><?php echo htmlspecialchars($template_error); ?></div>
            <?php endif; ?>

            <form method="post" action="index.php?c=auth&a=login">
                <div class="mb-4">
                    <label for="username" class="block mb-1.5 text-sm font-medium text-text">用户名或邮箱</label>
                    <input type="text" id="username" name="username" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" placeholder="请输入用户名或邮箱" required autocomplete="username">
                </div>
                <div class="mb-4">
                    <label for="password" class="block mb-1.5 text-sm font-medium text-text">密码</label>
                    <input type="password" id="password" name="password" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" placeholder="请输入密码" required autocomplete="current-password">
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark w-full">登录</button>
            </form>

            <div class="mt-4 text-center text-muted text-sm">
                <p>还没有账号？<a href="index.php?c=auth&a=register" class="text-primary hover:underline">立即注册</a></p>
            </div>
        </div>
    </div>
</div>
