<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>用户组管理</h2>
        <a href="index.php?c=admin&a=usergroupAdd" class="btn btn-primary">添加用户组</a>
    </div>
    <div class="card-body padded">
        <div class="table-container">
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
                    <tr>
                        <td><?php echo $group['gid']; ?></td>
                        <td><?php echo htmlspecialchars($group['title']); ?></td>
                        <td>
                            <?php 
                            $types = ['system' => '系统', 'special' => '特殊', 'member' => '会员'];
                            echo $types[$group['group_type']] ?? '未知';
                            ?>
                        </td>
                        <td><?php echo $group['credit_lower']; ?></td>
                        <td>
                            <button class="btn btn-secondary edit-group-btn" data-gid="<?php echo $group['gid']; ?>">编辑</button>
                            <button class="btn btn-secondary delete-group-btn" data-gid="<?php echo $group['gid']; ?>" data-title="<?php echo htmlspecialchars($group['title']); ?>">删除</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-secondary">暂无用户组</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div id="group-edit-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>编辑用户组</h3>
            <button class="modal-close" onclick="closeModal('group-edit-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="group-edit-form" method="post" class="modal-form">
                <input type="hidden" name="gid" id="edit-group-gid">
                <div class="form-group">
                    <label>用户组名称</label>
                    <input type="text" name="title" id="edit-group-title" required>
                </div>
                <div class="form-group">
                    <label>用户组类型</label>
                    <select name="group_type" id="edit-group-type">
                        <option value="system">系统组</option>
                        <option value="special">特殊组</option>
                        <option value="member">会员组</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>积分下限</label>
                    <input type="number" name="credit_lower" id="edit-group-credit">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('group-edit-modal')">取消</button>
            <button class="btn btn-primary" onclick="document.getElementById('group-edit-form').submit()">保存修改</button>
        </div>
    </div>
</div>

<div id="group-delete-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>确认删除</h3>
            <button class="modal-close" onclick="closeModal('group-delete-modal')">×</button>
        </div>
        <div class="modal-body">
            <p class="modal-confirm-text" id="group-delete-confirm-text">确定要删除该用户组吗？此操作无法撤销。</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('group-delete-modal')">取消</button>
            <a href="#" id="group-delete-confirm-btn" class="btn btn-primary">确认删除</a>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.edit-group-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var gid = this.getAttribute('data-gid');
            fetch('index.php?c=admin&a=usergroupEdit&gid=' + gid + '&ajax=1')
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        document.getElementById('edit-group-gid').value = data.group.gid;
                        document.getElementById('edit-group-title').value = data.group.title;
                        document.getElementById('edit-group-type').value = data.group.group_type;
                        document.getElementById('edit-group-credit').value = data.group.credit_lower;
                        document.getElementById('group-edit-form').action = 'index.php?c=admin&a=usergroupEdit&gid=' + data.group.gid;
                        openModal('group-edit-modal');
                    }
                });
        });
    });

    document.querySelectorAll('.delete-group-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var gid = this.getAttribute('data-gid');
            var title = this.getAttribute('data-title');
            document.getElementById('group-delete-confirm-text').textContent = '确定要删除用户组"' + title + '" 吗？此操作无法撤销。';
            document.getElementById('group-delete-confirm-btn').href = 'index.php?c=admin&a=usergroupDelete&gid=' + gid;
            openModal('group-delete-modal');
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
});
</script>
