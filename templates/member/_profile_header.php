<div class="card">
    <div class="card-body">
        <div class="flex items-center gap-4">
            <div class="avatar avatar-xl text-primary">
                <?php echo \Lib\Helper::getAvatarInitial($template_member['username']); ?>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($template_member['username']); ?></h2>
                <div class="text-sm text-muted mb-2">
                    <?php if (!empty($template_memberGroup)): ?>
                        <span class="text-primary font-semibold"><?php echo htmlspecialchars($template_memberGroup['title'] ?? ''); ?></span>
                        <span> · </span>
                    <?php endif; ?>
                    注册于 <?php echo \Lib\Helper::formatTime((int)$template_member['reg_date']); ?>
                </div>
            </div>
        </div>
    </div>
</div>
