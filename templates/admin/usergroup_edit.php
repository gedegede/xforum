<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>编辑用户组</h2>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>用户组名称</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($group['title']); ?>" required>
            </div>
            <div class="form-group">
                <label>用户组类型</label>
                <select name="group_type">
                    <option value="system" <?php echo $group['group_type'] == 'system' ? 'selected' : ''; ?>>系统组</option>
                    <option value="special" <?php echo $group['group_type'] == 'special' ? 'selected' : ''; ?>>特殊组</option>
                    <option value="member" <?php echo $group['group_type'] == 'member' ? 'selected' : ''; ?>>会员组</option>
                </select>
            </div>
            <div class="form-group">
                <label>积分下限</label>
                <input type="number" name="credit_lower" value="<?php echo $group['credit_lower']; ?>">
            </div>
            <div class="form-group flex gap-md">
                <button type="submit" class="btn btn-primary">保存修改</button>
                <a href="index.php?c=admin&a=usergroups" class="btn btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>