<div class="card">
    <div class="card-header">
        <h2><?php echo $from === 'create' ? '发布新主题' : '论坛导航'; ?></h2>
    </div>
    <div class="card-body padded">
        <?php if ($from === 'create'): ?>
            <div class="text-secondary mb-lg">请选择要发布主题的版块</div>
        <?php endif; ?>
        <?php if (!empty($forums)): ?>
            <div class="forum-list">
                <?php foreach ($forums as $forum): ?>
                    <a href="<?php echo $from === 'create' ? 'index.php?c=thread&a=create&fid=' . $forum['fid'] : 'index.php?c=forum&a=index&fid=' . $forum['fid']; ?>" class="list-item forum-item">
                        <div class="item-info">
                            <div class="item-title">
                                <?php echo str_repeat('├─ ', $forum['depth']) . htmlspecialchars($forum['name']); ?>
                            </div>
                            <div class="item-meta">
                                <?php if (!empty($forum['description'])): ?>
                                    <?php echo htmlspecialchars($forum['description']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="item-arrow"></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">暂无论坛版块</div>
        <?php endif; ?>
    </div>
</div>
