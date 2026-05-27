<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">管理日志</h2>
    </div>
    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>操作人</th>
                        <th>操作内容</th>
                        <th>时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_logs)): ?>
                    <?php foreach ($template_logs as $log): ?>
                        <tr>
                            <td><?php echo $log['did']; ?></td>
                            <td><?php echo htmlspecialchars($template_users[$log['uid']]['username'] ?? '未知'); ?></td>
                            <td><?php echo htmlspecialchars($log['message']); ?></td>
                            <td><?php echo \Lib\Helper::formatTime((int)$log['dateline']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="table-empty">暂无管理日志</td>
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
