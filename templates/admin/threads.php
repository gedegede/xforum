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
                    <?php foreach ($forums as $forum): ?>
                        <option value="<?php echo $forum['fid']; ?>" <?php echo $fid == $forum['fid'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($forum['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="keyword" placeholder="搜索主题标题..." value="<?php echo htmlspecialchars($keyword); ?>">
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
                        <?php foreach ($forums as $forum): ?>
                            <option value="<?php echo $forum['fid']; ?>"><?php echo htmlspecialchars($forum['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-secondary ml-sm" id="batch-submit" disabled>执行</button>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>ID</th>
                        <th>标题</th>
                        <th>版块</th>
                        <th>作者</th>
                        <th>回复/浏览</th>
                        <th>时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($threads)): ?>
                <?php foreach ($threads as $thread): ?>
                    <tr>
                            <td><input type="checkbox" name="tids[]" value="<?php echo $thread['tid']; ?>"></td>
                            <td><?php echo $thread['tid']; ?></td>
                            <td><a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" target="_blank"><?php echo htmlspecialchars($thread['subject']); ?></a></td>
                            <td><?php echo $thread['fid']; ?></td>
                            <td><?php echo htmlspecialchars($users[$thread['uid']]['username'] ?? '已删除用户'); ?></td>
                            <td><?php echo $thread['reply_num']; ?>/<?php echo $thread['view_num']; ?></td>
                            <td><?php echo date('Y-m-d H:i', $thread['dateline']); ?></td>
                            <td>
                                <a href="index.php?c=admin&a=threadDelete&tid=<?php echo $thread['tid']; ?>" class="btn btn-secondary" onclick="return confirm('确定删除该主题？')">删除</a>
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

            <?php if ($pages > 1): ?>
                <div class="pagination mt-lg">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="index.php?c=admin&a=threads&fid=<?php echo $fid; ?>&keyword=<?php echo urlencode($keyword); ?>&page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
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
</script>
