<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <h2 class="font-semibold">通知</h2>
    </div>

    <div class="p-0">
        <?php if (!empty($template_notifies)): ?>
            <div class="divide-y">
                <?php foreach ($template_notifies as $notify): ?>
                    <div class="flex items-center gap-3 p-3 hover:bg-hover transition-colors">
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
                                <span><?php echo date('Y-m-d', $notify['dateline']); ?></span>
                            </div>
                        </div>
                        <?php if (empty($notify['status'])): ?>
                            <span class="w-2 h-2 rounded-full bg-primary flex-shrink-0 ml-2"></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-8 text-center text-muted">
                <p>暂无通知</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($template_pages) && $template_pages > 1): ?>
            <div class="p-4 border-t border-border">
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=notify&a=index'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>