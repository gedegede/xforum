<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>管理日志</h2>
    </div>
    <div class="card-body padded">
        <div class="table-container">
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
                        <td><?php echo date('Y-m-d H:i:s', $log['dateline']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-secondary">暂无管理日志</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <?php if ($template_pages > 1): ?>
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=admin&a=logs'); ?>
        <?php endif; ?>
    </div>
</div>
