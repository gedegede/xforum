<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">编辑用户</h2>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-field">
                <label class="form-label">用户名</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($member['username']); ?>" required>
            </div>

            <div class="form-field">
                <label class="form-label">邮箱</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email']); ?>" required>
            </div>

            <div class="form-field">
                <label class="form-label">头像地址</label>
                <input type="text" name="avatar" class="form-control" value="<?php echo htmlspecialchars($member['avatar'] ?? ''); ?>">
            </div>

            <div class="form-field">
                <label class="form-label">密码</label>
                <input type="password" name="password" class="form-control" placeholder="不修改密码请留空">
            </div>

            <div class="form-field">
                <label class="form-label">用户组</label>
                <select name="gid" class="form-control">
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['gid']; ?>" <?php echo $member['gid'] == $group['gid'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($group['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">积分</label>
                <input type="number" name="credit" class="form-control" value="<?php echo (int)$member['credit']; ?>" step="1">
            </div>

            <div class="form-field form-field-lg">
                <label class="form-label">状态</label>
                <select name="status" class="form-control">
                    <option value="0" <?php echo (int)$member['status'] !== -1 ? 'selected' : ''; ?>>正常</option>
                    <option value="-1" <?php echo (int)$member['status'] === -1 ? 'selected' : ''; ?>>禁止</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">保存修改</button>
                <a href="index.php?c=admin&a=users" class="btn btn-soft">取消</a>
            </div>
        </form>
    </div>
</div>
