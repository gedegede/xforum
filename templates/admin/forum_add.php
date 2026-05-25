<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border bg-soft">
        <h2 class="font-semibold">添加版块</h2>
    </div>
    <div class="p-4">
        <?php if ($template_error): ?>
            <div class="p-3 rounded bg-danger-light text-danger mb-4 text-sm"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-4 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">版块名称</label>
                <input type="text" name="name" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
            </div>

            <div class="mb-6 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">上级版块</label>
                <select name="up_fid" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                    <option value="0">无（顶级版块）</option>
                    <?php foreach ($template_parentForums as $forum): ?>
                        <option value="<?php echo $forum['fid']; ?>">
                            <?php echo str_repeat('├─ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">添加版块</button>
                <a href="index.php?c=admin&a=forums" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover">取消</a>
            </div>
        </form>
    </div>
</div>