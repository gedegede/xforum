<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">内容审核</h2>
    </div>
    <div class="card-body">
        <?php
        $auditPendingThreads = (int)($template_pendingStats['pending_threads'] ?? 0);
        $auditPendingPosts = (int)($template_pendingStats['pending_posts'] ?? 0);
        $auditPendingReports = (int)($template_pendingStats['pending_reports'] ?? 0);
        $auditPendingAll = $auditPendingThreads + $auditPendingPosts + $auditPendingReports;
        ?>
        <div class="pill-nav mb-4">
            <div class="pill-nav-list">
                <a href="index.php?c=admin&a=audits&filter=all" class="pill-nav-item <?php echo $template_filter === 'all' ? 'active' : ''; ?>">全部<?php echo $auditPendingAll > 0 ? ' ' . $auditPendingAll : ''; ?></a>
                <a href="index.php?c=admin&a=audits&filter=thread" class="pill-nav-item <?php echo $template_filter === 'thread' ? 'active' : ''; ?>">待审主题<?php echo $auditPendingThreads > 0 ? ' ' . $auditPendingThreads : ''; ?></a>
                <a href="index.php?c=admin&a=audits&filter=post" class="pill-nav-item <?php echo $template_filter === 'post' ? 'active' : ''; ?>">待审回帖<?php echo $auditPendingPosts > 0 ? ' ' . $auditPendingPosts : ''; ?></a>
                <a href="index.php?c=admin&a=audits&filter=report" class="pill-nav-item <?php echo $template_filter === 'report' ? 'active' : ''; ?>">待处理举报<?php echo $auditPendingReports > 0 ? ' ' . $auditPendingReports : ''; ?></a>
                <a href="index.php?c=admin&a=audits&filter=done" class="pill-nav-item <?php echo $template_filter === 'done' ? 'active' : ''; ?>">已通过</a>
                <a href="index.php?c=admin&a=audits&filter=rejected" class="pill-nav-item <?php echo $template_filter === 'rejected' ? 'active' : ''; ?>">已拒绝</a>
            </div>
        </div>
        <?php if (empty($template_audits)): ?>
            <div class="empty-state"><p>暂无待审核内容</p></div>
        <?php else: ?>
            <div class="list-stack" id="audit-list">
                <?php foreach ($template_audits as $audit): ?>
                    <?php
                    $auditType = (string)$audit['type'];
                    $thread = $template_threads[(int)$audit['tid']] ?? null;
                    $jsonData = json_decode((string)$audit['json_data'], true) ?: [];
                    $reportUser = $template_reportUsers[(int)($jsonData['report_uid'] ?? 0)] ?? null;
                    $post = $template_posts[(int)$audit['pid']] ?? null;
                    $title = $thread['subject'] ?? ($jsonData['subject'] ?? ('已发布内容 #' . (int)$audit['did']));
                    if ($auditType === 'thread') {
                        $title = $jsonData['subject'] ?? $title;
                    }
                    $authorUid = (int)($jsonData['uid'] ?? $audit['uid'] ?? ($post['uid'] ?? ($thread['uid'] ?? 0)));
                    $author = $template_users[$authorUid] ?? null;
                    $auditUser = $template_auditUsers[(int)($jsonData['audit_uid'] ?? 0)] ?? null;
                    $auditStatus = (int)($audit['audit_status'] ?? $audit['status'] ?? 0);
                    $auditTypeLabel = ['report' => '举报', 'thread' => '主题', 'post' => '回帖'][$auditType] ?? $auditType;
                    $threadTid = (int)($thread['tid'] ?? $audit['tid'] ?? 0);
                    $threadUrl = $threadTid > 0 ? 'index.php?c=thread&a=index&tid=' . $threadTid . ((int)$audit['pid'] !== 0 ? '&pid=' . (int)$audit['pid'] : '') : '';
                    $postPreview = $auditType === 'post' ? (string)($jsonData['message'] ?? ($post['message'] ?? '')) : '';
                    ?>
                    <div class="audit-item list-link" data-did="<?php echo (int)$audit['did']; ?>">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="badge badge-xs badge-primary"><?php echo htmlspecialchars($auditTypeLabel); ?></span>
                                <?php if ($threadUrl !== ''): ?>
                                <a href="<?php echo htmlspecialchars($threadUrl); ?>" target="_blank" class="font-semibold truncate text-primary"><?php echo htmlspecialchars($title); ?></a>
                                <?php else: ?>
                                <span class="font-semibold truncate"><?php echo htmlspecialchars($title); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="thread-item-meta mb-1">
                                <span>
                                    <svg class="thread-meta-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    <?php echo htmlspecialchars($author['username'] ?? '未知用户'); ?>
                                </span>
                                <span>
                                    <svg class="thread-meta-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M5 3h10l4 4v14H5V3zm9 1.5V8h3.5L14 4.5zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/></svg>
                                    <?php echo \Lib\Helper::formatTime((int)$audit['dateline']); ?>
                                </span>
                            </div>
                            <?php if ($auditType === 'report'): ?>
                                <div class="text-xs text-muted mb-1">
                                    举报人：<?php echo htmlspecialchars($reportUser['username'] ?? '未知用户'); ?> · 举报理由：<?php echo htmlspecialchars((string)($jsonData['report_reason'] ?? '')); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($auditType === 'post' && $postPreview !== ''): ?>
                                <div class="text-sm text-muted mb-1"><?php echo nl2br(htmlspecialchars($postPreview)); ?></div>
                            <?php endif; ?>
                            <?php if ($auditStatus !== 0): ?>
                                <div class="text-xs text-muted mb-1">
                                    处理人：<?php echo htmlspecialchars($auditUser['username'] ?? '未知用户'); ?> · <?php echo $auditStatus === 1 ? '通过' : '拒绝'; ?> · <?php echo !empty($jsonData['audit_time']) ? \Lib\Helper::formatTime((int)$jsonData['audit_time']) : '-'; ?>
                                </div>
                            <?php endif; ?>
                            <div class="audit-detail hidden text-sm text-muted"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="btn btn-soft btn-sm" data-action="view">查看</button>
                            <?php if ($auditStatus === 0): ?>
                            <button type="button" class="btn btn-soft btn-sm" data-action="reject">拒绝</button>
                            <button type="button" class="btn btn-primary btn-sm" data-action="pass">通过</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('audit-list')?.addEventListener('click', function(e) {
    var btn = e.target.closest('button[data-action]');
    if (!btn) return;
    var item = btn.closest('.audit-item');
    var action = btn.dataset.action;
    if (action === 'view') {
        var detail = item.querySelector('.audit-detail');
        if (!detail) return;
        if (detail.dataset.loaded === '1') {
            detail.classList.toggle('hidden');
            return;
        }
        fetch('index.php?c=admin&a=auditView&did=' + item.dataset.did)
            .then(function(res){return res.json();})
            .then(function(data){
                if (!data.success) return;
                detail.textContent = data.content || '';
                detail.dataset.loaded = '1';
                detail.classList.remove('hidden');
            });
        return;
    }
    var form = new FormData();
    form.append('status', action);
    form.append('csrf_token', window.getCsrfToken ? window.getCsrfToken() : '');
    fetch('index.php?c=admin&a=auditHandle&did=' + item.dataset.did, {method:'POST', body:form})
        .then(function(res){return res.json();})
        .then(function(data){if(data.success){item.remove();}});
});
</script>
