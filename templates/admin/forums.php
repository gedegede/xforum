<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border bg-soft">
        <h2 class="font-semibold">版块管理</h2>
        <a href="index.php?c=admin&a=forumAdd" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">添加版块</a>
    </div>
    <div class="p-4">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">ID</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">版块名称</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">上级版块</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">主题数</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">状态</th>
                        <th class="text-left px-4 py-2 text-sm font-medium text-text">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_forums)): ?>
                    <?php foreach ($template_forums as $forum): ?>
                        <tr class="border-b border-border hover:bg-hover transition-colors">
                            <td class="px-4 py-3"><?php echo $forum['fid']; ?></td>
                            <td class="px-4 py-3"><?php echo str_repeat('→ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?></td>
                            <td class="px-4 py-3"><?php echo $forum['up_fid'] ? htmlspecialchars($forum['parent_name']) : '无'; ?></td>
                            <td class="px-4 py-3"><?php echo $forum['thread_num']; ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $forum['status'] ? 'bg-success-light text-success' : 'bg-danger-light text-danger'; ?>">
                                    <?php echo $forum['status'] ? '启用' : '禁用'; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed h-control-sm px-3 text-sm bg-soft border-border text-text hover:bg-hover" data-action="edit-forum" data-fid="<?php echo $forum['fid']; ?>">编辑</button>
                                <a href="index.php?c=admin&a=moderators&fid=<?php echo $forum['fid']; ?>" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed h-control-sm px-3 text-sm bg-soft border-border text-text hover:bg-hover">版主</a>
                                <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed h-control-sm px-3 text-sm bg-danger border-danger text-white hover:bg-danger-dark" data-action="delete-forum" data-fid="<?php echo $forum['fid']; ?>" data-name="<?php echo htmlspecialchars($forum['name']); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-muted">暂无版块</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="forum-edit-modal" data-modal-overlay class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-4 py-3 border-b border-border">
            <h3 class="font-semibold">编辑版块</h3>
            <button class="text-muted hover:text-text text-xl font-bold leading-none" onclick="closeModal('forum-edit-modal')">×</button>
        </div>
        <div class="p-4">
            <form id="forum-edit-form" method="post">
                <input type="hidden" name="fid" id="edit-forum-fid">
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">版块名称</label>
                    <input type="text" name="name" id="edit-forum-name" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
                </div>
                <div class="mb-4 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">上级版块</label>
                    <select name="up_fid" id="edit-forum-upfid" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                        <option value="0">无（顶级版块）</option>
                    </select>
                </div>
                <div class="mb-2 flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-text">状态</label>
                    <select name="status" id="edit-forum-status" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="flex justify-end gap-3 px-4 py-3 border-t border-border bg-soft">
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" onclick="closeModal('forum-edit-modal')">取消</button>
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark" onclick="document.getElementById('forum-edit-form').submit()">保存修改</button>
        </div>
    </div>
</div>

<div id="forum-delete-modal" data-modal-overlay class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-4 py-3 border-b border-border">
            <h3 class="font-semibold">确认删除</h3>
            <button class="text-muted hover:text-text text-xl font-bold leading-none" onclick="closeModal('forum-delete-modal')">×</button>
        </div>
        <div class="p-4">
            <p id="forum-delete-confirm-text" class="text-text">确定要删除该版块吗？此操作无法撤销。</p>
        </div>
        <div class="flex justify-end gap-3 px-4 py-3 border-t border-border bg-soft">
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" onclick="closeModal('forum-delete-modal')">取消</button>
            <a href="#" id="forum-delete-confirm-btn" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-danger border-danger text-white hover:bg-danger-dark">确认删除</a>
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
    document.querySelectorAll('[data-action="edit-forum"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var fid = this.getAttribute('data-fid');
            fetch('index.php?c=admin&a=forumEdit&fid=' + fid + '&ajax=1')
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        document.getElementById('edit-forum-fid').value = data.forum.fid;
                        document.getElementById('edit-forum-name').value = data.forum.name;
                        document.getElementById('edit-forum-upfid').value = data.forum.up_fid;
                        document.getElementById('edit-forum-status').value = data.forum.status;
                        
                        var upFidSelect = document.getElementById('edit-forum-upfid');
                        while (upFidSelect.options.length > 1) {
                            upFidSelect.remove(1);
                        }
                        
                        data.parentForums.forEach(function(pf) {
                            if (pf.fid != data.forum.fid) {
                                var option = document.createElement('option');
                                option.value = pf.fid;
                                option.textContent = (pf.depth ? '├─ '.repeat(pf.depth) : '') + pf.name;
                                if (pf.fid == data.forum.up_fid) {
                                    option.selected = true;
                                }
                                upFidSelect.appendChild(option);
                            }
                        });
                        
                        document.getElementById('forum-edit-form').action = 'index.php?c=admin&a=forumEdit&fid=' + data.forum.fid;
                        openModal('forum-edit-modal');
                    }
                });
        });
    });

    document.querySelectorAll('[data-action="delete-forum"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var fid = this.getAttribute('data-fid');
            var name = this.getAttribute('data-name');
            document.getElementById('forum-delete-confirm-text').textContent = '确定要删除版块"' + name + '" 吗？此操作无法撤销，该版块下的所有主题和回复也将被删除。';
            document.getElementById('forum-delete-confirm-btn').href = 'index.php?c=admin&a=forumDelete&fid=' + fid;
            openModal('forum-delete-modal');
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
