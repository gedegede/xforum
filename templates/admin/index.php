<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>统计信息</h2>
    </div>
    <div class="card-body padded">
        <div class="grid grid-auto gap-lg">
            <div class="box">
                <div class="font-bold"><?php echo $template_stats['users']; ?></div>
                <div class="muted">用户数</div>
            </div>
            <div class="box">
                <div class="font-bold"><?php echo $template_stats['threads']; ?></div>
                <div class="muted">主题数</div>
            </div>
            <div class="box">
                <div class="font-bold"><?php echo $template_stats['forums']; ?></div>
                <div class="muted">版块数</div>
            </div>
        </div>
    </div>
</div>