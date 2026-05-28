<?php include __DIR__ . '/../member/_profile_header.php'; ?>
<?php include __DIR__ . '/../member/_profile_nav.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="font-semibold">通知</h2>
    </div>

    <div class="card-body-flush">
        <?php if (!empty($template_notifies)): ?>
            <div class="list-stack">
                <?php foreach ($template_notifies as $notify): ?>
                    <?php $fromUser = $template_users[$notify['from_uid']] ?? null; ?>
                    <?php $threadUrl = 'index.php?c=thread&a=index&tid=' . (int)$notify['tid'] . (!empty($notify['pid']) ? '&pid=' . (int)$notify['pid'] : ''); ?>
                    <div class="list-link" data-notify-row="<?php echo (int)$notify['did']; ?>">
                        <a href="<?php echo $fromUser ? 'index.php?c=member&a=profile&uid=' . (int)$notify['from_uid'] : 'javascript:void(0)'; ?>" class="avatar avatar-sm flex-shrink-0">
                            <?php if ($fromUser && !empty($fromUser['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($fromUser['avatar']); ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo \Lib\Helper::getAvatarInitial($fromUser['username'] ?? '系'); ?>
                            <?php endif; ?>
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <?php if ($fromUser): ?>
                                        <a href="index.php?c=member&a=profile&uid=<?php echo (int)$notify['from_uid']; ?>" class="font-semibold text-primary hover:underline"><?php echo htmlspecialchars($fromUser['username']); ?></a>
                                    <?php else: ?>
                                        <span class="font-semibold text-text">系统</span>
                                    <?php endif; ?>
                                    <?php if (str_contains((string)$notify['message'], '中@了你') && !empty($notify['tid']) && isset($template_threads[$notify['tid']])): ?>
                                        <span class="text-text">在 <a href="<?php echo htmlspecialchars($threadUrl); ?>" target="_blank" class="text-primary hover:underline" data-notify-read="<?php echo (int)$notify['did']; ?>"><?php echo htmlspecialchars($template_threads[$notify['tid']]['subject']); ?></a> 中@了你</span>
                                    <?php elseif (str_contains((string)$notify['message'], '中引用了你的回复') && !empty($notify['tid']) && isset($template_threads[$notify['tid']])): ?>
                                        <span class="text-text">在 <a href="<?php echo htmlspecialchars($threadUrl); ?>" target="_blank" class="text-primary hover:underline" data-notify-read="<?php echo (int)$notify['did']; ?>"><?php echo htmlspecialchars($template_threads[$notify['tid']]['subject']); ?></a> 中引用了你的回复</span>
                                    <?php else: ?>
                                        <span class="text-text"><?php echo htmlspecialchars($notify['message']); ?></span>
                                    <?php endif; ?>
                                    <span class="text-sm text-muted"><?php echo \Lib\Helper::formatTime((int)$notify['dateline']); ?></span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <?php if (!empty($notify['tid']) && isset($template_threads[$notify['tid']])): ?>
                                        <a href="<?php echo htmlspecialchars($threadUrl); ?>" target="_blank" class="btn btn-soft btn-sm" data-notify-read="<?php echo (int)$notify['did']; ?>">查看主题</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if (empty($notify['status'])): ?>
                            <span class="w-2 h-2 rounded-full bg-primary flex-shrink-0 ml-2" data-notify-dot></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>暂无通知</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($template_pages) && $template_pages > 1): ?>
            <div class="section-footer">
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=notify&a=index'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    var link = e.target.closest('[data-notify-read]');
    if (!link) return;
    document.querySelector('[data-notify-row="' + link.dataset.notifyRead + '"] [data-notify-dot]')?.remove();
});
</script>
