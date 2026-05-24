<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>添加版块</h2>
    </div>
    <div class="card-body padded">
        <?php if ($template_error): ?>
            <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>版块名称</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>上级版块</label>
                <select name="up_fid">
                    <option value="0">无（顶级版块）</option>
                    <?php foreach ($template_parentForums as $forum): ?>
                        <option value="<?php echo $forum['fid']; ?>">
                            <?php echo str_repeat('├─ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end gap-md mt-lg">
                <button type="submit" class="btn btn-primary">添加版块</button>
                <a href="index.php?c=admin&a=forums" class="btn btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>
