<?php

function renderForumTree($forums, $depth = 0) {
    foreach ($forums as $index => $forum):
        $hasChildren = !empty($forum['children']);
        $depthClass = 'forum-depth-' . min($depth, 5);
?>
<div class="forum-node <?php echo $depthClass; ?> py-md border-b hover-bg transition-bg">
    <div class="forum-node-main">
        <div class="flex items-center gap-sm">
            <?php if ($hasChildren): ?>
            <button class="btn btn-xs" onclick="toggleForum(this)">
                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/>
                </svg>
            </button>
            <?php endif; ?>
            <a href="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>" class="text-primary font-medium text-md">
                <?php echo htmlspecialchars($forum['name']); ?>
            </a>
        </div>
        <?php if (!empty($forum['description'])): ?>
        <div class="text-secondary text-sm mt-xs <?php echo $hasChildren ? 'forum-desc-indent' : ''; ?>">
            <?php echo htmlspecialchars($forum['description']); ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($hasChildren): ?>
    <div class="children hide">
        <?php renderForumTree($forum['children'], $depth + 1); ?>
    </div>
    <?php endif; ?>
</div>
<?php
    endforeach;
}
?>

<div class="card">
    <div class="card-header">
        <h2>论坛导航</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($forums)): ?>
            <?php renderForumTree($forums); ?>
        <?php else: ?>
        <div class="empty-state">暂无论坛版块</div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleForum(btn) {
    var item = btn.closest('.forum-node');
    var children = item.querySelector('.children');
    if (children) {
        children.classList.toggle('hide');
        btn.classList.toggle('rotate-90');
    }
}
</script>
