<div class="card member-shell">
    <div class="member-hero">
        <div class="member-hero-main">
            <div class="avatar avatar-lg member-hero-avatar">
                <?php echo \Lib\Helper::getAvatarInitial($template_member['username']); ?>
            </div>
            <div class="member-hero-info">
                <h2><?php echo htmlspecialchars($template_member['username']); ?></h2>
                <div class="member-hero-meta">
                    注册于 <?php echo date('Y-m-d', $template_member['reg_date']); ?> · 
                    <?php echo $template_member['thread_num']; ?> 主题 · 
                    <?php echo $template_member['reply_num']; ?> 回复
                </div>
                <div class="member-badges">
                    <span class="badge badge-gray">资料管理</span>
                    <span class="badge badge-gray">账号安全</span>
                    <span class="badge badge-green">主题模式</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card member-tabs-card">
    <div class="tabs member-tabs">
        <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=threads" class="tab<?php echo (isset($_GET['c']) && $_GET['c'] == 'member' && $_GET['a'] == 'profile') ? ' active' : ''; ?>">我的主题</a>
        <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=replies" class="tab">我的回复</a>
        <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=favorites" class="tab">我的收藏</a>
        <a href="index.php?c=member&a=settings" class="tab active">个人设置</a>
        <a href="index.php?c=admin&a=index" class="tab<?php echo (isset($_GET['c']) && $_GET['c'] == 'admin') ? ' active' : ''; ?>">站点设置</a>
    </div>
</div>

<div class="card member-settings-card">
    <div class="card-header member-card-header">
        <div>
            <h2>个人设置</h2>
            <p>维护账号资料、安全信息和界面偏好。</p>
        </div>
    </div>
    <div class="card-body padded">
        <?php if (!empty($template_error)): ?>
            <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>
        <?php if (!empty($template_success)): ?>
            <div class="success"><?php echo htmlspecialchars($template_success); ?></div>
        <?php endif; ?>

        <?php
        $jsonData = isset($template_member['json_data']) ? json_decode($template_member['json_data'], true) : [];
        $currentTheme = $jsonData['theme'] ?? 'light';
        ?>

        <div class="tabs member-form-tabs">
            <button class="tab active" data-tab="profile">基本信息</button>
            <button class="tab" data-tab="password">修改密码</button>
            <button class="tab" data-tab="theme">主题设置</button>
        </div>

        <form method="post" class="member-form-panel" id="profile-form">
            <input type="hidden" name="action" value="profile">
            <div class="settings-section">
                <div class="settings-section-title">公开资料</div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="member_username">用户名</label>
                        <p class="help-text">用于发帖、回复和个人主页展示。</p>
                    </div>
                    <div class="setting-control">
                        <input type="text" id="member_username" name="username" value="<?php echo htmlspecialchars($template_member['username']); ?>" required>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="member_email">邮箱</label>
                        <p class="help-text">用于账号识别和后续通知能力。</p>
                    </div>
                    <div class="setting-control">
                        <input type="email" id="member_email" name="email" value="<?php echo htmlspecialchars($template_member['email']); ?>" required>
                    </div>
                </div>
            </div>
            <div class="settings-actions">
                <button type="submit" class="btn btn-primary">保存修改</button>
            </div>
        </form>

        <form method="post" class="member-form-panel hide" id="password-form">
            <input type="hidden" name="action" value="password">
            <div class="settings-section">
                <div class="settings-section-title">账号安全</div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="old_password">原密码</label>
                        <p class="help-text">修改密码前需要验证当前密码。</p>
                    </div>
                    <div class="setting-control">
                        <input type="password" id="old_password" name="old_password" required>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="new_password">新密码</label>
                        <p class="help-text">建议至少 6 位，避免与其他站点重复。</p>
                    </div>
                    <div class="setting-control">
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="confirm_password">确认密码</label>
                        <p class="help-text">再次输入新密码，确认没有输错。</p>
                    </div>
                    <div class="setting-control">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
            </div>
            <div class="settings-actions">
                <button type="submit" class="btn btn-primary">修改密码</button>
            </div>
        </form>

        <form method="post" class="member-form-panel hide" id="theme-form">
            <input type="hidden" name="action" value="theme">
            <div class="settings-section">
                <div class="settings-section-title">界面偏好</div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label">主题模式</label>
                        <p class="help-text">系统会在后续访问中保持你的选择。</p>
                    </div>
                    <div class="setting-control">
                        <div class="theme-choice-grid">
                            <label class="theme-choice">
                                <span class="theme-preview theme-preview-light"></span>
                                <span>
                                    <strong>日间模式</strong>
                                    <small>明亮背景，适合白天浏览。</small>
                                </span>
                                <input type="radio" name="theme" value="light" <?php echo $currentTheme == 'light' ? 'checked' : ''; ?>>
                            </label>
                            <label class="theme-choice">
                                <span class="theme-preview theme-preview-dark"></span>
                                <span>
                                    <strong>夜间模式</strong>
                                    <small>深色背景，适合低光环境。</small>
                                </span>
                                <input type="radio" name="theme" value="dark" <?php echo $currentTheme == 'dark' ? 'checked' : ''; ?>>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="settings-actions">
                <button type="submit" class="btn btn-primary">保存设置</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.member-form-tabs .tab');
    const forms = document.querySelectorAll('.member-form-panel');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const tabName = this.dataset.tab;
            forms.forEach(form => {
                form.classList.toggle('hide', form.id !== tabName + '-form');
            });
        });
    });
});
</script>
