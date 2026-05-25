<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border bg-soft">
        <h2 class="font-semibold">用户组管理</h2>
        <a href="index.php?c=admin&a=usergroupAdd" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">添加用户组</a>
    </div>
    <div class="p-4">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-soft">
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">ID</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">用户组名称</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">类型</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">积分下限</th>
                        <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_groups)): ?>
                    <?php foreach ($template_groups as $group): ?>
                        <tr class="hover:bg-hover transition-colors">
                            <td class="px-4 py-3 border-b border-border"><?php echo $group['gid']; ?></td>
                            <td class="px-4 py-3 border-b border-border"><?php echo htmlspecialchars($group['title']); ?></td>
                            <td class="px-4 py-3 border-b border-border">
                                <?php
                                $types = ['system' => '系统', 'special' => '特殊', 'member' => '会员'];
                                echo $types[$group['group_type']] ?? '未知';
                                ?>
                            </td>
                            <td class="px-4 py-3 border-b border-border"><?php echo $group['credit_lower']; ?></td>
                            <td class="px-4 py-3 border-b border-border">
                                <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover h-control-sm px-3 text-sm" data-action="edit-group" data-gid="<?php echo $group['gid']; ?>">编辑</button>
                                <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover h-control-sm px-3 text-sm" data-action="delete-group" data-gid="<?php echo $group['gid']; ?>" data-title="<?php echo htmlspecialchars($group['title']); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-muted">暂无用户组</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="group-edit-modal" data-modal-overlay class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-panel rounded-lg max-w-md w-full mx-4 shadow-lg">
        <div class="flex items-center justify-between p-4 border-b border-border">
            <h3 class="font-semibold">编辑用户组</h3>
            <button onclick="closeModal('group-edit-modal')" class="text-muted hover:text-text text-xl">&times;</button>
        </div>
        <div class="p-4">
            <form id="group-edit-form" method="post">
                <input type="hidden" name="gid" id="edit-group-gid">
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">用户组名称</label>
                    <input type="text" name="title" id="edit-group-title" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
                </div>
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">用户组类型</label>
                    <select name="group_type" id="edit-group-type" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                        <option value="system">系统组</option>
                        <option value="special">特殊组</option>
                        <option value="member">会员组</option>
                    </select>
                </div>
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">积分下限</label>
                    <input type="number" name="credit_lower" id="edit-group-credit" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                </div>
            </form>
        </div>
        <div class="flex justify-end gap-3 p-4 border-t border-border">
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" onclick="closeModal('group-edit-modal')">取消</button>
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark" onclick="document.getElementById('group-edit-form').submit()">保存修改</button>
        </div>
    </div>
</div>

<div id="group-delete-modal" data-modal-overlay class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-panel rounded-lg max-w-md w-full mx-4 shadow-lg">
        <div class="flex items-center justify-between p-4 border-b border-border">
            <h3 class="font-semibold">确认删除</h3>
            <button onclick="closeModal('group-delete-modal')" class="text-muted hover:text-text text-xl">&times;</button>
        </div>
        <div class="p-4">
            <p class="text-muted" id="group-delete-confirm-text">确定要删除该用户组吗？此操作无法撤销。</p>
        </div>
        <div class="flex justify-end gap-3 p-4 border-t border-border">
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" onclick="closeModal('group-delete-modal')">取消</button>
            <a href="#" id="group-delete-confirm-btn" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">确认删除</a>
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
    document.querySelectorAll('[data-action="edit-group"]').forEach(function(btn) {
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
