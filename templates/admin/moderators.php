<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border bg-soft">
        <div class="flex items-center gap-3">
            <a href="index.php?c=admin&a=forums" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover">← 返回版块管理</a>
            <h2 class="font-semibold">版主管理 - <?php echo htmlspecialchars($template_forum['name']); ?></h2>
        </div>
        <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark" data-action="add-moderator">添加版主</button>
    </div>
    <div class="p-4">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">用户名</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">排序</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">任职结束日期</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_moderators)): ?>
                    <?php foreach ($template_moderators as $moderator): ?>
                        <?php $user = $template_users[$moderator['uid']] ?? null; ?>
                        <tr class="border-b border-border hover:bg-hover transition-colors">
                            <td class="px-4 py-3"><?php echo htmlspecialchars($user['username'] ?? '未知用户'); ?></td>
                            <td class="px-4 py-3"><?php echo $moderator['sort_order']; ?></td>
                            <td class="px-4 py-3"><?php echo $moderator['end_date'] ? date('Y-m-d', $moderator['end_date']) : '永久'; ?></td>
                            <td class="px-4 py-3">
                                <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed h-control-sm px-3 text-sm bg-soft border-border text-text hover:bg-hover" data-action="edit-moderator"
                                    data-uid="<?php echo $moderator['uid']; ?>"
                                    data-fid="<?php echo $moderator['fid']; ?>"
                                    data-sort-order="<?php echo $moderator['sort_order']; ?>"
                                    data-end-date="<?php echo $moderator['end_date'] ? date('Y-m-d', $moderator['end_date']) : ''; ?>">编辑</button>
                                <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed h-control-sm px-3 text-sm bg-danger border-danger text-white hover:bg-danger-dark" data-action="delete-moderator"
                                    data-uid="<?php echo $moderator['uid']; ?>"
                                    data-fid="<?php echo $moderator['fid']; ?>"
                                    data-username="<?php echo htmlspecialchars($user['username'] ?? '未知用户'); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-muted">暂无版主</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="add-moderator-modal" data-modal-overlay class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-4 py-3 border-b border-border">
            <h3 class="font-semibold">添加版主</h3>
            <button class="text-muted hover:text-text text-xl font-bold leading-none" onclick="closeModal('add-moderator-modal')">×</button>
        </div>
        <div class="p-4">
            <form id="add-moderator-form" method="post" action="index.php?c=admin&a=moderatorAdd">
                <input type="hidden" name="fid" value="<?php echo $template_forum['fid']; ?>">
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">用户名</label>
                    <input type="text" name="username" id="add-username" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required placeholder="请输入用户名">
                </div>
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">排序</label>
                    <input type="number" name="sort_order" id="add-sort-order" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" value="0" min="0">
                </div>
                <div class="mb-2 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">任职结束日期（留空为永久）</label>
                    <input type="date" name="end_date" id="add-end-date" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                </div>
            </form>
        </div>
        <div class="flex justify-end gap-3 px-4 py-3 border-t border-border bg-soft">
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" onclick="closeModal('add-moderator-modal')">取消</button>
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark" onclick="document.getElementById('add-moderator-form').submit()">添加</button>
        </div>
    </div>
</div>

<div id="edit-moderator-modal" data-modal-overlay class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-4 py-3 border-b border-border">
            <h3 class="font-semibold">编辑版主</h3>
            <button class="text-muted hover:text-text text-xl font-bold leading-none" onclick="closeModal('edit-moderator-modal')">×</button>
        </div>
        <div class="p-4">
            <form id="edit-moderator-form" method="post" action="index.php?c=admin&a=moderatorEdit">
                <input type="hidden" name="fid" id="edit-fid">
                <input type="hidden" name="uid" id="edit-uid">
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">排序</label>
                    <input type="number" name="sort_order" id="edit-sort-order" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" min="0" required>
                </div>
                <div class="mb-2 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">任职结束日期（留空为永久）</label>
                    <input type="date" name="end_date" id="edit-end-date" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                </div>
            </form>
        </div>
        <div class="flex justify-end gap-3 px-4 py-3 border-t border-border bg-soft">
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" onclick="closeModal('edit-moderator-modal')">取消</button>
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark" onclick="document.getElementById('edit-moderator-form').submit()">保存修改</button>
        </div>
    </div>
</div>

<div id="delete-moderator-modal" data-modal-overlay class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-4 py-3 border-b border-border">
            <h3 class="font-semibold">确认删除</h3>
            <button class="text-muted hover:text-text text-xl font-bold leading-none" onclick="closeModal('delete-moderator-modal')">×</button>
        </div>
        <div class="p-4">
            <p class="text-text" id="delete-confirm-text">确定要删除该版主吗？</p>
        </div>
        <div class="flex justify-end gap-3 px-4 py-3 border-t border-border bg-soft">
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" onclick="closeModal('delete-moderator-modal')">取消</button>
            <a href="#" id="delete-confirm-btn" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-danger border-danger text-white hover:bg-danger-dark">确认删除</a>
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
    document.querySelector('[data-action="add-moderator"]').addEventListener('click', function() {
        openModal('add-moderator-modal');
    });

    document.querySelectorAll('[data-action="edit-moderator"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit-fid').value = this.getAttribute('data-fid');
            document.getElementById('edit-uid').value = this.getAttribute('data-uid');
            document.getElementById('edit-sort-order').value = this.getAttribute('data-sort-order');
            document.getElementById('edit-end-date').value = this.getAttribute('data-end-date');
            openModal('edit-moderator-modal');
        });
    });

    document.querySelectorAll('[data-action="delete-moderator"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var uid = this.getAttribute('data-uid');
            var fid = this.getAttribute('data-fid');
            var username = this.getAttribute('data-username');
            document.getElementById('delete-confirm-text').textContent = '确定要删除版主"' + username + '" 吗？';
            document.getElementById('delete-confirm-btn').href = 'index.php?c=admin&a=moderatorDelete&fid=' + fid + '&uid=' + uid;
            openModal('delete-moderator-modal');
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
