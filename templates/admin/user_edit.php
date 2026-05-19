<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>编辑用户</h2>
    </div>
    <div class="card-body padded">
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($member['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>邮箱</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" placeholder="不修改密码请留空">
            </div>
            <div class="form-group">
                <label>用户组</label>
                <select name="gid">
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['gid']; ?>" <?php echo $member['gid'] == $group['gid'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($group['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>状态</label>
                <select name="status">
                    <option value="1" <?php echo $member['status'] == 1 ? 'selected' : ''; ?>>正常</option>
                    <option value="0" <?php echo $member['status'] == 0 ? 'selected' : ''; ?>>禁用</option>
                </select>
            </div>
            <div class="flex justify-end gap-md mt-lg">
                <button type="submit" class="btn btn-primary">保存修改</button>
                <a href="index.php?c=admin&a=users" class="btn btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>
