<?php include '_menu.php'; ?>

<style>
.archive-link{margin-left:6px;border:0;background:transparent;color:var(--primary);font-size:12px;font-weight:600;cursor:pointer}
.archive-link:hover{color:var(--primary-dark);text-decoration:underline}
.archive-modal-panel{max-width:min(760px,calc(100vw - 32px));max-height:85vh;display:flex;flex-direction:column}
.archive-modal-body{flex:1;min-height:0;max-height:65vh;overflow-y:auto;background:var(--soft)}
.archive-content{margin:0;padding:var(--space-4);border:1px solid var(--border);border-radius:var(--radius);background:var(--panel);color:var(--text);font:13px/1.7 ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;white-space:pre-wrap;word-break:break-word}
.log-filter{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px}
.log-filter-field{display:flex;flex-direction:column;gap:4px;min-width:150px}
.log-filter-field-wide{min-width:220px;flex:1}
.log-filter-label{color:var(--muted);font-size:12px;font-weight:600;white-space:nowrap}
.log-filter-control{height:var(--control-height);width:100%}
.log-filter-actions{display:flex;gap:8px;height:var(--control-height)}
</style>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">管理日志</h2>
    </div>
    <div class="card-body">
        <form method="get" action="index.php" class="log-filter mb-4">
            <input type="hidden" name="c" value="admin">
            <input type="hidden" name="a" value="logs">
            <label class="log-filter-field">
                <span class="log-filter-label">主题ID</span>
                <input type="number" name="tid" value="<?php echo (int)$template_tid; ?>" placeholder="TID" class="form-input log-filter-control">
            </label>
            <label class="log-filter-field">
                <span class="log-filter-label">操作类型</span>
                <select name="action_type" class="form-input log-filter-control">
                    <option value="">全部操作</option>
                    <?php foreach ($template_actionLabels as $action => $label): ?>
                        <option value="<?php echo htmlspecialchars($action); ?>" <?php echo $template_actionType === $action ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="log-filter-field">
                <span class="log-filter-label">操作者</span>
                <input type="text" name="operator" value="<?php echo htmlspecialchars($template_operator); ?>" placeholder="UID/用户名" class="form-input log-filter-control">
            </label>
            <label class="log-filter-field">
                <span class="log-filter-label">被操作者UID</span>
                <input type="number" name="authorid" value="<?php echo (int)$template_authorid; ?>" placeholder="authorid" class="form-input log-filter-control">
            </label>
            <label class="log-filter-field log-filter-field-wide">
                <span class="log-filter-label">操作内容</span>
                <input type="text" name="message" value="<?php echo htmlspecialchars($template_message); ?>" placeholder="关键词" class="form-input log-filter-control">
            </label>
            <div class="log-filter-actions">
                <button type="submit" class="btn btn-primary">搜索</button>
                <a href="index.php?c=admin&a=logs" class="btn btn-soft">重置</a>
            </div>
        </form>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="table-nowrap">ID</th>
                        <th class="table-nowrap">操作人</th>
                        <th>操作内容</th>
                        <th class="table-nowrap">时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($template_logs)): ?>
                    <?php foreach ($template_logs as $log): ?>
                        <tr>
                            <td class="table-nowrap"><?php echo $log['did']; ?></td>
                            <td class="table-nowrap"><?php echo htmlspecialchars($template_users[$log['uid']]['username'] ?? '未知'); ?></td>
                            <td>
                                <?php echo htmlspecialchars($log['message']); ?>
                                <?php if (($log['archive_data'] ?? '') !== ''): ?>
                                    <button type="button" class="archive-link" data-action="view-archive" data-did="<?php echo (int)$log['did']; ?>" data-can-restore="<?php echo in_array(($log['action'] ?? ''), ['post_delete', 'thread_delete', 'user_delete', 'thread_edit', 'post_edit'], true) ? '1' : '0'; ?>" data-archive-id="archive-<?php echo (int)$log['did']; ?>">[存档]</button>
                                    <template id="archive-<?php echo (int)$log['did']; ?>"><?php echo htmlspecialchars($log['archive_data'], ENT_QUOTES, 'UTF-8'); ?></template>
                                <?php endif; ?>
                            </td>
                            <td class="table-nowrap"><?php echo \Lib\Helper::formatTime((int)$log['dateline']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="table-empty">暂无管理日志</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($template_pages > 1): ?>
            <div class="mt-4">
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=admin&a=logs&tid=' . (int)$template_tid . '&authorid=' . (int)$template_authorid . '&action_type=' . urlencode($template_actionType) . '&operator=' . urlencode($template_operator) . '&message=' . urlencode($template_message)); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="archive-modal" data-modal-overlay class="modal hidden">
    <div class="modal-panel archive-modal-panel">
        <div class="modal-header">
            <h3 class="font-semibold">存档内容</h3>
            <button type="button" class="modal-close" data-action="close-archive">&times;</button>
        </div>
        <div class="modal-body archive-modal-body">
            <pre id="archive-content" class="archive-content"></pre>
        </div>
        <div class="modal-footer">
            <button type="button" id="archive-restore" class="btn btn-soft">还原</button>
            <button type="button" class="btn btn-primary" data-action="close-archive">关闭</button>
        </div>
    </div>
</div>

<script>
var archiveModal = document.getElementById('archive-modal');
var archiveContent = document.getElementById('archive-content');
var archiveRestore = document.getElementById('archive-restore');
var archiveDid = 0;
function closeArchiveModal() {
    archiveModal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
document.addEventListener('click', function(e) {
    var viewBtn = e.target.closest('[data-action="view-archive"]');
    if (viewBtn) {
        var source = document.getElementById(viewBtn.dataset.archiveId || '');
        archiveDid = Number(viewBtn.dataset.did || 0);
        archiveContent.textContent = source ? source.content.textContent : '';
        archiveRestore.classList.toggle('hidden', viewBtn.dataset.canRestore !== '1');
        archiveModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        return;
    }
    if (e.target.closest('[data-action="close-archive"]')) closeArchiveModal();
});
archiveModal.addEventListener('click', function(e) {
    if (e.target === archiveModal) closeArchiveModal();
});
archiveRestore.addEventListener('click', function() {
    if (!archiveDid) return;
    archiveRestore.disabled = true;
    fetch('index.php?c=admin&a=restorePost&did=' + archiveDid, {method: 'POST'})
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                closeArchiveModal();
                window.showTip ? window.showTip(data.message || '已还原', 'success') : location.reload();
            } else {
                window.showTip ? window.showTip(data.message || '还原失败', 'danger') : alert(data.message || '还原失败');
            }
        })
        .catch(function() {
            window.showTip ? window.showTip('还原失败', 'danger') : alert('还原失败');
        })
        .finally(function() {
            archiveRestore.disabled = false;
        });
});
</script>
