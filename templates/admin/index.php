<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border bg-soft">
        <h2 class="font-semibold">统计信息</h2>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-3 gap-4">
            <div class="p-4 rounded border bg-soft text-center">
                <div class="text-2xl font-bold text-primary mb-1"><?php echo $template_stats['users']; ?></div>
                <div class="text-sm text-muted">用户数</div>
            </div>
            <div class="p-4 rounded border bg-soft text-center">
                <div class="text-2xl font-bold text-primary mb-1"><?php echo $template_stats['threads']; ?></div>
                <div class="text-sm text-muted">主题数</div>
            </div>
            <div class="p-4 rounded border bg-soft text-center">
                <div class="text-2xl font-bold text-primary mb-1"><?php echo $template_stats['forums']; ?></div>
                <div class="text-sm text-muted">版块数</div>
            </div>
        </div>
    </div>
</div>