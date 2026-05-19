<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>添加用户组</h2>
    </div>
    <div class="card-body padded">
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>用户组名称</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>用户组类型</label>
                <select name="group_type">
                    <option value="system">系统组</option>
                    <option value="special">特殊组</option>
                    <option value="member" selected>会员组</option>
                </select>
            </div>
            <div class="form-group">
                <label>积分下限</label>
                <input type="number" name="credit_lower" value="0">
            </div>
            <div class="flex justify-end gap-md mt-lg">
                <button type="submit" class="btn btn-primary">添加用户组</button>
                <a href="index.php?c=admin&a=usergroups" class="btn btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>
