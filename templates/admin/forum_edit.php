<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">编辑版块</h2>
    </div>
    <div class="card-body">
        <?php if ($template_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?c=admin&a=forumEdit&fid=<?php echo $template_forum['fid']; ?>">
            <div class="form-field">
                <label class="form-label">版块名称</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($template_forum['name']); ?>" required>
            </div>

            <div class="form-field">
                <label class="form-label">上级版块</label>
                <select name="up_fid" class="form-control">
                    <option value="0" <?php echo $template_forum['up_fid'] == 0 ? 'selected' : ''; ?>>无（顶级版块）</option>
                    <?php foreach ($template_parentForums as $pf): ?>
                        <?php if ($pf['fid'] != $template_forum['fid']): ?>
                        <option value="<?php echo $pf['fid']; ?>" <?php echo $template_forum['up_fid'] == $pf['fid'] ? 'selected' : ''; ?>>
                            <?php echo str_repeat('→ ', $pf['depth'] ?? 0) . htmlspecialchars($pf['name']); ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field form-field-lg">
                <label class="form-label">状态</label>
                <select name="status" class="form-control">
                    <option value="1" <?php echo $template_forum['status'] == 1 ? 'selected' : ''; ?>>启用</option>
                    <option value="0" <?php echo $template_forum['status'] == 0 ? 'selected' : ''; ?>>禁用</option>
                </select>
            </div>

            <?php
            $forumGroupPermissions = $template_forum['group_permissions'] ?? [];
            $forumPermissionLabels = [
                'view' => '允许浏览',
                'thread' => '允许发主题',
                'reply' => '允许发回帖',
            ];
            ?>
            <?php foreach ($forumPermissionLabels as $key => $label): ?>
                <div class="mb-6">
                    <label class="form-label form-label-block"><?php echo $label; ?></label>
                    <div class="check-grid">
                        <?php foreach ($template_usergroups as $group): ?>
                            <?php $allowedGroups = array_map('intval', (array)($forumGroupPermissions[$key] ?? [])); ?>
                            <label class="check-row">
                                <input type="checkbox" name="group_<?php echo $key; ?>[]" value="<?php echo (int)$group['gid']; ?>" class="rounded" <?php echo in_array((int)$group['gid'], $allowedGroups, true) ? 'checked' : ''; ?>>
                                <span><?php echo htmlspecialchars($group['title']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">保存修改</button>
                <a href="index.php?c=admin&a=forums" class="btn btn-soft">取消</a>
            </div>
        </form>
    </div>
</div>
