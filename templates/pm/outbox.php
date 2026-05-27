<div class="card">
    <div class="card-header">
        <div>
            <h2 class="font-semibold">发件箱</h2>
        </div>
        <a href="index.php?c=pm&a=send" class="btn btn-primary">发送私信</a>
    </div>

    <div class="card-body-flush">
        <?php if (!empty($template_messages)): ?>
            <div class="list-stack">
                <?php foreach ($template_messages as $message): ?>
                    <a href="index.php?c=pm&a=view&pmid=<?php echo $message['pmid']; ?>" class="list-link">
                        <div class="avatar avatar-md text-primary">
                            <?php echo \Lib\Helper::getAvatarInitial($template_users[$message['to_uid']]['username'] ?? '?'); ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-text">发送给：<?php echo htmlspecialchars($template_users[$message['to_uid']]['username'] ?? '已删除用户'); ?></span>
                                <span class="badge badge-xs badge-soft">已发送</span>
                            </div>
                            <div class="text-sm text-muted mt-1">
                                <span><?php echo \Lib\Helper::formatTime((int)$message['dateline']); ?></span>
                                <span class="mx-1">·</span>
                                <span class="truncate"><?php echo htmlspecialchars(mb_substr($message['content'], 0, 100)) . (mb_strlen($message['content']) > 100 ? '...' : ''); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>暂无发送的私信</p>
            </div>
        <?php endif; ?>

        <?php if ($template_pages > 1): ?>
            <div class="section-footer">
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=pm&a=outbox'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
