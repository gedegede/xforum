<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">用户管理</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($template_success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($template_success); ?></div>
        <?php endif; ?>
        <form method="get" class="mb-4">
            <input type="hidden" name="c" value="admin">
            <input type="hidden" name="a" value="users">
            <div class="flex gap-2 flex-wrap">
                <input type="text" name="keyword" class="form-control" placeholder="搜索用户名或邮箱..." value="<?php echo htmlspecialchars($template_keyword); ?>">
                <select name="gid" class="form-control w-auto">
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

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户名</th>
                        <th>邮箱</th>
                        <th>用户组</th>
                        <th>积分</th>
                        <th>状态</th>
                        <th>注册时间</th>
                        <th class="whitespace-nowrap">主题数</th>
                        <th class="whitespace-nowrap">回帖数</th>
                        <th class="whitespace-nowrap">操作</th>
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
                            <td class="whitespace-nowrap"><?php echo (int)($user['credit'] ?? 0); ?></td>
                            <td>
                                <span class="badge <?php echo (int)$user['status'] === -1 ? 'badge-danger' : 'badge-success'; ?>">
                                    <?php echo (int)$user['status'] === -1 ? '禁止' : '正常'; ?>
                                </span>
                            </td>
                            <td><?php echo \Lib\Helper::formatTime((int)$user['reg_date']); ?></td>
                            <td class="whitespace-nowrap"><?php echo $user['thread_num'] ?? 0; ?></td>
                            <td class="whitespace-nowrap"><?php echo $user['reply_num'] ?? 0; ?></td>
                            <td class="whitespace-nowrap">
                                <button class="btn btn-soft btn-sm" data-action="edit-user" data-uid="<?php echo $user['uid']; ?>">编辑</button>
                                <button class="btn btn-soft btn-sm" data-action="delete-user" data-uid="<?php echo $user['uid']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="table-empty">暂无用户</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($template_pages > 1): ?>
            <div class="mt-4">
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=admin&a=users&keyword=' . urlencode($template_keyword) . '&gid=' . $template_gid); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="edit-modal" data-modal-overlay class="modal hidden">
    <div class="modal-panel" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="font-semibold">编辑用户</h3>
            <button onclick="closeModal('edit-modal')" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-form" method="post">
                <input type="hidden" name="uid" id="edit-uid">
                <div class="form-field">
                    <label class="form-label">用户名</label>
                    <input type="text" name="username" id="edit-username" class="form-control" required>
                </div>
                <div class="form-field">
                    <label class="form-label">邮箱</label>
                    <input type="email" name="email" id="edit-email" class="form-control" required>
                </div>
                <div class="form-field">
                    <label class="form-label">头像地址</label>
                    <input type="text" name="avatar" id="edit-avatar" class="form-control">
                </div>
                <div class="form-field">
                    <label class="form-label">密码</label>
                    <input type="password" name="password" class="form-control" placeholder="不修改密码请留空">
                </div>
                <div class="form-field">
                    <label class="form-label">用户组</label>
                    <select name="gid" id="edit-gid" class="form-control">
                        <?php foreach ($template_groups as $group): ?>
                            <option value="<?php echo $group['gid']; ?>"><?php echo htmlspecialchars($group['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label">积分</label>
                    <input type="number" name="credit" id="edit-credit" class="form-control" step="1">
                </div>
                <div class="form-field">
                    <label class="form-label">状态</label>
                    <select name="status" id="edit-status" class="form-control">
                        <option value="0">正常</option>
                        <option value="-1">禁止</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-soft" onclick="closeModal('edit-modal')">取消</button>
            <button type="submit" form="edit-form" class="btn btn-primary">保存修改</button>
        </div>
    </div>
</div>

<div id="delete-modal" data-modal-overlay class="modal hidden">
    <div class="modal-panel" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="font-semibold">确认删除</h3>
            <button onclick="closeModal('delete-modal')" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="text-muted" id="delete-confirm-text">确定要删除该用户吗？此操作无法撤销。</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-soft" onclick="closeModal('delete-modal')">取消</button>
            <a href="#" id="delete-confirm-btn" class="btn btn-primary" data-post-link="1">确认删除</a>
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
    var editForm = document.getElementById('edit-form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetch(editForm.action, {
                method: 'POST',
                body: new FormData(editForm),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    closeModal('edit-modal');
                    if (typeof window.showTip === 'function') {
                        window.showTip(data.message || '用户已更新', 'success');
                    }
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                    return;
                }
                if (typeof window.showTip === 'function') {
                    window.showTip(data.message || '保存失败', 'danger');
                }
            })
            .catch(function() {
                if (typeof window.showTip === 'function') {
                    window.showTip('保存失败', 'danger');
                }
            });
        });
    }

    document.querySelectorAll('[data-action="edit-user"]').forEach(function(btn) {
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
                        document.getElementById('edit-avatar').value = data.user.avatar || '';
                        document.getElementById('edit-gid').value = data.user.gid;
                        document.getElementById('edit-credit').value = data.user.credit;
                        document.getElementById('edit-status').value = data.user.status;
                        document.getElementById('edit-form').action = 'index.php?c=admin&a=userEdit&uid=' + data.user.uid;
                        openModal('edit-modal');
                    }
                });
        });
    });

    document.querySelectorAll('[data-action="delete-user"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var uid = this.getAttribute('data-uid');
            var username = this.getAttribute('data-username');
            document.getElementById('delete-confirm-text').textContent = '确定要删除用户"' + username + '" 吗？此操作无法撤销。';
            document.getElementById('delete-confirm-btn').href = 'index.php?c=admin&a=userDelete&uid=' + uid;
            openModal('delete-modal');
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
