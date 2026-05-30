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
        <?php if (!empty($template_success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($template_success); ?></div>
        <?php endif; ?>
        <?php if (!empty($template_cacheWarnings)): ?>
            <div class="admin-warning-box mb-4" role="alert">
                <div class="admin-warning-title">运行环境必须开启缓存扩展</div>
                <ul class="admin-warning-list">
                    <?php foreach ($template_cacheWarnings as $warning): ?>
                        <li><?php echo htmlspecialchars($warning); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="table-wrap">
            <table class="table">
                <tbody>
                    <?php foreach ($template_systemInfo as $label => $value): ?>
                        <?php
                        $valueText = (string)$value;
                        $isRequiredCache = in_array($label, ['OPcache', 'APCu'], true);
                        $isCacheEnabled = str_starts_with($valueText, '已启用');
                        ?>
                        <tr>
                            <th class="table-nowrap"><?php echo htmlspecialchars($label); ?></th>
                            <td>
                                <div class="flex items-center justify-between gap-2">
                                    <?php if ($isRequiredCache): ?>
                                        <span class="badge <?php echo $isCacheEnabled ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo htmlspecialchars($valueText); ?>
                                        </span>
                                    <?php else: ?>
                                        <span><?php echo htmlspecialchars($valueText); ?></span>
                                    <?php endif; ?>
                                    <div class="flex items-center justify-end gap-2">
                                <?php if ($label === 'OPcache' && $isCacheEnabled): ?>
                                    <form method="post" action="index.php?c=admin&a=index">
                                        <input type="hidden" name="cache_action" value="opcache_reset">
                                        <button type="submit" class="btn btn-soft btn-sm">清空缓存</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($label === 'APCu' && $isCacheEnabled): ?>
                                    <form method="post" action="index.php?c=admin&a=index">
                                        <input type="hidden" name="cache_action" value="apcu_clear">
                                        <button type="submit" class="btn btn-soft btn-sm">清空缓存</button>
                                    </form>
                                <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
