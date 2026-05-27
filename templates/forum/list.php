<div class="card">
    <div class="card-header">
        <h2><?php echo $template_from === 'create' ? '选择发布版块' : '论坛导航'; ?></h2>
    </div>
    <div class="card-body-flush">
        <?php if (!empty($template_forums)): ?>
            <div class="forum-nav-list">
                <?php foreach ($template_forums as $forum): ?>
                    <?php $depth = max(0, (int)($forum['depth'] ?? 0)); ?>
                    <a href="<?php echo $template_from === 'create' ? 'index.php?c=thread&a=create&fid=' . $forum['fid'] : 'index.php?c=forum&a=index&fid=' . $forum['fid']; ?>"
                       class="forum-nav-item<?php echo $depth === 0 ? ' forum-nav-item-root' : ''; ?>"
                       style="--forum-depth: <?php echo $depth; ?>;">
                        <span class="forum-nav-branch" aria-hidden="true"></span>
                        <div class="forum-nav-main min-w-0">
                            <div class="forum-nav-title-row">
                                <?php if ($depth > 0): ?>
                                    <span class="badge badge-xs badge-soft">Lv <?php echo $depth + 1; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-xs badge-success">主版块</span>
                                <?php endif; ?>
                                <?php if ((int)($forum['today_num'] ?? 0) > 0): ?>
                                    <span class="badge badge-xs badge-primary">今日 +<?php echo (int)($forum['today_num'] ?? 0); ?></span>
                                <?php endif; ?>
                                <span class="forum-nav-name"><?php echo htmlspecialchars($forum['name']); ?></span>
                            </div>
                            <div class="forum-nav-meta">
                                <?php if (!empty($forum['description'])): ?>
                                    <?php echo htmlspecialchars($forum['description']); ?>
                                <?php else: ?>
                                    暂无版块简介
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="forum-nav-stats hide-mobile">
                            <span><?php echo (int)($forum['thread_num'] ?? 0); ?> 主题</span>
                            <span><?php echo (int)($forum['reply_num'] ?? 0); ?> 回复</span>
                        </div>
                        <svg class="thread-item-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>暂无论坛版块</p>
            </div>
        <?php endif; ?>
    </div>
</div>
