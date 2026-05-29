<link rel="stylesheet" href="assets/css/forum.css">

<div class="page-grid">
    <!-- Main Content -->
    <div class="main-stack">
        <!-- Thread List Card -->
        <div class="card">
            <!-- Card Header -->
            <div class="card-header-col">
                <div class="flex flex-wrap items-center gap-2 text-sm text-muted">
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
                            <span class="badge badge-soft"><?php echo (int)$template_forum['thread_num']; ?> 主题</span>
                            <?php if ((int)$template_forum['reply_num'] > 0): ?>
                                <span class="badge badge-soft"><?php echo (int)$template_forum['reply_num']; ?> 回复</span>
                            <?php endif; ?>
                            <span class="badge badge-success">今日 <?php echo (int)$template_forum['today_num']; ?></span>
                        </div>
                    </div>
                    <?php if (isset($template_user)): ?>
                        <a href="index.php?c=thread&a=create&fid=<?php echo $template_forum['fid']; ?>" class="btn btn-primary flex-shrink-0">发布主题</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($template_subForums)): ?>
                <div class="card-body forum-children-section">
                    <div class="forum-children-header">
                        <div class="font-semibold">子版块</div>
                        <span class="badge badge-soft"><?php echo count($template_subForums); ?> 个</span>
                    </div>
                    <div class="forum-children-list">
                        <?php foreach ($template_subForums as $subForum): ?>
                            <a href="index.php?c=forum&a=index&fid=<?php echo $subForum['fid']; ?>" class="forum-children-item">
                                <div class="forum-children-main min-w-0">
                                    <div class="forum-children-title-row">
                                        <?php if ((int)($subForum['today_num'] ?? 0) > 0): ?>
                                            <span class="badge badge-xs badge-primary">今日 +<?php echo (int)$subForum['today_num']; ?></span>
                                        <?php endif; ?>
                                        <span class="forum-children-name"><?php echo htmlspecialchars($subForum['name']); ?></span>
                                    </div>
                                    <div class="text-sm text-muted">
                                        <?php if (!empty($subForum['description'])): ?>
                                            <?php echo htmlspecialchars($subForum['description']); ?>
                                        <?php else: ?>
                                            暂无版块简介
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="forum-children-stats hide-mobile">
                                    <span><?php echo (int)($subForum['thread_num'] ?? 0); ?> 主题</span>
                                    <?php if ((int)($subForum['reply_num'] ?? 0) > 0): ?>
                                        <span><?php echo (int)$subForum['reply_num']; ?> 回复</span>
                                    <?php endif; ?>
                                </div>
                                <svg class="thread-item-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Toolbar -->
            <div class="toolbar">
                <div class="flex-1 min-w-0 flex flex-col gap-2">
                    <div class="flex items-center gap-1 hide-mobile">
                        <?php foreach ($template_orderOptions as $option): ?>
                            <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>&order=<?php echo $option['value']; ?><?php echo !empty($template_keyword) ? '&keyword=' . urlencode($template_keyword) : ''; ?>"
                                class="filter-link <?php echo $template_order == $option['value'] ? 'active' : ''; ?>">
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
                    <input type="text" name="keyword" class="form-control" placeholder="搜索标题..." value="<?php echo htmlspecialchars($template_keyword ?? ''); ?>">
                    <button type="submit" class="btn">搜索</button>
                </form>
                <form action="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>" method="get" class="flex gap-2 mobile-only">
                    <input type="hidden" name="c" value="forum">
                    <input type="hidden" name="a" value="index">
                    <input type="hidden" name="fid" value="<?php echo $template_forum['fid']; ?>">
                    <select name="order" onchange="this.form.submit()" class="flex-1 form-control">
                        <?php foreach ($template_orderOptions as $option): ?>
                            <option value="<?php echo $option['value']; ?>" <?php echo $template_order == $option['value'] ? 'selected' : ''; ?>>
                                <?php echo $option['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="keyword" class="flex-1 form-control" placeholder="搜索..." value="<?php echo htmlspecialchars($template_keyword ?? ''); ?>">
                    <button type="submit" class="btn">搜索</button>
                </form>
            </div>
            <?php if (!empty($template_searchError)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($template_searchError); ?></div>
            <?php endif; ?>

            <?php if (!empty($template_pendingThreads)): ?>
                <div class="forum-pending-section">
                    <div class="forum-pending-header">
                        <span class="font-semibold">待审核主题</span>
                        <span class="badge badge-warning"><?php echo count($template_pendingThreads); ?></span>
                    </div>
                    <div class="forum-pending-list">
                        <?php foreach ($template_pendingThreads as $pendingThread): ?>
                            <?php $pendingUid = (int)($pendingThread['uid'] ?? 0); ?>
                            <div class="forum-pending-item">
                                <div class="forum-pending-main">
                                    <div class="forum-pending-title-row">
                                        <a href="index.php?c=thread&a=index&tid=<?php echo (int)$pendingThread['tid']; ?>" class="forum-pending-title"><?php echo htmlspecialchars($pendingThread['subject']); ?></a>
                                        <span class="badge badge-xs badge-warning">待审核</span>
                                        <div class="forum-pending-actions">
                                            <a href="index.php?c=thread&a=auditThread&tid=<?php echo (int)$pendingThread['tid']; ?>&status=reject" class="btn btn-soft btn-sm" data-post-link="1">拒绝</a>
                                            <a href="index.php?c=thread&a=auditThread&tid=<?php echo (int)$pendingThread['tid']; ?>&status=pass" class="btn btn-primary btn-sm" data-post-link="1">通过</a>
                                            <a href="index.php?c=thread&a=auditThread&tid=<?php echo (int)$pendingThread['tid']; ?>&status=delete" class="btn btn-danger btn-sm" data-post-link="1">删除</a>
                                        </div>
                                    </div>
                                    <div class="thread-item-meta">
                                        <span><?php echo htmlspecialchars($template_users[$pendingUid]['username'] ?? '匿名'); ?></span>
                                        <span><?php echo \Lib\Helper::formatTime((int)$pendingThread['dateline']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Thread List -->
            <?php if ($template_threads): ?>
                <div class="list-stack">
                    <?php foreach ($template_threads as $thread): ?>
                        <?php echo \Lib\ThreadHelper::renderThread($thread, $template_users, [], [
                            'badge' => !empty($template_keyword) ? ['text' => '命中搜索', 'class' => 'bg-primary-light text-primary'] : null
                        ]); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/>
                    </svg>
                    <p>暂无主题，点击上方按钮发布新主题</p>
                </div>
            <?php endif; ?>

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
    <aside class="sidebar-stack">
        <?php if (!empty($template_moderators)): ?>
        <div class="card">
            <div class="card-header">
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
            <div class="card-body-flush">
                <div class="list-stack">
                    <?php foreach ($template_moderators as $moderator): ?>
                        <?php $user = $template_moderatorUsers[$moderator['uid']] ?? null; ?>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $moderator['uid']; ?>" class="list-link list-link-sm">
                            <div class="avatar avatar-sm">
                                <?php if ($user && !empty($user['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?php echo \Lib\Helper::getAvatarInitial($user['username'] ?? '?'); ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm"><?php echo htmlspecialchars($user['username'] ?? '未知用户'); ?></div>
                                <div class="text-xs text-muted">
                                    <?php echo $moderator['end_date'] ? '任期至 ' . \Lib\Helper::formatTime((int)$moderator['end_date']) : '永久版主'; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($template_hotThreads)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                    本版热帖
                </h3>
            </div>
            <div class="card-body-flush">
                <div class="list-stack">
                    <?php foreach ($template_hotThreads as $index => $thread): ?>
                        <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="list-link list-link-sm">
                                <span class="rank-badge <?php echo $index < 3 ? 'hot' : ''; ?>"><?php echo $index + 1; ?></span>
                                <div class="flex-1 min-w-0 flex flex-col gap-0.5">
                                    <span class="text-sm text-text truncate"><?php echo htmlspecialchars($thread['subject']); ?></span>
                                    <?php if ((int)$thread['reply_num'] > 0): ?>
                                        <span class="text-xs text-muted"><?php echo (int)$thread['reply_num']; ?> 回复</span>
                                    <?php endif; ?>
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
