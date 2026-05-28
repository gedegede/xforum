<?php $template_isSelf = true; ?>
<?php include __DIR__ . '/_profile_header.php'; ?>
<?php include __DIR__ . '/_profile_nav.php'; ?>
<style>
.tabs{display:flex;flex-wrap:wrap;gap:var(--space-1);padding:var(--space-1);border-radius:var(--radius);background:var(--soft)}
.tab{flex:1;min-width:168px;padding:var(--space-2) var(--space-4);border:0;border-radius:var(--radius);background:transparent;color:var(--sub);font-size:12px;font-weight:500;text-align:center;cursor:pointer;transition:color .15s ease,background-color .15s ease,box-shadow .15s ease}
.tab:hover{background:var(--hover);color:var(--text)}
.tab.active{background:var(--panel);color:var(--primary);font-weight:600;box-shadow:var(--shadow-sm)}
.avatar-file-input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
.avatar-upload-box{display:flex;flex-direction:column;gap:var(--space-1);max-width:500px;padding:var(--space-4);border:1px dashed var(--border);border-radius:var(--radius);background:var(--panel);cursor:pointer;transition:border-color .15s ease,background-color .15s ease}
.avatar-upload-box:hover{border-color:var(--primary);background:var(--hover)}
.avatar-upload-title{color:var(--text);font-size:14px;font-weight:600}
.avatar-upload-desc{color:var(--muted);font-size:12px}
.member-logout-link{padding:0;border:0;background:transparent;color:var(--danger);font-size:14px;cursor:pointer}
.member-logout-link:hover{text-decoration:underline}
</style>

