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
                <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log['did']; ?></td>
                        <td><?php echo htmlspecialchars($users[$log['uid']]['username'] ?? '未知'); ?></td>
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

        <?php if ($pages > 1): ?>
            <div class="pagination mt-lg">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a href="index.php?c=admin&a=logs&page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
