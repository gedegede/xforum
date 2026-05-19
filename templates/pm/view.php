<div class="card">
    <div class="card-header">
        <h2>查看私信</h2>
        <a href="index.php?c=pm&a=inbox" class="btn btn-secondary">返回收件箱</a>
    </div>
    <div class="card-body padded">
        <div class="flex items-center gap-md mb-lg pb-lg border-b">
            <div class="avatar avatar-md"><?php echo strtoupper(substr($sender['username'], 0, 1)); ?></div>
            <div>
                <div class="font-bold"><?php echo htmlspecialchars($sender['username']); ?></div>
                <div class="text-secondary font-xs">发送于 <?php echo date('Y-m-d H:i', $message['dateline']); ?></div>
            </div>
        </div>
        
        <div class="post-content mb-lg">
            <?php echo nl2br(htmlspecialchars($message['content'])); ?>
        </div>
        
        <div class="flex justify-end gap-md pt-lg border-t">
            <a href="index.php?c=pm&a=send&toUid=<?php echo $sender['uid']; ?>" class="btn btn-primary">回复</a>
        </div>
    </div>
</div>
