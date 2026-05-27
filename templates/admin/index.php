<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">统计信息</h2>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-3 gap-4">
            <div class="stat-box">
                <div class="stat-box-value"><?php echo $template_stats['users']; ?></div>
                <div class="stat-box-label">用户数</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-value"><?php echo $template_stats['threads']; ?></div>
                <div class="stat-box-label">主题数</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-value"><?php echo $template_stats['forums']; ?></div>
                <div class="stat-box-label">版块数</div>
            </div>
        </div>
    </div>
</div>

<div class="card card-clip mt-4">
    <div class="card-header">
        <h2 class="font-semibold">系统信息</h2>
    </div>
    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <tbody>
                    <?php foreach ($template_systemInfo as $label => $value): ?>
                        <tr>
                            <th><?php echo htmlspecialchars($label); ?></th>
                            <td>
                                <?php echo htmlspecialchars((string)$value); ?>
                                <?php if ($label === 'OPcache' && str_starts_with((string)$value, '已启用')): ?>
                                    <form method="post" action="index.php?c=admin&a=index" class="inline">
                                        <input type="hidden" name="cache_action" value="opcache_reset">
                                        <button type="submit" class="text-primary ml-2">[清空缓存]</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($label === 'APCu' && str_starts_with((string)$value, '已启用')): ?>
                                    <form method="post" action="index.php?c=admin&a=index" class="inline">
                                        <input type="hidden" name="cache_action" value="apcu_clear">
                                        <button type="submit" class="text-primary ml-2">[清空缓存]</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
