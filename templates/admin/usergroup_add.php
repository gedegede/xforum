<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border bg-soft">
        <h2 class="font-semibold">添加用户组</h2>
    </div>
    <div class="p-4">
        <?php if ($template_error): ?>
            <div class="p-3 rounded bg-danger-light text-danger mb-4 text-sm"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-4 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">用户组名称</label>
                <input type="text" name="title" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
            </div>

            <div class="mb-4 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">用户组类型</label>
                <select name="group_type" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                    <option value="system">系统组</option>
                    <option value="special">特殊组</option>
                    <option value="member" selected>会员组</option>
                </select>
            </div>

            <div class="mb-4 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">积分下限</label>
                <input type="number" name="credit_lower" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" value="0">
            </div>

            <div class="mb-6">
                <label class="text-sm font-medium text-text mb-2 block">权限设置</label>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="can_manage" value="1" class="rounded">
                        <span>允许管理主题</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="thread_need_approve" value="1" class="rounded">
                        <span>发主题需要审核</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="post_need_approve" value="1" class="rounded">
                        <span>发回帖需要审核</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">添加用户组</button>
                <a href="index.php?c=admin&a=usergroups" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover">取消</a>
            </div>
        </form>
    </div>
</div>