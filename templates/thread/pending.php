<div class="card">
    <div class="card-header">
        <h2 class="font-semibold">发布成功</h2>
    </div>
    <div class="card-body">
        <div class="text-center py-8">
            <div class="avatar avatar-xl text-success mb-4">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($template_message); ?></h3>
            <?php $template_pendingType = (string)($template_type ?? 'thread'); ?>
            <p class="text-muted mb-6">
                <?php echo $template_pendingType === 'post' ? '您的回帖已提交，等待管理员审核后会显示在主题中。' : '您的主题已提交，等待管理员审核后会显示在论坛中。'; ?>
            </p>
            <?php if ($template_pendingType === 'post' && !empty($template_thread['tid'])): ?>
                <a href="index.php?c=thread&a=index&tid=<?php echo (int)$template_thread['tid']; ?>" class="btn btn-primary">返回主题</a>
            <?php else: ?>
                <a href="index.php" class="btn btn-primary">返回首页</a>
            <?php endif; ?>
        </div>
    </div>
</div>
