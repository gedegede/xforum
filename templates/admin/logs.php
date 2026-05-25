<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <h2 class="font-semibold">管理日志</h2>
    </div>
    <div class="p-4">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-soft">
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">ID</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">操作人</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">操作内容</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_logs)): ?>
                    <?php foreach ($template_logs as $log): ?>
                        <tr class="hover:bg-hover transition-colors">
                            <td class="px-4 py-3 border-b border-border"><?php echo $log['did']; ?></td>
                            <td class="px-4 py-3 border-b border-border"><?php echo htmlspecialchars($template_users[$log['uid']]['username'] ?? '未知'); ?></td>
                            <td class="px-4 py-3 border-b border-border"><?php echo htmlspecialchars($log['message']); ?></td>
                            <td class="px-4 py-3 border-b border-border"><?php echo date('Y-m-d H:i:s', $log['dateline']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-muted">暂无管理日志</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($template_pages > 1): ?>
            <div class="mt-4">
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=admin&a=logs'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>