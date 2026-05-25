<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <h2 class="font-semibold">在线用户列表</h2>
        <p class="text-sm text-muted">查看当前活跃成员、用户组与最近浏览主题。</p>
    </div>
    <div class="p-4">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left px-4 py-2 text-sm font-medium text-text whitespace-nowrap">用户名</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text whitespace-nowrap">用户组</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text whitespace-nowrap">活跃时间</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">最后浏览主题</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_onlineUsers)): ?>
                    <?php foreach ($template_onlineUsers as $online): ?>
                        <tr class="border-b border-border hover:bg-hover transition-colors">
                            <td class="px-4 py-3">
                                <?php if ($online['uid'] > 0): ?>
                                <a href="index.php?c=member&a=profile&uid=<?php echo $online['uid']; ?>" class="flex items-center gap-2 font-semibold text-primary hover:text-primary-dark transition-colors">
                                    <span class="w-7 h-7 rounded-full bg-primary-light text-primary flex items-center justify-center text-sm font-medium"><?php echo \Lib\Helper::getAvatarInitial($online['username']); ?></span>
                                    <span class="whitespace-nowrap"><?php echo htmlspecialchars($online['username']); ?></span>
                                </a>
                                <?php else: ?>
                                <span class="flex items-center gap-2 font-semibold text-text">
                                    <span class="w-7 h-7 rounded-full bg-muted-light text-muted flex items-center justify-center text-sm font-medium">游</span>
                                    <span class="whitespace-nowrap"><?php echo htmlspecialchars($online['username']); ?></span>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted-light text-muted">
                                    <?php echo htmlspecialchars($online['group_name']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="flex items-center gap-2 text-sm text-muted">
                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                    <?php echo date('Y-m-d H:i:s', $online['dateline']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (!empty($online['thread_subject'])): ?>
                                <a href="index.php?c=thread&a=index&tid=<?php echo $online['tid']; ?>" class="font-semibold text-primary hover:text-primary-dark transition-colors">
                                    <?php echo htmlspecialchars($online['thread_subject']); ?>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">暂无记录</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-muted">暂无在线用户</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
