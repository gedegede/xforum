<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">版块管理</h2>
        <button type="button" class="btn btn-primary" data-action="add-forum">添加版块</button>
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
                        <th>版块名称</th>
                        <th>上级版块</th>
                        <th>主题数</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_forums)): ?>
                    <?php foreach ($template_forums as $forum): ?>
                        <tr>
                            <td><?php echo $forum['fid']; ?></td>
                            <td><?php echo str_repeat('→ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?></td>
                            <td><?php echo $forum['up_fid'] ? htmlspecialchars($forum['parent_name']) : '无'; ?></td>
                            <td><?php echo $forum['thread_num']; ?></td>
                            <td>
                                <span class="badge <?php echo $forum['status'] ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $forum['status'] ? '启用' : '禁用'; ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-soft btn-sm" href="index.php?c=admin&a=forumEdit&fid=<?php echo $forum['fid']; ?>">编辑</a>
                                <a href="index.php?c=admin&a=moderators&fid=<?php echo $forum['fid']; ?>" class="btn btn-soft btn-sm">版主</a>
                                <button class="btn btn-danger btn-sm" data-action="delete-forum" data-fid="<?php echo $forum['fid']; ?>" data-name="<?php echo htmlspecialchars($forum['name']); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="table-empty">暂无版块</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="forum-add-modal" data-modal-overlay class="modal hidden">
    <div class="modal-panel">
        <div class="modal-header">
            <h3 class="font-semibold">添加版块</h3>
            <button class="modal-close" onclick="closeModal('forum-add-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="forum-add-form" method="post" action="index.php?c=admin&a=forumAdd">
                <div class="form-field">
                    <label class="form-label">版块名称</label>
                    <input type="text" name="name" id="add-forum-name" class="form-control" required>
                </div>
                <div class="form-field">
                    <label class="form-label">上级版块</label>
                    <select name="up_fid" id="add-forum-upfid" class="form-control">
                        <option value="0">无（顶级版块）</option>
                        <?php foreach ($template_parentForums as $forum): ?>
                            <option value="<?php echo $forum['fid']; ?>">
                                <?php echo str_repeat('→ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-soft" onclick="closeModal('forum-add-modal')">取消</button>
            <button type="submit" form="forum-add-form" class="btn btn-primary">添加版块</button>
        </div>
    </div>
</div>

<div id="forum-delete-modal" data-modal-overlay class="modal hidden">
    <div class="modal-panel">
        <div class="modal-header">
            <h3 class="font-semibold">确认删除</h3>
            <button class="modal-close" onclick="closeModal('forum-delete-modal')">×</button>
        </div>
        <div class="modal-body">
            <p id="forum-delete-confirm-text" class="text-text">确定要删除该版块吗？此操作无法撤销。</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-soft" onclick="closeModal('forum-delete-modal')">取消</button>
            <a href="#" id="forum-delete-confirm-btn" class="btn btn-danger" data-post-link="1">确认删除</a>
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
    var addForumBtn = document.querySelector('[data-action="add-forum"]');
    if (addForumBtn) {
        addForumBtn.addEventListener('click', function() {
            document.getElementById('forum-add-form').reset();
            openModal('forum-add-modal');
            document.getElementById('add-forum-name').focus();
        });
    }

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
