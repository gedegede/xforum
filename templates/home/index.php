<div class="grid grid-cols-3">
    <div class="main-content">
        <?php if ($template_canManage && !empty($template_modStats)): ?>
            <div class="card mod-card">
                <div class="mod-card-header">
                    <h3>管理提示</h3>
                </div>
                <div class="mod-card-body">
                    <div class="mod-stats">
                        <a href="index.php?c=admin&a=threads&status=pending" class="mod-stat-item">
                            <span class="mod-stat-value"><?php echo $template_modStats['pending_threads'] ?? 0; ?></span>
                            <span class="mod-stat-label">待审核主题</span>
                        </a>
                        <a href="index.php?c=admin&a=threads&status=pending_posts" class="mod-stat-item">
                            <span class="mod-stat-value"><?php echo $template_modStats['pending_posts'] ?? 0; ?></span>
                            <span class="mod-stat-label">待审核回帖</span>
                        </a>
                        <a href="index.php?c=admin&a=threads&fid=<?php echo (int)($template_settings['report_forum_fid'] ?? 0); ?>" class="mod-stat-item">
                            <span class="mod-stat-value"><?php echo $template_modStats['pending_reports'] ?? 0; ?></span>
                            <span class="mod-stat-label">待处理举报</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="card">
            <div class="hero-head">
                <div class="flex items-center justify-between gap-md" style="align-items:flex-start;">
                    <div class="min-width-0">
                        <h1>XForum</h1>
                        <div class="flex flex-wrap gap-sm mt-lg">
                            <span class="badge badge-gray"><?php echo count($template_threads); ?> 条当前列表</span>
                            <span class="badge badge-gray"><?php echo (int)$template_onlineCount; ?> 人在线</span>
                            <?php if (!empty($template_keyword)): ?>
                                <span class="badge badge-green">搜索：<?php echo htmlspecialchars($template_keyword); ?></span>
                            <?php else: ?>
                                <span class="badge badge-gray">排序：<?php echo htmlspecialchars($template_orderOptions[array_search($template_order, array_column($template_orderOptions, 'value'))]['label'] ?? '最后回复'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="hero-actions" style="flex-direction:column;align-items:stretch;min-width:168px;">
                        <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
                            <a href="index.php?c=forum&a=index&from=create" class="btn btn-primary">发布主题</a>
                            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=threads" class="btn btn-secondary">我的主题</a>
                        <?php else: ?>
                            <a href="index.php?c=auth&a=register" class="btn btn-primary">创建账号</a>
                            <a href="index.php?c=auth&a=login" class="btn btn-secondary">登录参与讨论</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="toolbar">
                <div class="min-width-0" style="display:flex;flex-direction:column;gap:8px;flex:1;">
                    <div class="text-secondary font-sm hide-mobile">按不同维度切换首页信息流</div>
                    <div class="tabs hide-mobile">
                    <?php foreach ($template_orderOptions as $option): ?>
                        <a href="index.php?order=<?php echo $option['value']; ?><?php echo !empty($template_keyword) ? '&keyword=' . urlencode($template_keyword) : ''; ?>"
                           class="tab <?php echo $template_order == $option['value'] ? 'active' : ''; ?>">
                            <?php echo $option['label']; ?>
                        </a>
                    <?php endforeach; ?>
                    </div>
                </div>
                <form action="index.php" method="get" class="search-box hide-mobile">
                    <input type="hidden" name="order" value="<?php echo $template_order; ?>">
                    <input type="text" name="keyword" placeholder="搜索标题、关键讨论..." value="<?php echo htmlspecialchars($template_keyword ?? ''); ?>">
                    <button type="submit" class="btn btn-search">搜索</button>
                </form>
                <form action="index.php" method="get" class="mobile-toolbar">
                    <select name="order" class="order-select" onchange="this.form.submit()">
                        <?php foreach ($template_orderOptions as $option): ?>
                            <option value="<?php echo $option['value']; ?>" <?php echo $template_order == $option['value'] ? 'selected' : ''; ?>>
                                <?php echo $option['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="keyword" placeholder="搜索..." value="<?php echo htmlspecialchars($template_keyword ?? ''); ?>">
                    <button type="submit" class="btn btn-search">搜索</button>
                </form>
            </div>

            <div class="post-list">
                <?php foreach ($template_threads as $thread): ?>
                    <?php $author = $template_users[$thread['uid']] ?? null; ?>
                    <?php $forum = $template_forums[$thread['fid']] ?? null; ?>
                    <div class="thread-item">
                        <div class="thread-avatar">
                            <div class="avatar">
                                <?php if ($author && !empty($author['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($author['avatar']); ?>" alt="">
                                <?php else: ?>
                                    <?php echo \Lib\Helper::getAvatarInitial($author['username'] ?? '?'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="thread-content">
                            <div class="thread-title">
                                <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>"><?php echo htmlspecialchars($thread['subject']); ?></a>
                                <?php if ($forum): ?>
                                    <span class="badge badge-green"><?php echo htmlspecialchars($forum['name']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="thread-meta">
                                <span><?php echo htmlspecialchars($author['username'] ?? '匿名'); ?></span>
                                <span class="separator">•</span>
                                <span>发布于 <?php echo date('Y-m-d H:i', $thread['dateline']); ?></span>
                                <?php if (!empty($thread['reply_time']) && (int)$thread['reply_time'] !== (int)$thread['dateline']): ?>
                                    <span class="separator">•</span>
                                    <span>最后活跃 <?php echo date('Y-m-d H:i', (int)$thread['reply_time']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="thread-stats">
                            <div class="thread-stat">
                                <span class="thread-stat-value"><?php echo $thread['view_num']; ?></span>
                                <span class="thread-stat-label">浏览</span>
                            </div>
                            <div class="thread-stat">
                                <span class="thread-stat-value"><?php echo $thread['reply_num']; ?></span>
                                <span class="thread-stat-label">回复</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($template_threads)): ?>
                    <div class="empty-state">
                        <p>暂无话题</p>
                    </div>
                <?php endif; ?>
            </div>

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

        <?php if ($template_collapsedTotal > 0): ?>
        <div class="collapse-section">
            <div id="collapse-header" class="collapse-header collapsed" onclick="toggleCollapsed()">
                <div class="collapse-title">
                    <svg class="collapse-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                    <span class="collapse-label">折叠版块主题</span>
                    <span class="collapse-count"><?php echo $template_collapsedTotal; ?></span>
                </div>
                <span class="collapse-hint">点击展开</span>
            </div>
            <div id="collapsed-content" class="collapse-content" style="display: none;">
                <?php foreach ($template_collapsedThreads as $thread): ?>
                    <?php $author = $template_users[$thread['uid']] ?? null; ?>
                    <?php $forum = $template_forums[$thread['fid']] ?? null; ?>
                    <div class="thread-item">
                        <div class="thread-avatar">
                            <div class="avatar">
                                <?php if ($author && !empty($author['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($author['avatar']); ?>" alt="">
                                <?php else: ?>
                                    <?php echo \Lib\Helper::getAvatarInitial($author['username'] ?? '?'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="thread-content">
                            <div class="thread-title">
                                <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>"><?php echo htmlspecialchars($thread['subject']); ?></a>
                                <?php if ($forum): ?>
                                    <span class="badge badge-gray"><?php echo htmlspecialchars($forum['name']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="thread-meta">
                                <span><?php echo htmlspecialchars($author['username'] ?? '匿名'); ?></span>
                                <span class="separator">•</span>
                                <span><?php echo date('Y-m-d H:i', $thread['dateline']); ?></span>
                            </div>
                        </div>
                        <div class="thread-stats">
                            <div class="thread-stat">
                                <span class="thread-stat-value"><?php echo $thread['view_num']; ?></span>
                                <span class="thread-stat-label">浏览</span>
                            </div>
                            <div class="thread-stat">
                                <span class="thread-stat-value"><?php echo $thread['reply_num']; ?></span>
                                <span class="thread-stat-label">回复</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="collapse-footer">
                以上主题来自折叠版块：<?php echo implode(', ', array_column($template_collapsedForums, 'name')); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="sidebar">
        <div class="card">
            <div class="user-card">
                <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
                    <div class="user-header">
                        <div class="user-avatar">
                            <div class="avatar">
                                <?php if (!empty($template_user['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($template_user['avatar']); ?>" alt="">
                                <?php else: ?>
                                    <?php echo \Lib\Helper::getAvatarInitial($template_user['username']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($template_user['username']); ?></div>
                            <div class="user-level">已登录，可参与发帖、回复、收藏与站内消息同步</div>
                        </div>
                    </div>
                    <div class="user-stats">
                        <div class="user-stat">
                            <div class="user-stat-value"><?php echo (int)($template_userStats['thread_count'] ?? 0); ?></div>
                            <div class="user-stat-label">主题</div>
                        </div>
                        <div class="user-stat">
                            <div class="user-stat-value"><?php echo (int)($template_userStats['post_count'] ?? 0); ?></div>
                            <div class="user-stat-label">回复</div>
                        </div>
                        <div class="user-stat">
                            <div class="user-stat-value"><?php echo (int)($template_userStats['notify_num'] ?? 0); ?></div>
                            <div class="user-stat-label">通知</div>
                        </div>
                    </div>
                    <div class="user-actions">
                        <div class="grid grid-cols-2 gap-sm">
                            <a href="index.php?c=member&a=settings" class="btn btn-secondary btn-sm">个人设置</a>
                            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=favorites" class="btn btn-secondary btn-sm">我的收藏</a>
                            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=threads" class="btn btn-secondary btn-sm">我的话题</a>
                            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=replies" class="btn btn-secondary btn-sm">我的回复</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="user-header">
                        <div class="user-avatar">
                            <div class="avatar brand-avatar">G</div>
                        </div>
                        <div class="user-info">
                            <div class="user-name">访客</div>
                            <div class="user-level">登录后可同步主题、收藏、通知与私信记录</div>
                        </div>
                    </div>
                    <div class="user-stats">
                        <div class="user-stat">
                            <div class="user-stat-value"><?php echo count($template_noticeThreads); ?></div>
                            <div class="user-stat-label">公告</div>
                        </div>
                        <div class="user-stat">
                            <div class="user-stat-value"><?php echo count($template_hotForums); ?></div>
                            <div class="user-stat-label">版块</div>
                        </div>
                        <div class="user-stat">
                            <div class="user-stat-value"><?php echo (int)$template_onlineCount; ?></div>
                            <div class="user-stat-label">在线</div>
                        </div>
                    </div>
                    <div class="user-actions">
                        <div class="grid grid-cols-2 gap-sm">
                            <a href="index.php?c=auth&a=login" class="btn btn-primary btn-sm">登录</a>
                            <a href="index.php?c=auth&a=register" class="btn btn-secondary btn-sm">注册</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="sidebar-header">
                <h3>社区公告</h3>
            </div>
            <div class="sidebar-body">
                <?php if (!empty($template_noticeThreads)): ?>
                    <div class="related-topics">
                        <?php foreach ($template_noticeThreads as $thread): ?>
                            <div class="related-item">
                                <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="related-title text-primary">
                                    <?php echo htmlspecialchars($thread['subject']); ?>
                                </a>
                                <div class="related-meta"><?php echo date('Y-m-d', $thread['dateline']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>暂无公告</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="sidebar-header">
                <h3>热门版块</h3>
            </div>
            <div class="sidebar-body">
                <?php if (!empty($template_hotForums)): ?>
                    <div class="hot-forum-list">
                        <?php foreach ($template_hotForums as $index => $forum): ?>
                            <a href="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>" class="hot-forum-item">
                                <span class="hot-forum-rank <?php echo $index < 3 ? 'is-top' : ''; ?>"><?php echo $index + 1; ?></span>
                                <span class="hot-forum-main">
                                    <span class="hot-forum-name"><?php echo htmlspecialchars($forum['name']); ?></span>
                                    <span class="hot-forum-meta"><?php echo (int)$forum['thread_num']; ?> 主题 · <?php echo (int)$forum['reply_num']; ?> 回复</span>
                                </span>
                                <div class="hot-forum-today">
                                    <strong><?php echo (int)$forum['today_num']; ?></strong>
                                    <span>今日</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>暂无数据</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="sidebar-header">
                <h3>当前在线</h3>
            </div>
            <div class="sidebar-body">
                <a href="index.php?c=member&a=online" class="online-count-link">
                    <span class="online-count-value"><?php echo $template_onlineCount; ?></span>
                    <span class="online-count-label">人在线</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
var collapsedStateKey = 'xforum.home.collapsedThreads.expanded';

function setCollapsedExpanded(expanded) {
    var content = document.getElementById('collapsed-content');
    var header = document.getElementById('collapse-header');
    var icon = document.querySelector('.collapse-icon');
    var hint = header ? header.querySelector('.collapse-hint') : null;

    if (!content || !header || !icon) {
        return;
    }

    content.style.display = expanded ? 'block' : 'none';
    header.classList.toggle('collapsed', !expanded);
    icon.style.transform = expanded ? 'rotate(90deg)' : 'rotate(0deg)';

    if (hint) {
        hint.textContent = expanded ? '点击收起' : '点击展开';
    }
}

function toggleCollapsed() {
    var content = document.getElementById('collapsed-content');
    if (!content) {
        return;
    }

    var expanded = content.style.display === 'none';
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
