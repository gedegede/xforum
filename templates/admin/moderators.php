<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <a href="index.php?c=admin&a=forums" class="btn btn-secondary">← 返回版块管理</a>
        <h2>版主管理 - <?php echo htmlspecialchars($template_forum['name']); ?></h2>
        <button class="btn btn-primary add-moderator-btn">添加版主</button>
    </div>
    <div class="card-body padded">
        <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>用户名</th>
                    <th>排序</th>
                    <th>任职结束日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($template_moderators)): ?>
                <?php foreach ($template_moderators as $moderator): ?>
                    <?php $user = $template_users[$moderator['uid']] ?? null; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username'] ?? '未知用户'); ?></td>
                        <td><?php echo $moderator['sort_order']; ?></td>
                        <td><?php echo $moderator['end_date'] ? date('Y-m-d', $moderator['end_date']) : '永久'; ?></td>
                        <td>
                            <button class="btn btn-secondary edit-moderator-btn" 
                                data-uid="<?php echo $moderator['uid']; ?>" 
                                data-fid="<?php echo $moderator['fid']; ?>"
                                data-sort-order="<?php echo $moderator['sort_order']; ?>"
                                data-end-date="<?php echo $moderator['end_date'] ? date('Y-m-d', $moderator['end_date']) : ''; ?>">编辑</button>
                            <button class="btn btn-danger delete-moderator-btn" 
                                data-uid="<?php echo $moderator['uid']; ?>" 
                                data-fid="<?php echo $moderator['fid']; ?>"
                                data-username="<?php echo htmlspecialchars($user['username'] ?? '未知用户'); ?>">删除</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-secondary">暂无版主</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div id="add-moderator-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>添加版主</h3>
            <button class="modal-close" onclick="closeModal('add-moderator-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="add-moderator-form" method="post" action="index.php?c=admin&a=moderatorAdd" class="modal-form">
                <input type="hidden" name="fid" value="<?php echo $template_forum['fid']; ?>">
                <div class="form-group">
                    <label>用户名</label>
                    <input type="text" name="username" id="add-username" required placeholder="请输入用户名">
                </div>
                <div class="form-group">
                    <label>排序</label>
                    <input type="number" name="sort_order" id="add-sort-order" value="0" min="0">
                </div>
                <div class="form-group">
                    <label>任职结束日期（留空为永久）</label>
                    <input type="date" name="end_date" id="add-end-date">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('add-moderator-modal')">取消</button>
            <button class="btn btn-primary" onclick="document.getElementById('add-moderator-form').submit()">添加</button>
        </div>
    </div>
</div>

<div id="edit-moderator-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>编辑版主</h3>
            <button class="modal-close" onclick="closeModal('edit-moderator-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="edit-moderator-form" method="post" action="index.php?c=admin&a=moderatorEdit" class="modal-form">
                <input type="hidden" name="fid" id="edit-fid">
                <input type="hidden" name="uid" id="edit-uid">
                <div class="form-group">
                    <label>排序</label>
                    <input type="number" name="sort_order" id="edit-sort-order" min="0" required>
                </div>
                <div class="form-group">
                    <label>任职结束日期（留空为永久）</label>
                    <input type="date" name="end_date" id="edit-end-date">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('edit-moderator-modal')">取消</button>
            <button class="btn btn-primary" onclick="document.getElementById('edit-moderator-form').submit()">保存修改</button>
        </div>
    </div>
</div>

<div id="delete-moderator-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>确认删除</h3>
            <button class="modal-close" onclick="closeModal('delete-moderator-modal')">×</button>
        </div>
        <div class="modal-body">
            <p class="modal-confirm-text" id="delete-confirm-text">确定要删除该版主吗？</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('delete-moderator-modal')">取消</button>
            <a href="#" id="delete-confirm-btn" class="btn btn-danger">确认删除</a>
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
    document.querySelector('.add-moderator-btn').addEventListener('click', function() {
        openModal('add-moderator-modal');
    });

    document.querySelectorAll('.edit-moderator-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit-fid').value = this.getAttribute('data-fid');
            document.getElementById('edit-uid').value = this.getAttribute('data-uid');
            document.getElementById('edit-sort-order').value = this.getAttribute('data-sort-order');
            document.getElementById('edit-end-date').value = this.getAttribute('data-end-date');
            openModal('edit-moderator-modal');
        });
    });

    document.querySelectorAll('.delete-moderator-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var uid = this.getAttribute('data-uid');
            var fid = this.getAttribute('data-fid');
            var username = this.getAttribute('data-username');
            document.getElementById('delete-confirm-text').textContent = '确定要删除版主"' + username + '" 吗？';
            document.getElementById('delete-confirm-btn').href = 'index.php?c=admin&a=moderatorDelete&fid=' + fid + '&uid=' + uid;
            openModal('delete-moderator-modal');
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