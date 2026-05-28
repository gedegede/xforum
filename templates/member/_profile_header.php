<div class="card">
    <div class="card-body">
        <div class="flex items-center gap-4">
            <div class="avatar avatar-xl text-primary">
                <?php if (!empty($template_member['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($template_member['avatar']); ?>" alt="" class="w-full h-full object-cover">
                <?php else: ?>
                    <?php echo \Lib\Helper::getAvatarInitial($template_member['username']); ?>
                <?php endif; ?>
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
                <?php if (empty($template_isSelf)): ?>
                    <a href="index.php?c=pm&a=send&toUid=<?php echo (int)$template_member['uid']; ?>" class="btn btn-soft btn-sm">给TA私信</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
