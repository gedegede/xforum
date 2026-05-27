<div class="card">
    <div class="card-header">
        <h2 class="font-semibold">在线用户列表</h2>
        <p class="text-sm text-muted">查看当前活跃成员、用户组与最近浏览主题。</p>
    </div>
    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">用户名</th>
                        <th class="whitespace-nowrap">用户组</th>
                        <th class="whitespace-nowrap">活跃时间</th>
                        <th>最后浏览主题</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_onlineUsers)): ?>
                    <?php foreach ($template_onlineUsers as $online): ?>
                        <tr>
                            <td>
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
                            <td>
                                <span class="badge badge-soft">
                                    <?php echo htmlspecialchars($online['group_name']); ?>
                                </span>
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="flex items-center gap-2 text-sm text-muted">
                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                    <?php echo \Lib\Helper::formatTime((int)$online['dateline']); ?>
                                </span>
                            </td>
                            <td>
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
                            <td colspan="4" class="table-empty">暂无在线用户</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
