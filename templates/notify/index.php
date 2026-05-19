<div class="card">
    <div class="card-header">
        <h2>通知</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($notifies)): ?>
            <?php foreach ($notifies as $notify): ?>
                <div class="list-item">
                    <div class="item-info">
                        <div class="item-title">
                            <span class="font-bold"><?php echo htmlspecialchars($users[$notify['from_uid']]['username'] ?? '系统'); ?></span>
                            <?php echo htmlspecialchars($notify['message']); ?>
                        </div>
                        <div class="item-meta">
                            <?php if (!empty($notify['tid']) && isset($threads[$notify['tid']])): ?>
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

        <?php if (!empty($pages) && $pages > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="index.php?c=notify&a=index&page=<?php echo $i; ?>" <?php echo $page == $i ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
