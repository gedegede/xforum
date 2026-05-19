<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>统计信息</h2>
    </div>
    <div class="card-body padded">
        <div class="grid grid-auto gap-lg">
            <div class="stat-box">
                <div class="stat-value"><?php echo $stats['users']; ?></div>
                <div class="stat-label">用户数</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo $stats['threads']; ?></div>
                <div class="stat-label">主题数</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo $stats['forums']; ?></div>
                <div class="stat-label">版块数</div>
            </div>
        </div>
    </div>
</div>