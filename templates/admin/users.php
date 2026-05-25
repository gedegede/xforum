<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border bg-soft">
        <h2 class="font-semibold">用户管理</h2>
    </div>
    <div class="p-4">
        <form method="get" class="mb-4">
            <input type="hidden" name="c" value="admin">
            <input type="hidden" name="a" value="users">
            <div class="flex gap-2 flex-wrap">
                <input type="text" name="keyword" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" placeholder="搜索用户名或邮箱..." value="<?php echo htmlspecialchars($template_keyword); ?>">
                <select name="gid" class="w-auto h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                    <option value="0">全部用户组</option>
                    <?php foreach ($template_groups as $group): ?>
                        <option value="<?php echo $group['gid']; ?>" <?php echo $template_gid == $group['gid'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($group['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">搜索</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-soft">
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">ID</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">用户名</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">邮箱</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">用户组</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">状态</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">注册时间</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border whitespace-nowrap">主题数</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border whitespace-nowrap">回帖数</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border whitespace-nowrap">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_users)): ?>
                    <?php foreach ($template_users as $user): ?>
                        <tr class="hover:bg-hover transition-colors">
                            <td class="px-4 py-3 border-b border-border"><?php echo $user['uid']; ?></td>
                            <td class="px-4 py-3 border-b border-border"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="px-4 py-3 border-b border-border"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-4 py-3 border-b border-border"><?php echo htmlspecialchars($template_groups[$user['gid']]['title'] ?? $user['gid']); ?></td>
                            <td class="px-4 py-3 border-b border-border">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium <?php echo $user['status'] ? 'bg-success-light text-success' : 'bg-danger-light text-danger'; ?>">
                                    <?php echo $user['status'] ? '正常' : '禁用'; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 border-b border-border"><?php echo date('Y-m-d', $user['reg_date']); ?></td>
                            <td class="px-4 py-3 border-b border-border whitespace-nowrap"><?php echo $user['thread_num'] ?? 0; ?></td>
                            <td class="px-4 py-3 border-b border-border whitespace-nowrap"><?php echo $user['reply_num'] ?? 0; ?></td>
                            <td class="px-4 py-3 border-b border-border whitespace-nowrap">
                                <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover h-control-sm px-3 text-sm" data-action="edit-user" data-uid="<?php echo $user['uid']; ?>">编辑</button>
                                <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover h-control-sm px-3 text-sm" data-action="delete-user" data-uid="<?php echo $user['uid']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-muted">暂无用户</td>
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

<div id="edit-modal" data-modal-overlay class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-panel rounded-lg max-w-md w-full mx-4 shadow-lg">
        <div class="flex items-center justify-between p-4 border-b border-border">
            <h3 class="font-semibold">编辑用户</h3>
            <button onclick="closeModal('edit-modal')" class="text-muted hover:text-text text-xl">&times;</button>
        </div>
        <div class="p-4">
            <form id="edit-form" method="post">
                <input type="hidden" name="uid" id="edit-uid">
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">用户名</label>
                    <input type="text" name="username" id="edit-username" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
                </div>
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">邮箱</label>
                    <input type="email" name="email" id="edit-email" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
                </div>
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">密码</label>
                    <input type="password" name="password" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" placeholder="不修改密码请留空">
                </div>
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">用户组</label>
                    <select name="gid" id="edit-gid" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                        <?php foreach ($template_groups as $group): ?>
                            <option value="<?php echo $group['gid']; ?>"><?php echo htmlspecialchars($group['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">状态</label>
                    <select name="status" id="edit-status" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                        <option value="1">正常</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="flex justify-end gap-3 p-4 border-t border-border">
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" onclick="closeModal('edit-modal')">取消</button>
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark" onclick="document.getElementById('edit-form').submit()">保存修改</button>
        </div>
    </div>
</div>

<div id="delete-modal" data-modal-overlay class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-panel rounded-lg max-w-md w-full mx-4 shadow-lg">
        <div class="flex items-center justify-between p-4 border-b border-border">
            <h3 class="font-semibold">确认删除</h3>
            <button onclick="closeModal('delete-modal')" class="text-muted hover:text-text text-xl">&times;</button>
        </div>
        <div class="p-4">
            <p class="text-muted" id="delete-confirm-text">确定要删除该用户吗？此操作无法撤销。</p>
        </div>
        <div class="flex justify-end gap-3 p-4 border-t border-border">
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" onclick="closeModal('delete-modal')">取消</button>
            <a href="#" id="delete-confirm-btn" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">确认删除</a>
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
                        document.getElementById('edit-gid').value = data.user.gid;
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
