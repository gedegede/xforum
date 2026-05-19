<div class="grid grid-cols-3">
    <div class="main-content">
        <div class="card">
            <div class="thread-header">
                <div class="breadcrumb">
                    <a href="index.php">首页</a>
                    /
                    <?php if ($parentForum): ?>
                        <a href="index.php?c=forum&a=index&fid=<?php echo $parentForum['fid']; ?>"><?php echo htmlspecialchars($parentForum['name']); ?></a>
                        /
                    <?php endif; ?>
                    <a href="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>">版块</a>
                </div>
                <div class="thread-title-row">
                    <div class="flex items-center justify-between gap-md">
                        <h1><?php echo htmlspecialchars($forum['name']); ?></h1>
                        <?php if (isset($user)): ?>
                            <a href="index.php?c=thread&a=create&fid=<?php echo $forum['fid']; ?>" class="btn btn-primary">发布主题</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="toolbar">
                <div class="tabs hide-mobile">
                    <?php foreach ($orderOptions as $option): ?>
                        <a href="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>&order=<?php echo $option['value']; ?><?php echo !empty($keyword) ? '&keyword=' . urlencode($keyword) : ''; ?>" 
                           class="tab <?php echo $order == $option['value'] ? 'active' : ''; ?>">
                            <?php echo $option['label']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <form action="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>" method="get" class="search-box hide-mobile">
                    <input type="hidden" name="c" value="forum">
                    <input type="hidden" name="a" value="index">
                    <input type="hidden" name="fid" value="<?php echo $forum['fid']; ?>">
                    <input type="hidden" name="order" value="<?php echo $order; ?>">
                    <input type="text" name="keyword" placeholder="搜索标题..." value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                    <button type="submit" class="btn btn-search">搜索</button>
                </form>
                <form action="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>" method="get" class="mobile-toolbar">
                    <input type="hidden" name="c" value="forum">
                    <input type="hidden" name="a" value="index">
                    <input type="hidden" name="fid" value="<?php echo $forum['fid']; ?>">
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
                        <p>暂无主题，点击上方按钮发布新主题</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($pages > 1): ?>
                <div class="pagination-container">
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <a href="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>&order=<?php echo $order; ?><?php echo !empty($keyword) ? '&keyword=' . urlencode($keyword) : ''; ?>&page=<?php echo $i; ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="sidebar">
        <div class="card">
            <div class="sidebar-header">
                <h3><?php echo htmlspecialchars($forum['name']); ?></h3>
            </div>
            <div class="sidebar-body">
                <p class="forum-desc"><?php echo htmlspecialchars($forum['description'] ?? '分享和讨论各类话题'); ?></p>
                <div class="user-stats">
                    <div class="user-stat">
                        <div class="user-stat-value"><?php echo $forum['thread_num']; ?></div>
                        <div class="user-stat-label">主题</div>
                    </div>
                    <div class="user-stat">
                        <div class="user-stat-value"><?php echo $forum['reply_num']; ?></div>
                        <div class="user-stat-label">回复</div>
                    </div>
                    <div class="user-stat">
                        <div class="user-stat-value"><?php echo $forum['today_num']; ?></div>
                        <div class="user-stat-label">今日</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>