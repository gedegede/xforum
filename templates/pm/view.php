<div class="card">
    <div class="card-header">
        <div>
            <h2 class="font-semibold">查看私信</h2>
        </div>
        <a href="index.php?c=pm&a=inbox" class="btn btn-soft">返回收件箱</a>
    </div>

    <div class="card-body">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-border">
            <div class="avatar avatar-md text-primary">
                <?php echo \Lib\Helper::getAvatarInitial($template_sender['username']); ?>
            </div>
            <div>
                <div class="font-semibold"><?php echo htmlspecialchars($template_sender['username']); ?></div>
                <div class="text-sm text-muted">发送于 <?php echo \Lib\Helper::formatTime((int)$template_message['dateline']); ?></div>
            </div>
        </div>

        <div class="summary-box">
            <?php echo nl2br(htmlspecialchars($template_message['content'])); ?>
        </div>

        <div class="form-actions pt-6 border-t border-border">
            <a href="index.php?c=pm&a=send&toUid=<?php echo $template_sender['uid']; ?>" class="btn btn-primary">回复</a>
        </div>
    </div>
</div>
