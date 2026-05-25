<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <div>
            <h2 class="font-semibold">发件箱</h2>
        </div>
        <a href="index.php?c=pm&a=send" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">发送私信</a>
    </div>

    <div class="p-0">
        <?php if (!empty($template_messages)): ?>
            <div class="divide-y">
                <?php foreach ($template_messages as $message): ?>
                    <a href="index.php?c=pm&a=view&pmid=<?php echo $message['pmid']; ?>" class="flex items-center gap-3 p-3 hover:bg-hover transition-colors">
                        <div class="w-10 h-10 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold text-sm flex-shrink-0 overflow-hidden">
                            <?php echo \Lib\Helper::getAvatarInitial($template_users[$message['to_uid']]['username'] ?? '?'); ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-text">发送给：<?php echo htmlspecialchars($template_users[$message['to_uid']]['username'] ?? '已删除用户'); ?></span>
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-soft text-sub">已发送</span>
                            </div>
                            <div class="text-sm text-muted mt-1">
                                <span><?php echo date('Y-m-d H:i', $message['dateline']); ?></span>
                                <span class="mx-1">·</span>
                                <span class="truncate"><?php echo htmlspecialchars(mb_substr($message['content'], 0, 100)) . (mb_strlen($message['content']) > 100 ? '...' : ''); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-8 text-center text-muted">
                <p>暂无发送的私信</p>
            </div>
        <?php endif; ?>

        <?php if ($template_pages > 1): ?>
            <div class="p-4 border-t border-border">
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=pm&a=outbox'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>