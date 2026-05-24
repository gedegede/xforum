<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>用户管理</h2>
    </div>
    <div class="card-body padded">
        <form method="get">
            <input type="hidden" name="c" value="admin">
            <input type="hidden" name="a" value="users">
            <div class="flex gap-sm flex-wrap">
                <input type="text" name="keyword" placeholder="搜索用户名或邮箱..." value="<?php echo htmlspecialchars($template_keyword); ?>">
                <select name="gid">
                    <option value="0">全部用户组</option>
                    <?php foreach ($template_groups as $group): ?>
                        <option value="<?php echo $group['gid']; ?>" <?php echo $template_gid == $group['gid'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($group['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">搜索</button>
            </div>
        </form>

        <div class="table-container">
        <table class="table mt-lg">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>邮箱</th>
                    <th>用户组</th>
                    <th>状态</th>
                    <th>注册时间</th>
                    <th class="nowrap">主题数</th>
                    <th class="nowrap">回帖数</th>
                    <th class="nowrap">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($template_users)): ?>
                <?php foreach ($template_users as $user): ?>
                    <tr>
                        <td><?php echo $user['uid']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($template_groups[$user['gid']]['title'] ?? $user['gid']); ?></td>
                        <td><span class="badge <?php echo $user['status'] ? 'badge-green' : 'badge-red'; ?>"><?php echo $user['status'] ? '正常' : '禁用'; ?></span></td>
                        <td><?php echo date('Y-m-d', $user['reg_date']); ?></td>
                        <td class="nowrap"><?php echo $user['thread_num'] ?? 0; ?></td>
                        <td class="nowrap"><?php echo $user['reply_num'] ?? 0; ?></td>
                        <td class="nowrap">
                            <button class="btn btn-secondary edit-user-btn" data-uid="<?php echo $user['uid']; ?>">编辑</button>
                            <button class="btn btn-secondary delete-user-btn" data-uid="<?php echo $user['uid']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>">删除</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-secondary">暂无用户</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <?php if ($template_pages > 1): ?>
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=admin&a=users&keyword=' . urlencode($template_keyword) . '&gid=' . $template_gid); ?>
        <?php endif; ?>
    </div>
</div>

<div id="edit-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>编辑用户</h3>
            <button class="modal-close" onclick="closeModal('edit-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="edit-form" method="post" class="modal-form">
                <input type="hidden" name="uid" id="edit-uid">
                <div class="form-group">
                    <label>用户名</label>
                    <input type="text" name="username" id="edit-username" required>
                </div>
                <div class="form-group">
                    <label>邮箱</label>
                    <input type="email" name="email" id="edit-email" required>
                </div>
                <div class="form-group">
                    <label>密码</label>
                    <input type="password" name="password" placeholder="不修改密码请留空">
                </div>
                <div class="form-group">
                    <label>用户组</label>
                    <select name="gid" id="edit-gid">
                        <?php foreach ($template_groups as $group): ?>
                            <option value="<?php echo $group['gid']; ?>"><?php echo htmlspecialchars($group['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>状态</label>
                    <select name="status" id="edit-status">
                        <option value="1">正常</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('edit-modal')">取消</button>
            <button class="btn btn-primary" onclick="document.getElementById('edit-form').submit()">保存修改</button>
        </div>
    </div>
</div>

<div id="delete-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>确认删除</h3>
            <button class="modal-close" onclick="closeModal('delete-modal')">×</button>
        </div>
        <div class="modal-body">
            <p class="modal-confirm-text" id="delete-confirm-text">确定要删除该用户吗？此操作无法撤销。</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('delete-modal')">取消</button>
            <a href="#" id="delete-confirm-btn" class="btn btn-primary">确认删除</a>
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
    document.querySelectorAll('.edit-user-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var uid = this.getAttribute('data-uid');
            fetch('index.php?c=admin&a=userEdit&uid=' + uid + '&ajax=1')
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        document.getElementById('edit-uid').value = data.user.uid;
                        document.getElementById('edit-username').value = data.user.username;
                        document.getElementById('edit-email').value = data.user.email;
                        document.getElementById('edit-gid').value = data.user.gid;
                        document.getElementById('edit-status').value = data.user.status;
                        document.getElementById('edit-form').action = 'index.php?c=admin&a=userEdit&uid=' + data.user.uid;
                        openModal('edit-modal');
                    }
                });
        });
    });

    document.querySelectorAll('.delete-user-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var uid = this.getAttribute('data-uid');
            var username = this.getAttribute('data-username');
            document.getElementById('delete-confirm-text').textContent = '确定要删除用户 "' + username + '" 吗？此操作无法撤销。';
            document.getElementById('delete-confirm-btn').href = 'index.php?c=admin&a=userDelete&uid=' + uid;
            openModal('delete-modal');
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
