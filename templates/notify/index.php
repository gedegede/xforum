<div class="card">
    <div class="card-header">
        <h2 class="font-semibold">通知</h2>
    </div>

    <div class="card-body-flush">
        <?php if (!empty($template_notifies)): ?>
            <div class="list-stack">
                <?php foreach ($template_notifies as $notify): ?>
                    <div class="list-link">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-text"><?php echo htmlspecialchars($template_users[$notify['from_uid']]['username'] ?? '系统'); ?></span>
                                <span class="text-text"><?php echo htmlspecialchars($notify['message']); ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-muted mt-1">
                                <?php if (!empty($notify['tid']) && isset($template_threads[$notify['tid']])): ?>
                                    <a href="index.php?c=thread&a=index&tid=<?php echo $notify['tid']; ?><?php echo !empty($notify['pid']) ? '&pid=' . (int)$notify['pid'] : ''; ?>" class="hover:text-primary transition-colors">查看主题</a>
                                    <span>·</span>
                                <?php endif; ?>
                                <span><?php echo \Lib\Helper::formatTime((int)$notify['dateline']); ?></span>
                            </div>
                        </div>
                        <?php if (empty($notify['status'])): ?>
                            <span class="w-2 h-2 rounded-full bg-primary flex-shrink-0 ml-2"></span>
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
