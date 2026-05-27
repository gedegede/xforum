<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">用户组管理</h2>
        <a href="index.php?c=admin&a=usergroupAdd" class="btn btn-primary">添加用户组</a>
    </div>
    <div class="card-body">
        <?php if (!empty($template_success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($template_success); ?></div>
        <?php endif; ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户组名称</th>
                        <th>类型</th>
                        <th>积分下限</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_groups)): ?>
                    <?php foreach ($template_groups as $group): ?>
                        <?php
                        $groupType = $group['group_type'] ?? '';
                        $nameClasses = [
                            'system' => 'text-danger',
                            'special' => 'text-success',
                            'member' => 'text-primary',
                        ];
                        ?>
                        <tr>
                            <td><?php echo $group['gid']; ?></td>
                            <td>
                                <span class="font-semibold <?php echo $nameClasses[$groupType] ?? 'text-muted'; ?>">
                                    <?php echo htmlspecialchars($group['title']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $types = ['system' => '系统', 'special' => '特殊', 'member' => '会员'];
                                echo $types[$groupType] ?? '未知';
                                ?>
                            </td>
                            <td><?php echo $groupType === 'member' ? (int)$group['credit_lower'] : '-'; ?></td>
                            <td>
                                <a class="btn btn-soft btn-sm" href="index.php?c=admin&a=usergroupEdit&gid=<?php echo $group['gid']; ?>">编辑</a>
                                <button class="btn btn-soft btn-sm" data-action="delete-group" data-gid="<?php echo $group['gid']; ?>" data-title="<?php echo htmlspecialchars($group['title']); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="table-empty">暂无用户组</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="group-delete-modal" data-modal-overlay class="modal hidden">
    <div class="modal-panel">
        <div class="modal-header">
            <h3 class="font-semibold">确认删除</h3>
            <button onclick="closeModal('group-delete-modal')" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="text-muted" id="group-delete-confirm-text">确定要删除该用户组吗？此操作无法撤销。</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-soft" onclick="closeModal('group-delete-modal')">取消</button>
            <a href="#" id="group-delete-confirm-btn" class="btn btn-primary">确认删除</a>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-action="delete-group"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var gid = this.getAttribute('data-gid');
            var title = this.getAttribute('data-title');
            document.getElementById('group-delete-confirm-text').textContent = '确定要删除用户组"' + title + '" 吗？此操作无法撤销。';
            document.getElementById('group-delete-confirm-btn').href = 'index.php?c=admin&a=usergroupDelete&gid=' + gid;
            openModal('group-delete-modal');
        });
    });

    document.querySelectorAll('[data-modal-overlay]').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
});
</script>
