<div class="card member-shell">
    <div class="member-hero">
        <div class="member-hero-main">
            <div class="avatar avatar-lg member-hero-avatar">
                <?php echo \Lib\Helper::getAvatarInitial($template_member['username']); ?>
            </div>
            <div class="member-hero-info">
                <h2><?php echo htmlspecialchars($template_member['username']); ?></h2>
                <div class="member-hero-meta">
                    注册于 <?php echo date('Y-m-d', $template_member['reg_date']); ?> · 
                    <?php echo $template_member['thread_num']; ?> 主题 · 
                    <?php echo $template_member['reply_num']; ?> 回复
                </div>
                <div class="member-badges">
                    <span class="badge badge-gray"><?php echo (int)$template_member['thread_num']; ?> 个主题</span>
                    <span class="badge badge-gray"><?php echo (int)$template_member['reply_num']; ?> 条回复</span>
                    <?php if ($template_isSelf): ?>
                        <span class="badge badge-green"><?php echo (int)($template_member['fav_num'] ?? 0); ?> 条收藏</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($template_isSelf): ?>
<div class="card member-tabs-card">
    <div class="tabs member-tabs">
        <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=threads" class="tab<?php echo $template_type == 'threads' ? ' active' : ''; ?>">我的主题</a>
        <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=replies" class="tab<?php echo $template_type == 'replies' ? ' active' : ''; ?>">我的回复</a>
        <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=favorites" class="tab<?php echo $template_type == 'favorites' ? ' active' : ''; ?>">我的收藏</a>
        <a href="index.php?c=member&a=settings" class="tab<?php echo (isset($_GET['c']) && $_GET['c'] == 'member' && $_GET['a'] == 'settings') ? ' active' : ''; ?>">个人设置</a>
        <a href="index.php?c=admin&a=index" class="tab<?php echo (isset($_GET['c']) && $_GET['c'] == 'admin') ? ' active' : ''; ?>">站点设置</a>
    </div>
</div>
<?php endif; ?>

<div class="card member-list-card">
    <div class="card-body">
        <div class="member-section-head">
            <h3>
                <?php if ($template_type == 'threads'): ?>
                    <?php echo $template_isSelf ? '我的主题' : 'Ta 的主题'; ?>
                <?php elseif ($template_type == 'replies'): ?>
                    <?php echo $template_isSelf ? '我的回复' : 'Ta 的回复'; ?>
                <?php else: ?>
                    我的收藏
                <?php endif; ?>
            </h3>
            <p>
                <?php if ($template_type == 'threads'): ?>
                    这里集中展示该用户发起的主题内容。
                <?php elseif ($template_type == 'replies'): ?>
                    这里集中展示该用户最近参与过的讨论。
                <?php else: ?>
                    这里集中展示你收藏保存的主题。
                <?php endif; ?>
            </p>
        </div>

        <?php if ($template_type == 'threads'): ?>
            <?php if ($template_threads): ?>
                <div class="member-list">
                <?php foreach ($template_threads as $thread): ?>
                    <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="member-list-item">
                        <div class="member-list-main">
                            <div class="member-list-title">
                                <?php echo htmlspecialchars($thread['subject']); ?>
                            </div>
                            <div class="member-list-meta">
                                <span><?php echo date('Y-m-d', $thread['dateline']); ?></span>
                                <span><?php echo $thread['reply_num']; ?> 回复</span>
                                <span><?php echo $thread['view_num']; ?> 浏览</span>
                            </div>
                        </div>
                        <div class="item-arrow"></div>
                    </a>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state member-empty">
                    <p>暂无主题</p>
                </div>
            <?php endif; ?>
        <?php elseif ($template_type == 'replies'): ?>
            <?php if ($template_posts): ?>
                <div class="member-list">
                <?php foreach ($template_posts as $post): ?>
                    <?php $thread = $template_threads[$post['tid']] ?? null; ?>
                    <a href="index.php?c=thread&a=index&tid=<?php echo $post['tid']; ?>" class="member-list-item">
                        <div class="member-list-main">
                            <div class="member-list-title">
                                <?php echo $thread ? htmlspecialchars($thread['subject']) : '已删除'; ?>
                                <span class="badge badge-gray">回复</span>
                            </div>
                            <div class="member-list-meta">
                                <span><?php echo date('Y-m-d', $post['dateline']); ?></span>
                            </div>
                        </div>
                        <div class="item-arrow"></div>
                    </a>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state member-empty">
                    <p>暂无回复</p>
                </div>
            <?php endif; ?>
        <?php elseif ($template_type == 'favorites'): ?>
            <?php if ($template_favorites): ?>
                <div class="member-list">
                <?php foreach ($template_favorites as $fav): ?>
                    <?php $thread = $template_threads[$fav['tid']] ?? null; ?>
                    <a href="index.php?c=thread&a=index&tid=<?php echo $fav['tid']; ?>" class="member-list-item">
                        <div class="member-list-main">
                            <div class="member-list-title">
                                <?php echo $thread ? htmlspecialchars($thread['subject']) : '已删除'; ?>
                                <span class="badge badge-green">收藏</span>
                            </div>
                            <div class="member-list-meta">
                                <span><?php echo date('Y-m-d', $fav['dateline']); ?></span>
                                <span><?php echo $thread ? $thread['reply_num'] : 0; ?> 回复</span>
                            </div>
                        </div>
                        <div class="item-arrow"></div>
                    </a>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state member-empty">
                    <p>暂无收藏</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($template_pages > 1): ?>
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=member&a=profile&uid=' . $template_member['uid'] . '&type=' . $template_type); ?>
        <?php endif; ?>
    </div>
</div>
