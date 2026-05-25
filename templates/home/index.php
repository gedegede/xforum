<div class="grid grid-cols-main-3 gap-4 md:grid-cols-1">
    <!-- Main Content -->
    <div class="min-w-0 flex flex-col gap-4">
        <?php if ($template_canManage && !empty($template_modStats)): ?>
        <div class="bg-panel border border-border rounded shadow-sm">
            <div class="flex items-center justify-between gap-3 p-3 border-b border-border">
                <h3>管理提示</h3>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap gap-2">
                    <a href="index.php?c=admin&a=threads&status=pending" class="flex-1 flex flex-col items-center gap-1 p-3 rounded bg-soft hover:bg-hover transition-colors">
                        <span class="text-lg font-bold"><?php echo $template_modStats['pending_threads'] ?? 0; ?></span>
                        <span class="text-sm text-muted">待审核主题</span>
                    </a>
                    <a href="index.php?c=admin&a=threads&status=pending_posts" class="flex-1 flex flex-col items-center gap-1 p-3 rounded bg-soft hover:bg-hover transition-colors">
                        <span class="text-lg font-bold"><?php echo $template_modStats['pending_posts'] ?? 0; ?></span>
                        <span class="text-sm text-muted">待审核回帖</span>
                    </a>
                    <a href="index.php?c=admin&a=threads&fid=<?php echo (int)($template_settings['report_forum_fid'] ?? 0); ?>" class="flex-1 flex flex-col items-center gap-1 p-3 rounded bg-soft hover:bg-hover transition-colors">
                        <span class="text-lg font-bold"><?php echo $template_modStats['pending_reports'] ?? 0; ?></span>
                        <span class="text-sm text-muted">待处理举报</span>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Thread List Card -->
        <div class="bg-panel border border-border rounded shadow-sm">
            <!-- Card Header -->
            <div class="flex flex-col items-start gap-3 p-3 border-b border-border">
                <div class="flex items-center justify-between gap-4 w-full">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl font-bold">XForum</h1>
                    </div>
                    <div class="flex flex-col gap-2">
                        <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
                            <a href="index.php?c=forum&a=index&from=create" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark w-full">发布主题</a>
                        <?php else: ?>
                            <a href="index.php?c=auth&a=login" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover w-full">登录</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex items-center justify-between gap-4 p-3 border-b border-border flex-wrap">
                <div class="flex-1 min-w-0 flex flex-col gap-2">
                    <div class="flex items-center gap-1 hide-mobile">
                        <?php foreach ($template_orderOptions as $option): ?>
                            <a href="index.php?order=<?php echo $option['value']; ?><?php echo !empty($template_keyword) ? '&keyword=' . urlencode($template_keyword) : ''; ?>"
                                class="px-3 py-1.5 rounded text-sm transition-all <?php echo $template_order == $option['value'] ? 'bg-primary-light text-primary' : 'text-sub hover:bg-hover hover:text-text'; ?>">
                                <?php echo $option['label']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <form action="index.php" method="get" class="flex items-center gap-2 hide-mobile">
                    <input type="hidden" name="order" value="<?php echo $template_order; ?>">
                    <input type="text" name="keyword" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" placeholder="搜索标题、关键讨论..." value="<?php echo htmlspecialchars($template_keyword ?? ''); ?>">
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed">搜索</button>
                </form>
                <form action="index.php" method="get" class="flex gap-2 mobile-only">
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
            <?php if ($template_threads): ?>
                <div class="flex flex-col">
                    <?php foreach ($template_threads as $thread): ?>
                        <?php echo \Lib\ThreadHelper::renderThread($thread, $template_users, $template_forums, ['show_forum' => true]); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-8 text-center text-muted">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/>
                    </svg>
                    <p>暂无话题</p>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($template_pages > 1): ?>
                <?php
                $baseUrl = 'index.php?order=' . $template_order;
                if (!empty($template_keyword)) {
                    $baseUrl .= '&keyword=' . urlencode($template_keyword);
                }
                echo \Lib\Helper::renderPagination($template_page, $template_pages, $baseUrl);
                ?>
            <?php endif; ?>
        </div>

        <!-- Collapsed Forums -->
        <?php if ($template_collapsedTotal > 0): ?>
        <div class="bg-panel border border-border rounded shadow-sm overflow-hidden">
            <div id="collapse-header" class="flex items-center justify-between p-3 rounded-t bg-soft cursor-pointer hover:bg-hover transition-colors" onclick="toggleCollapsed()">
                <div class="flex items-center gap-2">
                    <svg id="collapse-icon" class="w-4 h-4 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6" />
                    </svg>
                    <span class="font-medium">折叠版块主题</span>
                    <span class="px-2 py-0.5 rounded-full bg-panel text-xs font-semibold text-primary"><?php echo $template_collapsedTotal; ?></span>
                </div>
                <span class="text-sm text-muted">点击展开</span>
            </div>
            <div id="collapsed-content" class="hidden">
                <?php if ($template_collapsedThreads): ?>
                    <div class="flex flex-col">
                        <?php foreach ($template_collapsedThreads as $thread): ?>
                            <?php echo \Lib\ThreadHelper::renderThread($thread, $template_users, $template_forums, ['show_forum' => true, 'truncate_subject' => true]); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="p-2 text-sm text-muted bg-soft border-t border-border">
                以上主题来自折叠版块：<?php echo implode(', ', array_column($template_collapsedForums, 'name')); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <aside class="md:hidden flex flex-col gap-4">
        <!-- User Card -->
        <div class="bg-panel border border-border rounded shadow-sm">
            <div class="p-4">
                <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
                    <div class="flex items-center gap-3 mb-4">
                        <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>">
                            <div class="w-14 h-14 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold text-xl flex-shrink-0 overflow-hidden">
                                <?php if (!empty($template_user['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($template_user['avatar']); ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?php echo \Lib\Helper::getAvatarInitial($template_user['username']); ?>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>" class="font-semibold block truncate hover:text-primary transition-colors"><?php echo htmlspecialchars($template_user['username']); ?></a>
                            <div class="text-sm text-muted">
                                <?php if ($template_userGroup): ?>
                                    <span><?php echo htmlspecialchars($template_userGroup['name'] ?? ''); ?></span>
                                    <span> · </span>
                                <?php endif; ?>
                                <span>金币：<?php echo (int)($template_userStats['credit'] ?? 0); ?></span>
                            </div>
                        </div>
                    </div>

                    <form method="post" action="index.php?c=member&a=signin" class="mb-4">
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed <?php echo !empty($template_userStats['signed_today']) ? 'bg-soft border-border text-text hover:bg-hover' : 'bg-primary border-primary text-white hover:bg-primary-dark'; ?> h-control-sm px-3 text-sm w-full" <?php echo !empty($template_userStats['signed_today']) ? 'disabled' : ''; ?>>
                            <?php echo !empty($template_userStats['signed_today']) ? '今日已签到' : '每日签到'; ?>
                        </button>
                    </form>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="index.php?c=member&a=settings" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover h-control-sm px-3 text-sm text-center">个人设置</a>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=favorites" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover h-control-sm px-3 text-sm text-center">我的收藏</a>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=threads" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover h-control-sm px-3 text-sm text-center">我的话题</a>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=credits" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover h-control-sm px-3 text-sm text-center">金币明细</a>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-14 h-14 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold text-xl flex-shrink-0">G</div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold">访客</div>
                            <div class="text-sm text-muted">登录后可同步主题、收藏、通知与私信记录</div>
                        </div>
                    </div>
                    <div class="flex gap-2 mb-4">
                        <div class="flex-1 flex flex-col items-center gap-1 p-3 rounded bg-soft">
                            <span class="text-lg font-bold"><?php echo count($template_noticeThreads); ?></span>
                            <span class="text-xs text-muted">公告</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1 p-3 rounded bg-soft">
                            <span class="text-lg font-bold"><?php echo count($template_hotForums); ?></span>
                            <span class="text-xs text-muted">版块</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1 p-3 rounded bg-soft">
                            <span class="text-lg font-bold"><?php echo (int)$template_onlineCount; ?></span>
                            <span class="text-xs text-muted">在线</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="index.php?c=auth&a=login" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark h-control-sm px-3 text-sm text-center">登录</a>
                        <a href="index.php?c=auth&a=register" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover h-control-sm px-3 text-sm text-center">注册</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notice -->
        <div class="bg-panel border border-border rounded shadow-sm">
            <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
                <h3>社区公告</h3>
            </div>
            <div class="p-0">
                <?php if (!empty($template_noticeThreads)): ?>
                    <div class="flex flex-col">
                        <?php foreach ($template_noticeThreads as $thread): ?>
                            <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="flex items-center gap-2 p-2 px-4 border-b border-border last:border-b-0 hover:bg-hover transition-colors">
                                <span class="flex-1 min-w-0 font-medium truncate text-primary text-sm"><?php echo htmlspecialchars($thread['subject']); ?></span>
                                <span class="text-xs text-muted flex-shrink-0 ml-2"><?php echo date('Y-m-d', $thread['dateline']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center text-muted">
                        <p>暂无公告</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hot Forums -->
        <div class="bg-panel border border-border rounded shadow-sm">
            <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
                <h3>热门版块</h3>
            </div>
            <div class="p-0">
                <?php if (!empty($template_hotForums)): ?>
                    <div class="flex flex-col">
                        <?php foreach ($template_hotForums as $index => $forum): ?>
                            <a href="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>" class="flex items-center gap-2 p-2 border-b border-border last:border-b-0 hover:bg-hover transition-colors">
                                <span class="w-5 h-5 flex items-center justify-center rounded-sm text-xs font-semibold flex-shrink-0 <?php echo $index < 3 ? 'bg-success-light text-success' : 'bg-soft text-muted'; ?>"><?php echo $index + 1; ?></span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-sm"><?php echo htmlspecialchars($forum['name']); ?></div>
                                    <div class="text-xs text-muted"><?php echo (int)$forum['thread_num']; ?> 主题 · <?php echo (int)$forum['reply_num']; ?> 回复</div>
                                </div>
                                <div class="text-center flex-shrink-0 ml-2">
                                    <div class="font-semibold text-sm"><?php echo (int)$forum['today_num']; ?></div>
                                    <div class="text-xs text-muted">今日</div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center text-muted">
                        <p>暂无数据</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Online Count -->
        <div class="bg-panel border border-border rounded shadow-sm">
            <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
                <h3>当前在线</h3>
            </div>
            <div class="p-0">
                <a href="index.php?c=member&a=online" class="block p-4 text-center hover:bg-hover transition-colors">
                    <div class="text-2xl font-bold"><?php echo $template_onlineCount; ?></div>
                    <div class="text-sm text-muted">人在线</div>
                </a>
            </div>
        </div>
    </aside>
</div>

<script>
    var collapsedStateKey = 'xforum.home.collapsedThreads.expanded';

    function setCollapsedExpanded(expanded) {
        var content = document.getElementById('collapsed-content');
        var header = document.getElementById('collapse-header');
        var icon = document.getElementById('collapse-icon');
        var hint = header ? header.querySelector('[data-role="collapse-hint"]') : null;

        if (!content || !header || !icon) {
            return;
        }

        content.classList.toggle('hidden', !expanded);
        icon.classList.toggle('rotate-90', expanded);

        if (hint) {
            hint.textContent = expanded ? '点击收起' : '点击展开';
        }
    }

    function toggleCollapsed() {
        var content = document.getElementById('collapsed-content');
        if (!content) {
            return;
        }

        var expanded = content.classList.contains('hidden');
        setCollapsedExpanded(expanded);

        try {
            sessionStorage.setItem(collapsedStateKey, expanded ? '1' : '0');
        } catch (e) {
            // Ignore storage errors in private browsing or restricted environments.
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        try {
            setCollapsedExpanded(sessionStorage.getItem(collapsedStateKey) === '1');
        } catch (e) {
            setCollapsedExpanded(false);
        }
    });
</script>
