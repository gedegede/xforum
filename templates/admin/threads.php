<?php include '_menu.php'; ?>

<style>
.thread-filter{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px}
.thread-filter-field{display:flex;flex-direction:column;gap:4px;min-width:150px}
.thread-filter-field-wide{min-width:220px;flex:1}
.thread-filter-label{color:var(--muted);font-size:12px;font-weight:600;white-space:nowrap}
.thread-filter-control{height:var(--control-height);width:100%}
.thread-filter-actions{display:flex;gap:8px;height:var(--control-height)}
</style>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">主题管理</h2>
    </div>
    <div class="card-body">
        <form method="get" action="index.php" class="thread-filter mb-4">
            <input type="hidden" name="c" value="admin">
            <input type="hidden" name="a" value="threads">
            <label class="thread-filter-field">
                <span class="thread-filter-label">版块</span>
                <select name="fid" class="form-input thread-filter-control">
                    <option value="0">全部版块</option>
                    <?php foreach ($template_forums as $forum): ?>
                        <option value="<?php echo $forum['fid']; ?>" <?php echo $template_fid == $forum['fid'] ? 'selected' : ''; ?>>
                            <?php echo str_repeat('→ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="thread-filter-field">
                <span class="thread-filter-label">发帖者</span>
                <input type="text" name="author" value="<?php echo htmlspecialchars($template_author); ?>" placeholder="UID/用户名" class="form-input thread-filter-control">
            </label>
            <label class="thread-filter-field thread-filter-field-wide">
                <span class="thread-filter-label">标题关键词</span>
                <input type="text" name="keyword" value="<?php echo htmlspecialchars($template_keyword); ?>" placeholder="关键词" class="form-input thread-filter-control">
            </label>
            <div class="thread-filter-actions">
                <button type="submit" class="btn btn-primary">搜索</button>
                <a href="index.php?c=admin&a=threads" class="btn btn-soft">重置</a>
            </div>
        </form>

        <form method="post" action="index.php?c=admin&a=threadBatch">
            <input type="hidden" name="action" id="batch-action">

            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-2">
                    <select id="action-select" class="form-control w-auto">
                        <option value="">批量操作</option>
                        <option value="delete">批量删除</option>
                        <option value="move">批量移动</option>
                    </select>
                    <select name="fid" id="move-fid" class="form-control w-auto hidden">
                        <?php foreach ($template_forums as $forum): ?>
                            <option value="<?php echo $forum['fid']; ?>"><?php echo str_repeat('→ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-soft" id="batch-submit" disabled>执行</button>
                </div>
            </div>

            <?php
            $forumNames = [];
            foreach ($template_forums as $forum) {
                $forumNames[$forum['fid']] = $forum['name'];
            }
            ?>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="table-nowrap"><input type="checkbox" id="select-all"></th>
                            <th class="table-nowrap">ID</th>
                            <th>标题</th>
                            <th class="table-nowrap">版块</th>
                            <th class="table-nowrap">作者</th>
                            <th class="table-nowrap">回复/浏览</th>
                            <th class="table-nowrap">时间</th>
                            <th class="table-nowrap">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($template_threads)): ?>
                    <?php foreach ($template_threads as $thread): ?>
                        <tr>
                            <td class="table-nowrap"><input type="checkbox" name="tids[]" value="<?php echo $thread['tid']; ?>"></td>
                            <td class="table-nowrap"><?php echo $thread['tid']; ?></td>
                            <td><a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" target="_blank" class="text-primary hover:underline"><?php echo htmlspecialchars($thread['subject']); ?></a></td>
                            <td class="table-nowrap"><?php echo htmlspecialchars($forumNames[$thread['fid']] ?? $thread['fid']); ?></td>
                            <td class="table-nowrap"><?php echo htmlspecialchars($template_users[$thread['uid']]['username'] ?? '已删除用户'); ?></td>
                            <td class="table-nowrap"><?php echo $thread['reply_num']; ?>/<?php echo $thread['view_num']; ?></td>
                            <td class="table-nowrap"><?php echo \Lib\Helper::formatTime((int)$thread['dateline']); ?></td>
                            <td class="table-nowrap">
                                <button class="btn btn-soft btn-sm" data-action="delete-thread" data-tid="<?php echo $thread['tid']; ?>" data-title="<?php echo htmlspecialchars($thread['subject']); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="table-empty">暂无主题</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($template_pages > 1): ?>
                <div class="mt-4">
                    <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=admin&a=threads&fid=' . (int)$template_fid . '&author=' . urlencode($template_author) . '&keyword=' . urlencode($template_keyword)); ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('input[name="tids[]"]');
    const actionSelect = document.getElementById('action-select');
    const moveFid = document.getElementById('move-fid');
    const batchSubmit = document.getElementById('batch-submit');
    const batchAction = document.getElementById('batch-action');

    selectAll.addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateSubmit();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('click', updateSubmit);
    });

    actionSelect.addEventListener('change', function() {
        if (this.value === 'move') {
            moveFid.classList.remove('hidden');
        } else {
            moveFid.classList.add('hidden');
        }
        updateSubmit();
    });

    function updateSubmit() {
        const hasChecked = Array.from(checkboxes).some(cb => cb.checked);
        const hasAction = actionSelect.value !== '';
        batchSubmit.disabled = !(hasChecked && hasAction);
        batchAction.value = actionSelect.value;
    }
});

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-action="delete-thread"]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var tid = this.getAttribute('data-tid');
            var title = this.getAttribute('data-title');
            document.getElementById('thread-delete-confirm-text').textContent = '确定要删除主题"' + title + '" 吗？此操作无法撤销，该主题下的所有回复也将被删除。';
            document.getElementById('thread-delete-confirm-btn').href = 'index.php?c=admin&a=threadDelete&tid=' + tid;
            openModal('thread-delete-modal');
        });
    });

    document.getElementById('thread-delete-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal('thread-delete-modal');
        }
    });
});
</script>

<div id="thread-delete-modal" data-modal-overlay class="modal hidden">
    <div class="modal-panel">
        <div class="modal-header">
            <h3 class="font-semibold">确认删除</h3>
            <button onclick="closeModal('thread-delete-modal')" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="text-muted" id="thread-delete-confirm-text">确定要删除该主题吗？此操作无法撤销。</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-soft" onclick="closeModal('thread-delete-modal')">取消</button>
            <a href="#" id="thread-delete-confirm-btn" class="btn btn-primary" data-post-link="1">确认删除</a>
        </div>
    </div>
</div>
