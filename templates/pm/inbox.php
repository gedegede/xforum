<div class="card">
    <div class="card-header">
        <h2>收件箱</h2>
        <a href="index.php?c=pm&a=send" class="btn btn-primary">发送私信</a>
    </div>
    <div class="card-body">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <a href="index.php?c=pm&a=view&pmid=<?php echo $message['pmid']; ?>" class="list-item">
                    <div class="avatar avatar-sm">
                        <?php echo strtoupper(substr($users[$message['uid']]['username'] ?? '?', 0, 1)); ?>
                    </div>
                    <div class="item-info flex-1">
                        <div class="item-title">
                            <span class="font-bold"><?php echo htmlspecialchars($users[$message['uid']]['username'] ?? '已删除用户'); ?></span>
                            <span class="text-secondary font-xs ml-sm"><?php echo date('Y-m-d', $message['dateline']); ?></span>
                        </div>
                        <div class="item-meta"><?php echo htmlspecialchars(mb_substr($message['content'], 0, 100)) . (mb_strlen($message['content']) > 100 ? '...' : ''); ?></div>
                    </div>
                    <?php if (empty($message['is_read'])): ?>
                        <div class="status-dot ml-sm"></div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>暂无私信</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($pages) && $pages > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="index.php?c=pm&a=inbox&page=<?php echo $i; ?>" <?php echo $page == $i ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
