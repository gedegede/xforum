<div class="bg-panel border border-border rounded shadow-sm">
    <div class="p-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold text-2xl flex-shrink-0 overflow-hidden">
                <?php echo \Lib\Helper::getAvatarInitial($template_member['username']); ?>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($template_member['username']); ?></h2>
                <div class="text-sm text-muted mb-2">
                    注册于 <?php echo date('Y-m-d', $template_member['reg_date']); ?> ·
                    <?php echo $template_member['thread_num']; ?> 主题 ·
                    <?php echo $template_member['reply_num']; ?> 回复 ·
                    <?php echo (int)($template_member['credit'] ?? 0); ?> 金币
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-soft text-sub">资料管理</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-soft text-sub">账号安全</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success">
                        <?php echo (int)($template_member['credit'] ?? 0); ?> 金币
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-panel border border-border rounded shadow-sm">
    <div class="p-0">
        <div class="flex flex-wrap border-b">
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=threads"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (isset($_GET['c']) && $_GET['c'] == 'member' && $_GET['a'] == 'profile') ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                我的主题
            </a>
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=replies"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors border-transparent text-sub hover:text-text hover:border-border">
                我的回复
            </a>
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=favorites"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors border-transparent text-sub hover:text-text hover:border-border">
                我的收藏
            </a>
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=credits"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors border-transparent text-sub hover:text-text hover:border-border">
                金币明细
            </a>
            <a href="index.php?c=member&a=settings"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors border-primary text-primary">
                个人设置
            </a>
            <a href="index.php?c=admin&a=index"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (isset($_GET['c']) && $_GET['c'] == 'admin') ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                站点设置
            </a>
        </div>
    </div>
</div>

<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <div>
            <h2 class="font-semibold">个人设置</h2>
            <p class="text-sm text-muted mt-1">维护账号资料、安全信息和界面偏好。</p>
        </div>
    </div>

    <div class="p-4">
        <?php if (!empty($template_error)): ?>
            <div class="p-3 rounded bg-danger-light text-danger mb-4 text-sm"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>
        <?php if (!empty($template_success)): ?>
            <div class="p-3 rounded bg-success-light text-success mb-4 text-sm"><?php echo htmlspecialchars($template_success); ?></div>
        <?php endif; ?>

        <?php
        $jsonData = isset($template_member['json_data']) ? json_decode($template_member['json_data'], true) : [];
        $currentTheme = $jsonData['theme'] ?? 'light';
        ?>

        <div class="flex border-b mb-4">
            <button class="px-4 py-2 text-sm font-medium border-b-2 transition-colors border-primary text-primary" data-tab="profile">基本信息</button>
            <button class="px-4 py-2 text-sm font-medium border-b-2 transition-colors border-transparent text-sub hover:text-text hover:border-border" data-tab="password">修改密码</button>
            <button class="px-4 py-2 text-sm font-medium border-b-2 transition-colors border-transparent text-sub hover:text-text hover:border-border" data-tab="theme">主题设置</button>
        </div>

        <form method="post" class="mb-4" id="profile-form">
            <input type="hidden" name="action" value="profile">
            <div class="mb-4 p-4 rounded border bg-soft">
                <div class="text-sm font-medium text-text mb-4">公开资料</div>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1.5">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="member_username">用户名</label>
                            <p class="text-xs text-muted mt-0.5">用于发帖、回复和个人主页展示。</p>
                        </div>
                        <input type="text" id="member_username" name="username" value="<?php echo htmlspecialchars($template_member['username']); ?>" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="member_email">邮箱</label>
                            <p class="text-xs text-muted mt-0.5">用于账号识别和后续通知能力。</p>
                        </div>
                        <input type="email" id="member_email" name="email" value="<?php echo htmlspecialchars($template_member['email']); ?>" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">保存修改</button>
            </div>
        </form>

        <form method="post" class="mb-4 hidden" id="password-form">
            <input type="hidden" name="action" value="password">
            <div class="mb-4 p-4 rounded border bg-soft">
                <div class="text-sm font-medium text-text mb-4">账号安全</div>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1.5">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="old_password">原密码</label>
                            <p class="text-xs text-muted mt-0.5">修改密码前需要验证当前密码。</p>
                        </div>
                        <input type="password" id="old_password" name="old_password" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="new_password">新密码</label>
                            <p class="text-xs text-muted mt-0.5">建议至少 6 位，避免与其他站点重复。</p>
                        </div>
                        <input type="password" id="new_password" name="new_password" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <div class="flex-1 min-w-0">
                            <label class="text-sm text-muted" for="confirm_password">确认密码</label>
                            <p class="text-xs text-muted mt-0.5">再次输入新密码，确认没有输错。</p>
                        </div>
                        <input type="password" id="confirm_password" name="confirm_password" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">修改密码</button>
            </div>
        </form>

        <form method="post" class="hidden" id="theme-form">
            <input type="hidden" name="action" value="theme">
            <div class="mb-4 p-4 rounded border bg-soft">
                <div class="text-sm font-medium text-text mb-4">界面偏好</div>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1.5">
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
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">保存设置</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('[data-tab]');
    const forms = document.querySelectorAll('form[id$="-form"]');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => {
                t.classList.remove('border-primary', 'text-primary');
                t.classList.add('border-transparent', 'text-sub', 'hover:text-text', 'hover:border-border');
            });
            this.classList.remove('border-transparent', 'text-sub', 'hover:text-text', 'hover:border-border');
            this.classList.add('border-primary', 'text-primary');

            const tabName = this.dataset.tab;
            forms.forEach(form => {
                form.classList.toggle('hidden', form.id !== tabName + '-form');
            });
        });
    });
});
</script>