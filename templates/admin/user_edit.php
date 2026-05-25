<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <h2 class="font-semibold">编辑用户</h2>
    </div>
    <div class="p-4">
        <?php if ($error): ?>
            <div class="p-3 rounded bg-danger-light text-danger mb-4 text-sm"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-4 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">用户名</label>
                <input type="text" name="username" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" value="<?php echo htmlspecialchars($member['username']); ?>" required>
            </div>

            <div class="mb-4 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">邮箱</label>
                <input type="email" name="email" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" value="<?php echo htmlspecialchars($member['email']); ?>" required>
            </div>

            <div class="mb-4 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">密码</label>
                <input type="password" name="password" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" placeholder="不修改密码请留空">
            </div>

            <div class="mb-4 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">用户组</label>
                <select name="gid" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['gid']; ?>" <?php echo $member['gid'] == $group['gid'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($group['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-6 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">状态</label>
                <select name="status" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                    <option value="1" <?php echo $member['status'] == 1 ? 'selected' : ''; ?>>正常</option>
                    <option value="0" <?php echo $member['status'] == 0 ? 'selected' : ''; ?>>禁用</option>
                </select>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">保存修改</button>
                <a href="index.php?c=admin&a=users" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover">取消</a>
            </div>
        </form>
    </div>
</div>