<div class="grid grid-cols-3">
    <div class="main-content">
        <div class="card">
            <div class="thread-header">
                <div class="thread-title-row">
                    <div class="flex items-center justify-between gap-md">
                        <h1>XForum</h1>
                        <?php if (isset($user)): ?>
                            <a href="index.php?c=thread&a=create" class="btn btn-primary">发布主题</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="toolbar">
                <div class="tabs hide-mobile">
                    <?php foreach ($orderOptions as $option): ?>
                        <a href="index.php?order=<?php echo $option['value']; ?><?php echo !empty($keyword) ? '&keyword=' . urlencode($keyword) : ''; ?>"
                           class="tab <?php echo $order == $option['value'] ? 'active' : ''; ?>">
                            <?php echo $option['label']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <form action="index.php" method="get" class="search-box hide-mobile">
                    <input type="hidden" name="order" value="<?php echo $order; ?>">
                    <input type="text" name="keyword" placeholder="搜索标题..." value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                    <button type="submit" class="btn btn-search">搜索</button>
                </form>
                <form action="index.php" method="get" class="mobile-toolbar">
                    <select name="order" class="order-select" onchange="this.form.submit()">
                        <?php foreach ($orderOptions as $option): ?>
                            <option value="<?php echo $option['value']; ?>" <?php echo $order == $option['value'] ? 'selected' : ''; ?>>
                                <?php echo $option['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="keyword" placeholder="搜索..." value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                    <button type="submit" class="btn btn-search">搜索</button>
                </form>
            </div>

            <div class="post-list">
                <?php foreach ($threads as $thread): ?>
                    <?php $user = $users[$thread['uid']] ?? null; ?>
                    <?php $forum = $forums[$thread['fid']] ?? null; ?>
                    <div class="thread-item">
                        <div class="thread-avatar">
                            <div class="avatar">
                                <?php if ($user && !empty($user['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($user['username'] ?? '?', 0, 1)); ?>
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
                                <span><?php echo htmlspecialchars($user['username'] ?? '匿名'); ?></span>
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
                <?php if (empty($threads)): ?>
                    <div class="empty-state">
                        <p>暂无话题</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($pages > 1): ?>
                <div class="pagination-container">
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <a href="index.php?order=<?php echo $order; ?><?php echo !empty($keyword) ? '&keyword=' . urlencode($keyword) : ''; ?>&page=<?php echo $i; ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="sidebar">
        <div class="card">
            <div class="user-card">
                <div class="user-avatar">
                    <div class="avatar brand-avatar">N</div>
                </div>
                <div class="user-info">
                    <div class="user-name">NodeSeek</div>
                    <div class="user-level">Lv.5 探索者</div>
                </div>
            </div>
            <div class="user-stats">
                <div class="user-stat">
                    <div class="user-stat-value">1</div>
                    <div class="user-stat-label">主题</div>
                </div>
                <div class="user-stat">
                    <div class="user-stat-value">15</div>
                    <div class="user-stat-label">帖子</div>
                </div>
                <div class="user-stat">
                    <div class="user-stat-value">12</div>
                    <div class="user-stat-label">金币</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="sidebar-header">
                <h3>当前在线</h3>
            </div>
            <div class="sidebar-body online-list">
                <span class="online-user">NodeSeek</span>
                <span class="online-user">访客206818</span>
                <span class="online-user">访客205497</span>
            </div>
        </div>

        <div class="card">
            <div class="sidebar-header">
                <h3>热门标签</h3>
            </div>
            <div class="sidebar-body">
                <div class="tag-box">
                    <div class="tags">
                        <span class="tag">服务器</span>
                        <span class="tag">VPS</span>
                        <span class="tag">小鸡</span>
                        <span class="tag">NAT</span>
                        <span class="tag">主机</span>
                        <span class="tag">优惠</span>
                        <span class="tag">优惠码</span>
                        <span class="tag">促销</span>
                        <span class="tag">建站</span>
                        <span class="tag">小鸡测评</span>
                        <span class="tag">Hostodo</span>
                        <span class="tag">Racknerd</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="sidebar-header">
                <h3>社区公告</h3>
            </div>
            <div class="sidebar-body">
                <div class="announcement">
                    <p>社区规则与发帖规范</p>
                    <p>违规处理与封号名单公示</p>
                    <p>请遵守社区规则，共同维护良好的交流环境。</p>
                </div>
            </div>
        </div>
    </div>
</div>
