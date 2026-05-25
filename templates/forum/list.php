<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <h2><?php echo $template_from === 'create' ? '选择发布版块' : '论坛导航'; ?></h2>
    </div>
    <div class="p-0">
        <?php if (!empty($template_forums)): ?>
            <div class="flex flex-col">
                <?php foreach ($template_forums as $forum): ?>
                    <a href="<?php echo $template_from === 'create' ? 'index.php?c=thread&a=create&fid=' . $forum['fid'] : 'index.php?c=forum&a=index&fid=' . $forum['fid']; ?>"
                       class="flex items-center gap-3 p-3 border-b border-border last:border-b-0 hover:bg-hover transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <?php if ((int)$forum['depth'] > 0): ?>
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-soft text-sub">Lv <?php echo (int)$forum['depth'] + 1; ?></span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-success-light text-success">主版块</span>
                                <?php endif; ?>
                                <span class="font-semibold"><?php echo htmlspecialchars($forum['name']); ?></span>
                            </div>
                            <div class="text-sm text-muted">
                                <?php if (!empty($forum['description'])): ?>
                                    <?php echo htmlspecialchars($forum['description']); ?>
                                <?php else: ?>
                                    暂无版块简介
                                <?php endif; ?>
                                <span> · </span>
                                <?php echo (int)($forum['thread_num'] ?? 0); ?> 主题
                                <span> · </span>
                                <?php echo (int)($forum['reply_num'] ?? 0); ?> 回复
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-muted flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-8 text-center text-muted">
                <p>暂无论坛版块</p>
            </div>
        <?php endif; ?>
    </div>
</div>
