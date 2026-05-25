<div class="grid grid-cols-main-3 gap-4 md:grid-cols-1">
    <!-- Main Content -->
    <div class="min-w-0 flex flex-col gap-4">
        <!-- Thread List Card -->
        <div class="bg-panel border border-border rounded shadow-sm">
            <!-- Card Header -->
            <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
                <div class="flex items-center gap-2 text-sm text-muted mb-2">
                    <a href="index.php" class="hover:text-primary">首页</a>
                    <span>/</span>
                    <?php if ($template_parentForum): ?>
                        <a href="index.php?c=forum&a=index&fid=<?php echo $template_parentForum['fid']; ?>" class="hover:text-primary"><?php echo htmlspecialchars($template_parentForum['name']); ?></a>
                        <span>/</span>
                    <?php endif; ?>
                    <span>版块</span>
                </div>
                <div class="flex items-start justify-between gap-4 w-full">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($template_forum['name']); ?></h1>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-soft text-sub"><?php echo (int)$template_forum['thread_num']; ?> 主题</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-soft text-sub"><?php echo (int)$template_forum['reply_num']; ?> 回复</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success">今日 <?php echo (int)$template_forum['today_num']; ?></span>
                        </div>
                    </div>
                    <?php if (isset($template_user)): ?>
                        <a href="index.php?c=thread&a=create&fid=<?php echo $template_forum['fid']; ?>" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark flex-shrink-0">发布主题</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex items-center justify-between gap-4 p-3 border-b border-border flex-wrap">
                <div class="flex-1 min-w-0 flex flex-col gap-2">
                    <div class="flex items-center gap-1 hide-mobile">
                        <?php foreach ($template_orderOptions as $option): ?>
                            <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>&order=<?php echo $option['value']; ?><?php echo !empty($template_keyword) ? '&keyword=' . urlencode($template_keyword) : ''; ?>"
                                class="px-3 py-1.5 rounded text-sm transition-all <?php echo $template_order == $option['value'] ? 'bg-primary-light text-primary' : 'text-sub hover:bg-hover hover:text-text'; ?>">
                                <?php echo $option['label']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <form action="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>" method="get" class="flex items-center gap-2 hide-mobile">
                    <input type="hidden" name="c" value="forum">
                    <input type="hidden" name="a" value="index">
                    <input type="hidden" name="fid" value="<?php echo $template_forum['fid']; ?>">
                    <input type="hidden" name="order" value="<?php echo $template_order; ?>">
                    <input type="text" name="keyword" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" placeholder="搜索标题..." value="<?php echo htmlspecialchars($template_keyword ?? ''); ?>">
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed">搜索</button>
                </form>
                <form action="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>" method="get" class="flex gap-2 mobile-only">
                    <input type="hidden" name="c" value="forum">
                    <input type="hidden" name="a" value="index">
                    <input type="hidden" name="fid" value="<?php echo $template_forum['fid']; ?>">
                    <select name="order" onchange="this.form.submit()" class="flex-1 w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary">
                        <?php foreach ($template_orderOptions as $option): ?>
                            <option value="<?php echo $option['value']; ?>" <?php echo $template_order == $option['value'] ? 'selected' : ''; ?>>
                                <?php echo $option['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="keyword" class="flex-1 w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" placeholder="搜索..." value="<?php echo htmlspecialchars($template_keyword ?? ''); ?>">
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed">搜索</button>
                </form>
            </div>

            <!-- Thread List -->
            <div class="flex flex-col">
                <?php foreach ($template_threads as $thread): ?>
                    <?php $author = $template_users[$thread['uid']] ?? null; ?>
                    <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="flex items-center gap-3 p-3 border-b border-border last:border-b-0 hover:bg-hover transition-colors">
                        <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold text-sm flex-shrink-0 overflow-hidden">
                            <?php if ($author && !empty($author['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($author['avatar']); ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo \Lib\Helper::getAvatarInitial($author['username'] ?? '?'); ?>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold truncate"><?php echo htmlspecialchars($thread['subject']); ?></span>
                                <?php if (!empty($template_keyword)): ?>
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-primary-light text-primary flex-shrink-0">命中搜索</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-muted mt-1">
                                <span><?php echo htmlspecialchars($author['username'] ?? '匿名'); ?></span>
                                <span>·</span>
                                <span><?php echo date('Y-m-d H:i', $thread['dateline']); ?></span>
                                <?php if (!empty($thread['reply_time']) && (int)$thread['reply_time'] !== (int)$thread['dateline']): ?>
                                    <span>·</span>
                                    <span>最后活跃 <?php echo date('Y-m-d H:i', (int)$thread['reply_time']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-sm flex-shrink-0">
                            <div class="text-center">
                                <div class="font-semibold"><?php echo $thread['view_num']; ?></div>
                                <div class="text-xs text-muted">浏览</div>
                            </div>
                            <div class="text-center">
                                <div class="font-semibold"><?php echo $thread['reply_num']; ?></div>
                                <div class="text-xs text-muted">回复</div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($template_threads)): ?>
                    <div class="p-8 text-center text-muted">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/>
                        </svg>
                        <p>暂无主题，点击上方按钮发布新主题</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($template_pages > 1): ?>
                <?php
                $baseUrl = 'index.php?c=forum&a=index&fid=' . $template_forum['fid'] . '&order=' . $template_order;
                if (!empty($template_keyword)) {
                    $baseUrl .= '&keyword=' . urlencode($template_keyword);
                }
                echo \Lib\Helper::renderPagination($template_page, $template_pages, $baseUrl);
                ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <?php if (!empty($template_moderators) || !empty($template_hotThreads)): ?>
    <aside class="md:hidden flex flex-col gap-4">
        <?php if (!empty($template_moderators)): ?>
        <div class="bg-panel border border-border rounded shadow-sm">
            <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
                <h3 class="flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    版主团队
                </h3>
            </div>
            <div class="p-0">
                <div class="flex flex-col">
                    <?php foreach ($template_moderators as $moderator): ?>
                        <?php $user = $template_moderatorUsers[$moderator['uid']] ?? null; ?>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $moderator['uid']; ?>" class="flex items-center gap-3 p-2 rounded hover:bg-hover transition-colors">
                            <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold text-sm flex-shrink-0 overflow-hidden">
                                <?php if ($user && !empty($user['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?php echo \Lib\Helper::getAvatarInitial($user['username'] ?? '?'); ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm"><?php echo htmlspecialchars($user['username'] ?? '未知用户'); ?></div>
                                <div class="text-xs text-muted">
                                    <?php echo $moderator['end_date'] ? '任期至 ' . date('Y-m-d', $moderator['end_date']) : '永久版主'; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($template_hotThreads)): ?>
        <div class="bg-panel border border-border rounded shadow-sm">
            <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
                <h3 class="flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                    本版热帖
                </h3>
            </div>
            <div class="p-0">
                <div class="flex flex-col">
                    <?php foreach ($template_hotThreads as $index => $thread): ?>
                        <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="flex items-center gap-3 p-2 rounded hover:bg-hover transition-colors">
                                <span class="w-5 h-5 flex items-center justify-center rounded-sm text-xs font-semibold flex-shrink-0 <?php echo $index < 3 ? 'bg-primary-light text-primary' : 'bg-soft text-muted'; ?>"><?php echo $index + 1; ?></span>
                                <div class="flex-1 min-w-0 flex flex-col gap-0.5">
                                    <span class="text-sm text-text truncate"><?php echo htmlspecialchars($thread['subject']); ?></span>
                                    <span class="text-xs text-muted"><?php echo $thread['reply_num']; ?> 回复</span>
                                </div>
                            </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </aside>
    <?php endif; ?>
</div>
