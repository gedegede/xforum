<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>添加用户组</h2>
    </div>
    <div class="card-body padded">
        <?php if ($template_error): ?>
            <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
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
            <div class="form-group">
                <label>权限设置</label>
                <div class="checkbox-group">
                    <label class="checkbox-item">
                        <input type="checkbox" name="can_manage" value="1">
                        <span>允许管理主题</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="thread_need_approve" value="1">
                        <span>发主题需要审核</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="post_need_approve" value="1">
                        <span>发回帖需要审核</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-md mt-lg">
                <button type="submit" class="btn btn-primary">添加用户组</button>
                <a href="index.php?c=admin&a=usergroups" class="btn btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>
