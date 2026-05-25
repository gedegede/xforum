<div class="bg-panel border border-border rounded shadow-sm">
    <div class="p-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold text-2xl flex-shrink-0 overflow-hidden">
                <?php echo \Lib\Helper::getAvatarInitial($template_member['username']); ?>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($template_member['username']); ?></h2>
                <div class="text-sm text-muted mb-2">
                    注册于 <?php echo date('Y-m-d', $template_member['reg_date']); ?> ·
                    <?php echo $template_member['thread_num']; ?> 主题 ·
                    <?php echo $template_member['reply_num']; ?> 回复 ·
                    <?php echo (int)($template_member['credit'] ?? 0); ?> 金币
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-soft text-sub">
                        <?php echo (int)$template_member['thread_num']; ?> 个主题
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-soft text-sub">
                        <?php echo (int)$template_member['reply_num']; ?> 条回复
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-soft text-sub">
                        <?php echo (int)($template_member['credit'] ?? 0); ?> 金币
                    </span>
                    <?php if ($template_isSelf): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success">
                            <?php echo (int)($template_member['fav_num'] ?? 0); ?> 条收藏
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($template_isSelf): ?>
<div class="bg-panel border border-border rounded shadow-sm">
    <div class="p-0">
        <div class="flex flex-wrap border-b">
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=threads"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo $template_type == 'threads' ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                我的主题
            </a>
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=replies"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo $template_type == 'replies' ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                我的回复
            </a>
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=favorites"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo $template_type == 'favorites' ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                我的收藏
            </a>
            <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=credits"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo $template_type == 'credits' ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                金币明细
            </a>
            <a href="index.php?c=member&a=settings"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (isset($_GET['c']) && $_GET['c'] == 'member' && $_GET['a'] == 'settings') ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                个人设置
            </a>
            <a href="index.php?c=admin&a=index"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (isset($_GET['c']) && $_GET['c'] == 'admin') ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                站点设置
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <div>
            <h3 class="font-semibold">
                <?php if ($template_type == 'threads'): ?>
                    <?php echo $template_isSelf ? '我的主题' : 'Ta 的主题'; ?>
                <?php elseif ($template_type == 'replies'): ?>
                    <?php echo $template_isSelf ? '我的回复' : 'Ta 的回复'; ?>
                <?php elseif ($template_type == 'favorites'): ?>
                    我的收藏
                <?php else: ?>
                    金币明细
                <?php endif; ?>
            </h3>
            <p class="text-sm text-muted mt-1">
                <?php if ($template_type == 'threads'): ?>
                    这里集中展示该用户发起的主题内容。
                <?php elseif ($template_type == 'replies'): ?>
                    这里集中展示该用户最近参与过的讨论。
                <?php elseif ($template_type == 'favorites'): ?>
                    这里集中展示你收藏保存的主题。
                <?php else: ?>
                    这里记录你的金币获得和消耗明细。
                <?php endif; ?>
            </p>
        </div>
        <?php if ($template_type == 'credits' && $template_isSelf): ?>
            <form method="post" action="index.php?c=member&a=signin">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed h-control-sm px-3 text-sm <?php echo !empty($template_signedToday) ? 'bg-soft border-border text-text hover:bg-hover' : 'bg-primary border-primary text-white hover:bg-primary-dark'; ?>" <?php echo !empty($template_signedToday) ? 'disabled' : ''; ?>>
                    <?php echo !empty($template_signedToday) ? '今日已签到' : '每日签到'; ?>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="p-0">
        <?php if ($template_type == 'threads'): ?>
            <?php if ($template_threads): ?>
                <div class="divide-y">
                    <?php foreach ($template_threads as $thread): ?>
                        <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="flex items-center gap-3 p-3 hover:bg-hover transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-text truncate"><?php echo htmlspecialchars($thread['subject']); ?></div>
                                <div class="flex items-center gap-3 text-sm text-muted mt-1">
                                    <span><?php echo date('Y-m-d', $thread['dateline']); ?></span>
                                    <span><?php echo $thread['reply_num']; ?> 回复</span>
                                    <span><?php echo $thread['view_num']; ?> 浏览</span>
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
                    <p>暂无主题</p>
                </div>
            <?php endif; ?>

        <?php elseif ($template_type == 'replies'): ?>
            <?php if ($template_posts): ?>
                <div class="divide-y">
                    <?php foreach ($template_posts as $post): ?>
                        <?php $thread = $template_threads[$post['tid']] ?? null; ?>
                        <a href="index.php?c=thread&a=index&tid=<?php echo $post['tid']; ?>&pid=<?php echo $post['pid']; ?>" class="flex items-center gap-3 p-3 hover:bg-hover transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-text truncate"><?php echo $thread ? htmlspecialchars($thread['subject']) : '已删除'; ?></span>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-soft text-sub">回复</span>
                                </div>
                                <div class="text-sm text-muted mt-1">
                                    <span><?php echo date('Y-m-d', $post['dateline']); ?></span>
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
                    <p>暂无回复</p>
                </div>
            <?php endif; ?>

        <?php elseif ($template_type == 'favorites'): ?>
            <?php if ($template_favorites): ?>
                <div class="divide-y">
                    <?php foreach ($template_favorites as $fav): ?>
                        <?php $thread = $template_threads[$fav['tid']] ?? null; ?>
                        <a href="index.php?c=thread&a=index&tid=<?php echo $fav['tid']; ?>" class="flex items-center gap-3 p-3 hover:bg-hover transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-text truncate"><?php echo $thread ? htmlspecialchars($thread['subject']) : '已删除'; ?></span>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-success-light text-success">收藏</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-muted mt-1">
                                    <span><?php echo date('Y-m-d', $fav['dateline']); ?></span>
                                    <span><?php echo $thread ? $thread['reply_num'] : 0; ?> 回复</span>
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
                    <p>暂无收藏</p>
                </div>
            <?php endif; ?>

        <?php elseif ($template_type == 'credits'): ?>
            <?php if ($template_credits): ?>
                <div class="divide-y">
                    <?php foreach ($template_credits as $credit): ?>
                        <?php $creditValue = (int)$credit['credit']; ?>
                        <?php $creditUrl = trim((string)($credit['url'] ?? '')); ?>
                        <<?php echo $creditUrl !== '' ? 'a href="' . htmlspecialchars($creditUrl) . '"' : 'div'; ?> class="flex items-center gap-3 p-3 hover:bg-hover transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-text"><?php echo htmlspecialchars($credit['message']); ?></div>
                                <div class="text-sm text-muted mt-1">
                                    <span><?php echo date('Y-m-d H:i', (int)$credit['dateline']); ?></span>
                                </div>
                            </div>
                            <div class="font-semibold <?php echo $creditValue >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $creditValue > 0 ? '+' . $creditValue : $creditValue; ?>
                            </div>
                        </<?php echo $creditUrl !== '' ? 'a' : 'div'; ?>>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-8 text-center text-muted">
                    <p>暂无金币明细</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($template_pages > 1): ?>
            <div class="p-4 border-t border-border">
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=member&a=profile&uid=' . $template_member['uid'] . '&type=' . $template_type); ?>
            </div>
        <?php endif; ?>
    </div>
</div>