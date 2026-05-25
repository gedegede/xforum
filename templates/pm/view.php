<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <div>
            <h2 class="font-semibold">查看私信</h2>
        </div>
        <a href="index.php?c=pm&a=inbox" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover">返回收件箱</a>
    </div>

    <div class="p-4">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-border">
            <div class="w-12 h-12 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold">
                <?php echo \Lib\Helper::getAvatarInitial($template_sender['username']); ?>
            </div>
            <div>
                <div class="font-semibold"><?php echo htmlspecialchars($template_sender['username']); ?></div>
                <div class="text-sm text-muted">发送于 <?php echo date('Y-m-d H:i', $template_message['dateline']); ?></div>
            </div>
        </div>

        <div class="mb-6 p-4 rounded border bg-soft">
            <?php echo nl2br(htmlspecialchars($template_message['content'])); ?>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-border">
            <a href="index.php?c=pm&a=send&toUid=<?php echo $template_sender['uid']; ?>" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">回复</a>
        </div>
    </div>
</div>