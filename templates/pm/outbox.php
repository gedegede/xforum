<div class="card">
    <div class="card-header">
        <h2>发件箱</h2>
        <a href="index.php?c=pm&a=send" class="btn btn-primary">发送私信</a>
    </div>
    <div class="card-body">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <a href="index.php?c=pm&a=view&pmid=<?php echo $message['pmid']; ?>" class="list-item">
                    <div class="avatar avatar-sm">
                        <?php echo strtoupper(substr($users[$message['to_uid']]['username'] ?? '?', 0, 1)); ?>
                    </div>
                    <div class="item-info">
                        <div class="item-title">
                            <span class="font-bold">发送给: <?php echo htmlspecialchars($users[$message['to_uid']]['username'] ?? '未知用户'); ?></span>
                            <span class="text-secondary font-xs ml-sm"><?php echo date('Y-m-d', $message['dateline']); ?></span>
                        </div>
                        <div class="item-meta"><?php echo htmlspecialchars(mb_substr($message['content'], 0, 100)) . (mb_strlen($message['content']) > 100 ? '...' : ''); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>暂无发送的私信</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($pages) && $pages > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="index.php?c=pm&a=outbox&page=<?php echo $i; ?>" <?php echo $page == $i ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
