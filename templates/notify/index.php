<div class="card">
    <div class="thread-hero">
        <h2>通知</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($template_notifies)): ?>
            <?php foreach ($template_notifies as $notify): ?>
                <div class="list-item">
                    <div class="item-info flex-1">
                        <div class="thread-title">
                            <span class="font-bold"><?php echo htmlspecialchars($template_users[$notify['from_uid']]['username'] ?? '系统'); ?></span>
                            <span class="item-title" style="white-space:normal;"><?php echo htmlspecialchars($notify['message']); ?></span>
                        </div>
                        <div class="item-meta">
                            <?php if (!empty($notify['tid']) && isset($template_threads[$notify['tid']])): ?>
                                <a href="index.php?c=thread&a=index&tid=<?php echo $notify['tid']; ?>">查看主题</a>
                                <span>·</span>
                            <?php endif; ?>
                            <span><?php echo date('Y-m-d', $notify['dateline']); ?></span>
                        </div>
                    </div>
                    <?php if (empty($notify['status'])): ?>
                        <div class="status-dot ml-sm"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>暂无通知</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($template_pages) && $template_pages > 1): ?>
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=notify&a=index'); ?>
        <?php endif; ?>
    </div>
</div>
