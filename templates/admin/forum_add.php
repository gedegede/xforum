<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">添加版块</h2>
    </div>
    <div class="card-body">
        <?php if ($template_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-field">
                <label class="form-label">版块名称</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-field form-field-lg">
                <label class="form-label">上级版块</label>
                <select name="up_fid" class="form-control">
                    <option value="0">无（顶级版块）</option>
                    <?php foreach ($template_parentForums as $forum): ?>
                        <option value="<?php echo $forum['fid']; ?>">
                            <?php echo str_repeat('→ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">添加版块</button>
                <a href="index.php?c=admin&a=forums" class="btn btn-soft">取消</a>
            </div>
        </form>
    </div>
</div>
