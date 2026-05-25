<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border bg-soft">
        <h2 class="font-semibold">主题管理</h2>
    </div>
    <div class="p-4">
        <form method="get" class="mb-4">
            <input type="hidden" name="c" value="admin">
            <input type="hidden" name="a" value="threads">
            <div class="flex gap-2 flex-wrap">
                <select name="fid" class="w-auto h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                    <option value="0">全部版块</option>
                    <?php foreach ($template_forums as $forum): ?>
                        <option value="<?php echo $forum['fid']; ?>" <?php echo $template_fid == $forum['fid'] ? 'selected' : ''; ?>>
                            <?php echo str_repeat('├─ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="search_type" class="w-auto h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                    <option value="title" <?php echo (!isset($template_searchType) || $template_searchType == 'title') ? 'selected' : ''; ?>>标题</option>
                    <option value="username" <?php echo isset($template_searchType) && $template_searchType == 'username' ? 'selected' : ''; ?>>用户名</option>
                </select>
                <input type="text" name="keyword" class="flex-1 min-w-50 w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" placeholder="输入关键词" value="<?php echo htmlspecialchars($template_keyword); ?>">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">搜索</button>
            </div>
        </form>

        <form method="post" action="index.php?c=admin&a=threadBatch">
            <input type="hidden" name="action" id="batch-action">

            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-2">
                    <select id="action-select" class="w-auto h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                        <option value="">批量操作</option>
                        <option value="delete">批量删除</option>
                        <option value="move">批量移动</option>
                    </select>
                    <select name="fid" id="move-fid" class="w-auto h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary hidden">
                        <?php foreach ($template_forums as $forum): ?>
                            <option value="<?php echo $forum['fid']; ?>"><?php echo str_repeat('├─ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" id="batch-submit" disabled>执行</button>
                </div>
            </div>

            <?php
            $forumNames = [];
            foreach ($template_forums as $forum) {
                $forumNames[$forum['fid']] = $forum['name'];
            }
            ?>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-soft">
                            <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border"><input type="checkbox" id="select-all"></th>
                            <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">ID</th>
                            <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border">标题</th>
                            <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border whitespace-nowrap">版块</th>
                            <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border whitespace-nowrap">作者</th>
                            <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border whitespace-nowrap">回复/浏览</th>
                            <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border whitespace-nowrap">时间</th>
                            <th class="text-left px-4 py-2 font-semibold text-muted border-b border-border whitespace-nowrap">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($template_threads)): ?>
                    <?php foreach ($template_threads as $thread): ?>
                        <tr class="hover:bg-hover transition-colors">
                            <td class="px-4 py-3 border-b border-border"><input type="checkbox" name="tids[]" value="<?php echo $thread['tid']; ?>"></td>
                            <td class="px-4 py-3 border-b border-border"><?php echo $thread['tid']; ?></td>
                            <td class="px-4 py-3 border-b border-border"><a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" target="_blank" class="text-primary hover:underline"><?php echo htmlspecialchars($thread['subject']); ?></a></td>
                            <td class="px-4 py-3 border-b border-border whitespace-nowrap"><?php echo htmlspecialchars($forumNames[$thread['fid']] ?? $thread['fid']); ?></td>
                            <td class="px-4 py-3 border-b border-border whitespace-nowrap"><?php echo htmlspecialchars($template_users[$thread['uid']]['username'] ?? '已删除用户'); ?></td>
                            <td class="px-4 py-3 border-b border-border whitespace-nowrap"><?php echo $thread['reply_num']; ?>/<?php echo $thread['view_num']; ?></td>
                            <td class="px-4 py-3 border-b border-border whitespace-nowrap"><?php echo date('Y-m-d H:i', $thread['dateline']); ?></td>
                            <td class="px-4 py-3 border-b border-border whitespace-nowrap">
                                <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover h-control-sm px-3 text-sm" data-action="delete-thread" data-tid="<?php echo $thread['tid']; ?>" data-title="<?php echo htmlspecialchars($thread['subject']); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-muted">暂无主题</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($template_pages > 1): ?>
                <div class="mt-4">
                    <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=admin&a=threads&fid=' . $template_fid . '&keyword=' . urlencode($template_keyword)); ?>
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

<div id="thread-delete-modal" data-modal-overlay class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-panel rounded-lg max-w-md w-full mx-4 shadow-lg">
        <div class="flex items-center justify-between p-4 border-b border-border">
            <h3 class="font-semibold">确认删除</h3>
            <button onclick="closeModal('thread-delete-modal')" class="text-muted hover:text-text text-xl">&times;</button>
        </div>
        <div class="p-4">
            <p class="text-muted" id="thread-delete-confirm-text">确定要删除该主题吗？此操作无法撤销。</p>
        </div>
        <div class="flex justify-end gap-3 p-4 border-t border-border">
            <button class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover" onclick="closeModal('thread-delete-modal')">取消</button>
            <a href="#" id="thread-delete-confirm-btn" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">确认删除</a>
        </div>
    </div>
</div>
