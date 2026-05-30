<?php include __DIR__ . '/../member/_profile_header.php'; ?>
<?php include __DIR__ . '/../member/_profile_nav.php'; ?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="font-semibold">私信</h2>
        </div>
        <a href="index.php?c=pm&a=send" class="btn btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 2L11 13"></path>
                <path d="M22 2L15 22l-4-9-9-4 20-7z"></path>
            </svg>
            写私信
        </a>
    </div>

    <div class="card-body-flush">
        <?php if (!empty($template_messages)): ?>
            <div class="list-stack">
                <?php foreach ($template_messages as $message): ?>
                    <?php $partner = $template_users[$message['partner_uid']] ?? null; ?>
                    <a href="index.php?c=pm&a=view&uid=<?php echo (int)$message['partner_uid']; ?>" class="list-link">
                        <div class="avatar avatar-md text-primary">
                            <?php echo \Lib\Helper::getAvatarInitial($partner['username'] ?? '?'); ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-text"><?php echo htmlspecialchars($partner['username'] ?? '已删除用户'); ?></span>
                                <?php if ((int)($message['unread_num'] ?? 0) > 0): ?>
                                    <span class="badge badge-xs badge-primary"><?php echo (int)$message['unread_num']; ?> 条未读</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm text-muted mt-1">
                                <span><?php echo \Lib\Helper::formatTime((int)$message['dateline']); ?></span>
                                <span class="mx-1">·</span>
                                <span class="truncate"><?php echo htmlspecialchars(mb_substr($message['content'], 0, 100)) . (mb_strlen($message['content']) > 100 ? '...' : ''); ?></span>
                            </div>
                        </div>
                        <?php if ((int)($message['unread_num'] ?? 0) > 0): ?>
                            <span class="w-2 h-2 rounded-full bg-primary flex-shrink-0 ml-2"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>暂无私信</p>
            </div>
        <?php endif; ?>

        <?php if ($template_pages > 1): ?>
            <div class="section-footer">
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=pm&a=inbox'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
