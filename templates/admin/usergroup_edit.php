<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">编辑用户组</h2>
    </div>
    <div class="card-body">
        <?php if ($template_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-field">
                <label class="form-label">用户组名称</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($template_group['title']); ?>" required>
            </div>

            <div class="form-field">
                <label class="form-label">用户组类型</label>
                <select name="group_type" class="form-control" id="group-type">
                    <option value="system" <?php echo $template_group['group_type'] == 'system' ? 'selected' : ''; ?>>系统组</option>
                    <option value="special" <?php echo $template_group['group_type'] == 'special' ? 'selected' : ''; ?>>特殊组</option>
                    <option value="member" <?php echo $template_group['group_type'] == 'member' ? 'selected' : ''; ?>>会员组</option>
                </select>
            </div>

            <div class="form-field" id="credit-lower-field">
                <label class="form-label">积分下限</label>
                <input type="number" name="credit_lower" class="form-control" value="<?php echo $template_group['credit_lower']; ?>">
            </div>

            <?php include __DIR__ . '/_usergroup_permissions.php'; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">保存修改</button>
                <a href="index.php?c=admin&a=usergroups" class="btn btn-soft">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var groupType = document.getElementById('group-type');
    var creditLowerField = document.getElementById('credit-lower-field');
    var creditLowerInput = creditLowerField ? creditLowerField.querySelector('[name="credit_lower"]') : null;

    function syncCreditLower() {
        var isMember = groupType && groupType.value === 'member';
        if (creditLowerField) {
            creditLowerField.classList.toggle('hidden', !isMember);
        }
        if (creditLowerInput) {
            creditLowerInput.disabled = !isMember;
            if (!isMember) {
                creditLowerInput.value = '0';
            }
        }
    }

    if (groupType) {
        groupType.addEventListener('change', syncCreditLower);
    }
    syncCreditLower();
});
</script>
