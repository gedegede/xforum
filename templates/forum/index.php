<div class="grid grid-cols-3">
    <div class="main-content">
        <div class="card">
            <div class="thread-header">
                <div class="breadcrumb">
                    <a href="index.php">首页</a>
                    <span>/</span>
                    <?php if ($template_parentForum): ?>
                        <a href="index.php?c=forum&a=index&fid=<?php echo $template_parentForum['fid']; ?>"><?php echo htmlspecialchars($template_parentForum['name']); ?></a>
                        <span>/</span>
                    <?php endif; ?>
                    <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>">版块</a>
                </div>
                <div class="thread-title-row">
                    <div class="flex items-center justify-between gap-md" style="align-items:flex-start;">
                        <div class="min-width-0">
                            <h1><?php echo htmlspecialchars($template_forum['name']); ?></h1>
                            <div class="flex flex-wrap gap-sm mt-lg">
                                <span class="badge badge-gray"><?php echo (int)$template_forum['thread_num']; ?> 主题</span>
                                <span class="badge badge-gray"><?php echo (int)$template_forum['reply_num']; ?> 回复</span>
                                <span class="badge badge-green">今日 <?php echo (int)$template_forum['today_num']; ?></span>
                            </div>
                        </div>
                        <?php if (isset($template_user)): ?>
                            <a href="index.php?c=thread&a=create&fid=<?php echo $template_forum['fid']; ?>" class="btn btn-primary">发布主题</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="toolbar">
                <div class="min-width-0" style="display:flex;flex-direction:column;gap:8px;flex:1;">
                    <div class="text-secondary font-sm hide-mobile">切换当前版块的排序视角</div>
                    <div class="tabs hide-mobile">
                    <?php foreach ($template_orderOptions as $option): ?>
                        <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>&order=<?php echo $option['value']; ?><?php echo !empty($template_keyword) ? '&keyword=' . urlencode($template_keyword) : ''; ?>" 
                           class="tab <?php echo $template_order == $option['value'] ? 'active' : ''; ?>">
                            <?php echo $option['label']; ?>
                        </a>
                    <?php endforeach; ?>
                    </div>
                </div>
                <form action="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>" method="get" class="search-box hide-mobile">
                    <input type="hidden" name="c" value="forum">
                    <input type="hidden" name="a" value="index">
                    <input type="hidden" name="fid" value="<?php echo $template_forum['fid']; ?>">
                    <input type="hidden" name="order" value="<?php echo $template_order; ?>">
                    <input type="text" name="keyword" placeholder="搜索标题..." value="<?php echo htmlspecialchars($template_keyword ?? ''); ?>">
                    <button type="submit" class="btn btn-search">搜索</button>
                </form>
                <form action="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>" method="get" class="mobile-toolbar">
                    <input type="hidden" name="c" value="forum">
                    <input type="hidden" name="a" value="index">
                    <input type="hidden" name="fid" value="<?php echo $template_forum['fid']; ?>">
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
                                <?php if (!empty($template_keyword)): ?>
                                    <span class="badge badge-blue">命中搜索</span>
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
                        <p>暂无主题，点击上方按钮发布新主题</p>
                    </div>
                <?php endif; ?>
            </div>
            
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

    <div class="sidebar">
        <div class="card">
            <div class="sidebar-header">
                <h3><?php echo htmlspecialchars($template_forum['name']); ?></h3>
            </div>
            <div class="sidebar-body">
                <p class="forum-desc"><?php echo htmlspecialchars($template_forum['description'] ?? '分享和讨论各类话题'); ?></p>
                <div class="user-stats">
                    <div class="user-stat">
                        <div class="user-stat-value"><?php echo $template_forum['thread_num']; ?></div>
                        <div class="user-stat-label">主题</div>
                    </div>
                    <div class="user-stat">
                        <div class="user-stat-value"><?php echo $template_forum['reply_num']; ?></div>
                        <div class="user-stat-label">回复</div>
                    </div>
                    <div class="user-stat">
                        <div class="user-stat-value"><?php echo $template_forum['today_num']; ?></div>
                        <div class="user-stat-label">今日</div>
                    </div>
                </div>
                <div class="mt-lg">
                    <?php if (isset($template_user) && !empty($template_user)): ?>
                        <a href="index.php?c=thread&a=create&fid=<?php echo $template_forum['fid']; ?>" class="btn btn-primary w-full">在本版块发帖</a>
                    <?php else: ?>
                        <a href="index.php?c=auth&a=login" class="btn btn-primary w-full">登录后参与讨论</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($template_moderators)): ?>
        <div class="card">
            <div class="sidebar-header">
                <h3>版主</h3>
            </div>
            <div class="sidebar-body">
                <div class="menu-list">
                    <?php foreach ($template_moderators as $moderator): ?>
                        <?php $modUser = $template_modUsers[$moderator['uid']] ?? null; ?>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $moderator['uid']; ?>" class="menu-item">
                            <div class="avatar avatar-sm">
                                <?php if ($modUser && !empty($modUser['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($modUser['avatar']); ?>" alt="">
                                <?php else: ?>
                                    <?php echo \Lib\Helper::getAvatarInitial($modUser['username'] ?? '?'); ?>
                                <?php endif; ?>
                            </div>
                            <span class="flex-1"><?php echo htmlspecialchars($modUser['username'] ?? '未知用户'); ?></span>
                            <span class="badge badge-gray">版主</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