<div class="card card-clip">

    <div class="card-body">
        <?php if (!empty($template_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>
        <?php if (!empty($template_success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($template_success); ?></div>
        <?php endif; ?>

        <?php
        $jsonData = isset($template_member['json_data']) ? json_decode($template_member['json_data'], true) : [];
        $currentTheme = $jsonData['theme'] ?? 'light';
        $currentCustomCss = $jsonData['custom_css'] ?? '';
        ?>

        <div class="tabs mb-4">
            <button type="button" class="tab active" data-tab="profile">基本信息</button>
            <button type="button" class="tab" data-tab="avatar">上传头像</button>
            <button type="button" class="tab" data-tab="password">修改密码</button>
            <button type="button" class="tab" data-tab="theme">主题设置</button>
        </div>

        <form method="post" class="mb-4" id="profile-form">
            <input type="hidden" name="action" value="profile">
            <div class="form-section">
                <div class="form-section-title">公开资料</div>
                <div class="flex flex-col gap-4">
                    <div class="form-group">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="member_username">用户名</label>
                            <p class="text-xs text-muted mt-0.5">用于发帖、回复和个人主页展示。</p>
                            <?php if ((int)$template_usernameChangeCredit < 0): ?>
                            <p class="text-xs text-warning mt-0.5">修改用户名将消耗 <?php echo abs((int)$template_usernameChangeCredit); ?> 金币</p>
                            <?php elseif ((int)$template_usernameChangeCredit > 0): ?>
                            <p class="text-xs text-success mt-0.5">修改用户名可获得 <?php echo (int)$template_usernameChangeCredit; ?> 金币</p>
                            <?php endif; ?>
                        </div>
                        <input type="text" id="member_username" name="username" value="<?php echo htmlspecialchars($template_member['username']); ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="member_email">邮箱</label>
                            <p class="text-xs text-muted mt-0.5">用于账号识别和后续通知能力。</p>
                        </div>
                        <input type="email" id="member_email" name="email" value="<?php echo htmlspecialchars($template_member['email']); ?>" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">保存修改</button>
            </div>
        </form>

        <form method="post" enctype="multipart/form-data" class="mb-4 hidden" id="avatar-form">
            <input type="hidden" name="action" value="avatar">
            <div class="form-section">
                <div class="form-section-title">上传头像</div>
                <div class="form-group">
                    <div class="flex items-center gap-4">
                        <div class="avatar avatar-xl text-primary">
                            <?php if (!empty($template_member['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($template_member['avatar']); ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo \Lib\Helper::getAvatarInitial($template_member['username']); ?>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="member_avatar">头像图片</label>
                            <p class="text-xs text-muted mt-0.5">上传后压缩为 64x64 PNG，小于 2MB。</p>
                        </div>
                    </div>
                    <label class="avatar-upload-box" for="member_avatar">
                        <span class="avatar-upload-title">选择头像图片</span>
                        <span class="avatar-upload-desc" id="avatar-file-name">JPG / PNG / GIF / WEBP</span>
                    </label>
                    <input type="file" id="member_avatar" name="avatar" class="avatar-file-input" accept="image/jpeg,image/png,image/gif,image/webp" required>
                </div>
            </div>
            <div class="form-actions">
                <?php if (!empty($template_member['avatar'])): ?>
                    <button type="submit" form="avatar-delete-form" class="btn btn-soft">删除头像</button>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">上传头像</button>
            </div>
        </form>
        <form method="post" class="hidden" id="avatar-delete-form">
            <input type="hidden" name="action" value="delete_avatar">
        </form>

        <form method="post" class="mb-4 hidden" id="password-form">
            <input type="hidden" name="action" value="password">
            <div class="form-section">
                <div class="form-section-title">账号安全</div>
                <div class="flex flex-col gap-4">
                    <div class="form-group">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="old_password">原密码</label>
                            <p class="text-xs text-muted mt-0.5">修改密码前需要验证当前密码。</p>
                        </div>
                        <input type="password" id="old_password" name="old_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="new_password">新密码</label>
                            <p class="text-xs text-muted mt-0.5">建议至少 6 位，避免与其他站点重复。</p>
                        </div>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="confirm_password">确认密码</label>
                            <p class="text-xs text-muted mt-0.5">再次输入新密码，确认没有输错。</p>
                        </div>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">修改密码</button>
            </div>
        </form>

        <form method="post" class="hidden" id="theme-form">
            <input type="hidden" name="action" value="theme">
            <div class="form-section">
                <div class="form-section-title">界面偏好</div>
                <div class="flex flex-col gap-4">
                    <div class="form-group">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted">主题模式</label>
                            <p class="text-xs text-muted mt-0.5">系统会在后续访问中保持你的选择。</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3 rounded border cursor-pointer hover:bg-hover transition-colors">
                                <span class="w-10 h-10 rounded bg-panel border border-border"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-sm">日间模式</div>
                                    <div class="text-xs text-muted">明亮背景，适合白天浏览。</div>
                                </div>
                                <input type="radio" name="theme" value="light" <?php echo $currentTheme == 'light' ? 'checked' : ''; ?> class="ml-2">
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded border cursor-pointer hover:bg-hover transition-colors">
                                <span class="w-10 h-10 rounded bg-text border border-border"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-sm">夜间模式</div>
                                    <div class="text-xs text-muted">深色背景，适合低光环境。</div>
                                </div>
                                <input type="radio" name="theme" value="dark" <?php echo $currentTheme == 'dark' ? 'checked' : ''; ?> class="ml-2">
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="custom_css">自定义 CSS</label>
                            <p class="text-xs text-muted mt-0.5">输入自定义样式代码，将应用于全局。</p>
                        </div>
                        <textarea id="custom_css" name="custom_css" rows="4" class="form-control font-mono text-sm" placeholder="body { background-color: #f0f0f0; }"><?php echo htmlspecialchars($currentCustomCss); ?></textarea>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">保存设置</button>
            </div>
        </form>

        <div class="form-section mt-6" data-profile-only>
            <div class="form-section-title">安全操作</div>
            <div>
                <button type="button" onclick="doLogout()" class="member-logout-link">退出登录</button>
                <div class="text-xs text-muted mt-0.5">安全退出当前账号</div>
            </div>
        </div>
    </div>
</div>

<script>
function doLogout() {
    showConfirmModal('确认退出', '确定要退出当前账号吗？', function() {
        window.submitPostUrl('index.php?c=auth&a=logout');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('[data-tab]');
    const forms = document.querySelectorAll('form[id$="-form"]');
    const avatarInput = document.getElementById('member_avatar');
    const avatarFileName = document.getElementById('avatar-file-name');

    function setActiveTab(tabName) {
        const target = document.querySelector('[data-tab="' + tabName + '"]') ? tabName : 'profile';
        tabs.forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === target);
        });
        forms.forEach(form => {
            form.classList.toggle('hidden', form.id !== target + '-form');
        });
        document.querySelectorAll('[data-profile-only]').forEach(item => {
            item.classList.toggle('hidden', target !== 'profile');
        });
    }

    setActiveTab((window.location.hash || '#profile').slice(1));

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab || 'profile';
            history.replaceState(null, '', '#' + tabName);
            setActiveTab(tabName);
        });
    });

    if (avatarInput && avatarFileName) {
        avatarInput.addEventListener('change', function() {
            avatarFileName.textContent = this.files && this.files[0] ? this.files[0].name : 'JPG / PNG / GIF / WEBP';
        });
    }
});
</script>
