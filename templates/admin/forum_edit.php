<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>编辑版块</h2>
    </div>
    <div class="card-body padded">
        <?php if ($template_error): ?>
            <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?c=admin&a=forumEdit&fid=<?php echo $template_forum['fid']; ?>">
            <div class="form-group">
                <label>版块名称</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($template_forum['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>上级版块</label>
                <select name="up_fid">
                    <option value="0" <?php echo $template_forum['up_fid'] == 0 ? 'selected' : ''; ?>>无（顶级版块）</option>
                    <?php foreach ($template_parentForums as $pf): ?>
                        <?php if ($pf['fid'] != $template_forum['fid']): ?>
                        <option value="<?php echo $pf['fid']; ?>" <?php echo $template_forum['up_fid'] == $pf['fid'] ? 'selected' : ''; ?>>
                            <?php echo str_repeat('├─ ', $pf['depth'] ?? 0) . htmlspecialchars($pf['name']); ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>状态</label>
                <select name="status">
                    <option value="1" <?php echo $template_forum['status'] == 1 ? 'selected' : ''; ?>>启用</option>
                    <option value="0" <?php echo $template_forum['status'] == 0 ? 'selected' : ''; ?>>禁用</option>
                </select>
            </div>
            <div class="flex justify-end gap-md mt-lg">
                <button type="submit" class="btn btn-primary">保存修改</button>
                <a href="index.php?c=admin&a=forums" class="btn btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>
