<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>版块管理</h2>
        <a href="index.php?c=admin&a=forumAdd" class="btn btn-primary">添加版块</a>
    </div>
    <div class="card-body padded">
        <div class="table-container">
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
                        <td><span class="badge <?php echo $forum['status'] ? 'badge-green' : 'badge-red'; ?>"><?php echo $forum['status'] ? '启用' : '禁用'; ?></span></td>
                        <td>
                            <button class="btn btn-secondary edit-forum-btn" data-fid="<?php echo $forum['fid']; ?>">编辑</button>
                            <a href="index.php?c=admin&a=moderators&fid=<?php echo $forum['fid']; ?>" class="btn btn-secondary">版主</a>
                            <button class="btn btn-secondary delete-forum-btn" data-fid="<?php echo $forum['fid']; ?>" data-name="<?php echo htmlspecialchars($forum['name']); ?>">删除</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary">暂无版块</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div id="forum-edit-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>编辑版块</h3>
            <button class="modal-close" onclick="closeModal('forum-edit-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="forum-edit-form" method="post" class="modal-form">
                <input type="hidden" name="fid" id="edit-forum-fid">
                <div class="form-group">
                    <label>版块名称</label>
                    <input type="text" name="name" id="edit-forum-name" required>
                </div>
                <div class="form-group">
                    <label>上级版块</label>
                    <select name="up_fid" id="edit-forum-upfid">
                        <option value="0">无（顶级版块）</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>状态</label>
                    <select name="status" id="edit-forum-status">
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('forum-edit-modal')">取消</button>
            <button class="btn btn-primary" onclick="document.getElementById('forum-edit-form').submit()">保存修改</button>
        </div>
    </div>
</div>

<div id="forum-delete-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>确认删除</h3>
            <button class="modal-close" onclick="closeModal('forum-delete-modal')">×</button>
        </div>
        <div class="modal-body">
            <p class="modal-confirm-text" id="forum-delete-confirm-text">确定要删除该版块吗？此操作无法撤销。</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('forum-delete-modal')">取消</button>
            <a href="#" id="forum-delete-confirm-btn" class="btn btn-primary">确认删除</a>
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
    document.querySelectorAll('.edit-forum-btn').forEach(function(btn) {
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

    document.querySelectorAll('.delete-forum-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var fid = this.getAttribute('data-fid');
            var name = this.getAttribute('data-name');
            document.getElementById('forum-delete-confirm-text').textContent = '确定要删除版块 "' + name + '" 吗？此操作无法撤销，该版块下的所有主题和回复也将被删除。';
            document.getElementById('forum-delete-confirm-btn').href = 'index.php?c=admin&a=forumDelete&fid=' + fid;
            openModal('forum-delete-modal');
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
