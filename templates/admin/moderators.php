<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <div class="flex items-center gap-3">
            <a href="index.php?c=admin&a=forums" class="btn btn-soft">← 返回版块管理</a>
            <h2 class="font-semibold">版主管理 - <?php echo htmlspecialchars($template_forum['name']); ?></h2>
        </div>
        <button class="btn btn-primary" data-action="add-moderator">添加版主</button>
    </div>
    <div class="card-body">
        <div class="table-wrap">
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
                            <td><?php echo $moderator['end_date'] ? \Lib\Helper::formatTime((int)$moderator['end_date']) : '永久'; ?></td>
                            <td>
                                <button class="btn btn-soft btn-sm" data-action="edit-moderator"
                                    data-uid="<?php echo $moderator['uid']; ?>"
                                    data-fid="<?php echo $moderator['fid']; ?>"
                                    data-sort-order="<?php echo $moderator['sort_order']; ?>"
                                    data-end-date="<?php echo $moderator['end_date'] ? date('Y-m-d', $moderator['end_date']) : ''; ?>">编辑</button>
                                <button class="btn btn-danger btn-sm" data-action="delete-moderator"
                                    data-uid="<?php echo $moderator['uid']; ?>"
                                    data-fid="<?php echo $moderator['fid']; ?>"
                                    data-username="<?php echo htmlspecialchars($user['username'] ?? '未知用户'); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="table-empty">暂无版主</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="add-moderator-modal" data-modal-overlay class="modal hidden">
    <div class="modal-panel">
        <div class="modal-header">
            <h3 class="font-semibold">添加版主</h3>
            <button class="modal-close" onclick="closeModal('add-moderator-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="add-moderator-form" method="post" action="index.php?c=admin&a=moderatorAdd">
                <input type="hidden" name="fid" value="<?php echo $template_forum['fid']; ?>">
                <div class="form-field">
                    <label class="form-label">用户名</label>
                    <input type="text" name="username" id="add-username" class="form-control" required placeholder="请输入用户名">
                </div>
                <div class="form-field">
                    <label class="form-label">排序</label>
                    <input type="number" name="sort_order" id="add-sort-order" class="form-control" value="0" min="0">
                </div>
                <div class="form-field">
                    <label class="form-label">任职结束日期（留空为永久）</label>
                    <input type="date" name="end_date" id="add-end-date" class="form-control">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-soft" onclick="closeModal('add-moderator-modal')">取消</button>
            <button class="btn btn-primary" onclick="document.getElementById('add-moderator-form').submit()">添加</button>
        </div>
    </div>
</div>

<div id="edit-moderator-modal" data-modal-overlay class="modal hidden">
    <div class="modal-panel">
        <div class="modal-header">
            <h3 class="font-semibold">编辑版主</h3>
            <button class="modal-close" onclick="closeModal('edit-moderator-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="edit-moderator-form" method="post" action="index.php?c=admin&a=moderatorEdit">
                <input type="hidden" name="fid" id="edit-fid">
                <input type="hidden" name="uid" id="edit-uid">
                <div class="form-field">
                    <label class="form-label">排序</label>
                    <input type="number" name="sort_order" id="edit-sort-order" class="form-control" min="0" required>
                </div>
                <div class="form-field">
                    <label class="form-label">任职结束日期（留空为永久）</label>
                    <input type="date" name="end_date" id="edit-end-date" class="form-control">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-soft" onclick="closeModal('edit-moderator-modal')">取消</button>
            <button class="btn btn-primary" onclick="document.getElementById('edit-moderator-form').submit()">保存修改</button>
        </div>
    </div>
</div>

<div id="delete-moderator-modal" data-modal-overlay class="modal hidden">
    <div class="modal-panel">
        <div class="modal-header">
            <h3 class="font-semibold">确认删除</h3>
            <button class="modal-close" onclick="closeModal('delete-moderator-modal')">×</button>
        </div>
        <div class="modal-body">
            <p class="text-text" id="delete-confirm-text">确定要删除该版主吗？</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-soft" onclick="closeModal('delete-moderator-modal')">取消</button>
            <a href="#" id="delete-confirm-btn" class="btn btn-danger">确认删除</a>
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
