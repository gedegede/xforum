<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>主题管理</h2>
    </div>
    <div class="card-body padded">
        <form method="get">
            <input type="hidden" name="c" value="admin">
            <input type="hidden" name="a" value="threads">
            <div class="flex gap-sm flex-wrap">
                <select name="fid">
                    <option value="0">全部版块</option>
                    <?php foreach ($template_forums as $forum): ?>
                        <option value="<?php echo $forum['fid']; ?>" <?php echo $template_fid == $forum['fid'] ? 'selected' : ''; ?>>
                            <?php echo str_repeat('├─ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="search_type">
                    <option value="title" <?php echo (!isset($template_searchType) || $template_searchType == 'title') ? 'selected' : ''; ?>>标题</option>
                    <option value="username" <?php echo isset($template_searchType) && $template_searchType == 'username' ? 'selected' : ''; ?>>用户名</option>
                </select>
                <input type="text" name="keyword" placeholder="输入关键词" value="<?php echo htmlspecialchars($template_keyword); ?>">
                <button type="submit" class="btn btn-primary">搜索</button>
            </div>
        </form>

        <form method="post" action="index.php?c=admin&a=threadBatch">
            <input type="hidden" name="action" id="batch-action">
            
            <div class="flex justify-between items-center mt-lg mb-lg">
                <div>
                    <select id="action-select">
                        <option value="">批量操作</option>
                        <option value="delete">批量删除</option>
                        <option value="move">批量移动</option>
                    </select>
                    <select name="fid" id="move-fid" class="hide">
                        <?php foreach ($template_forums as $forum): ?>
                            <option value="<?php echo $forum['fid']; ?>"><?php echo str_repeat('├─ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-secondary ml-sm" id="batch-submit" disabled>执行</button>
                </div>
            </div>

            <?php 
$forumNames = [];
foreach ($template_forums as $forum) {
    $forumNames[$forum['fid']] = $forum['name'];
}
?>

<div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>ID</th>
                        <th>标题</th>
                        <th class="nowrap">版块</th>
                        <th class="nowrap">作者</th>
                        <th class="nowrap">回复/浏览</th>
                        <th class="nowrap">时间</th>
                        <th class="nowrap">操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($template_threads)): ?>
                <?php foreach ($template_threads as $thread): ?>
                    <tr>
                            <td><input type="checkbox" name="tids[]" value="<?php echo $thread['tid']; ?>"></td>
                            <td><?php echo $thread['tid']; ?></td>
                            <td><a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" target="_blank"><?php echo htmlspecialchars($thread['subject']); ?></a></td>
                            <td class="nowrap"><?php echo htmlspecialchars($forumNames[$thread['fid']] ?? $thread['fid']); ?></td>
                            <td class="nowrap"><?php echo htmlspecialchars($template_users[$thread['uid']]['username'] ?? '已删除用户'); ?></td>
                            <td class="nowrap"><?php echo $thread['reply_num']; ?>/<?php echo $thread['view_num']; ?></td>
                            <td class="nowrap"><?php echo date('Y-m-d H:i', $thread['dateline']); ?></td>
                            <td class="nowrap">
                                <button class="btn btn-secondary delete-thread-btn" data-tid="<?php echo $thread['tid']; ?>" data-title="<?php echo htmlspecialchars($thread['subject']); ?>">删除</button>
                            </td>
                    </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-secondary">暂无主题</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>

            <?php if ($template_pages > 1): ?>
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=admin&a=threads&fid=' . $template_fid . '&keyword=' . urlencode($template_keyword)); ?>
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
            moveFid.classList.remove('hide');
        } else {
            moveFid.classList.add('hide');
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
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-thread-btn').forEach(function(btn) {
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

<div id="thread-delete-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>确认删除</h3>
            <button class="modal-close" onclick="closeModal('thread-delete-modal')">×</button>
        </div>
        <div class="modal-body">
            <p class="modal-confirm-text" id="thread-delete-confirm-text">确定要删除该主题吗？此操作无法撤销。</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('thread-delete-modal')">取消</button>
            <a href="#" id="thread-delete-confirm-btn" class="btn btn-primary">确认删除</a>
        </div>
    </div>
</div>
