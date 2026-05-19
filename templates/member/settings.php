<div class="card center-card">
    <div class="card-header">
        <h2>个人设置</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php
        $jsonData = isset($member['json_data']) ? json_decode($member['json_data'], true) : [];
        $currentTheme = $jsonData['theme'] ?? 'light';
        ?>

        <div class="tabs">
            <button class="tab active" data-tab="profile">基本信息</button>
            <button class="tab" data-tab="password">修改密码</button>
            <button class="tab" data-tab="theme">主题设置</button>
        </div>

        <form method="post" class="tab-content" id="profile-form">
            <input type="hidden" name="action" value="profile">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($member['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>邮箱</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">保存修改</button>
            </div>
        </form>

        <form method="post" class="tab-content hide" id="password-form">
            <input type="hidden" name="action" value="password">
            <div class="form-group">
                <label>原密码</label>
                <input type="password" name="old_password" required>
            </div>
            <div class="form-group">
                <label>新密码</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>确认密码</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">修改密码</button>
            </div>
        </form>

        <form method="post" class="tab-content hide" id="theme-form">
            <input type="hidden" name="action" value="theme">
            <div class="form-group">
                <label>主题模式</label>
                <div class="flex gap-md mt-sm">
                    <label class="flex items-center gap-sm">
                        <input type="radio" name="theme" value="light" <?php echo $currentTheme == 'light' ? 'checked' : ''; ?>>
                        <span>日间模式</span>
                    </label>
                    <label class="flex items-center gap-sm">
                        <input type="radio" name="theme" value="dark" <?php echo $currentTheme == 'dark' ? 'checked' : ''; ?>>
                        <span>夜间模式</span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">保存设置</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');
    const forms = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const tabName = this.dataset.tab;
            forms.forEach(form => {
                form.style.display = form.id === tabName + '-form' ? 'block' : 'none';
            });
        });
    });
});
</script>
