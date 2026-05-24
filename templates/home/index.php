<div class="grid grid-cols-3">
    <div class="min-width-0">
        <?php if ($template_canManage && !empty($template_modStats)): ?>
            <div class="card">
                <div class="section">
                    <h3>管理提示</h3>
                </div>
                <div class="panel">
                    <div class="grid grid-auto gap-sm">
                        <a href="index.php?c=admin&a=threads&status=pending" class="box">
                            <span class="font-bold"><?php echo $template_modStats['pending_threads'] ?? 0; ?></span>
                            <span class="muted">待审核主题</span>
                        </a>
                        <a href="index.php?c=admin&a=threads&status=pending_posts" class="box">
                            <span class="font-bold"><?php echo $template_modStats['pending_posts'] ?? 0; ?></span>
                            <span class="muted">待审核回帖</span>
                        </a>
                        <a href="index.php?c=admin&a=threads&fid=<?php echo (int)($template_settings['report_forum_fid'] ?? 0); ?>" class="box">
                            <span class="font-bold"><?php echo $template_modStats['pending_reports'] ?? 0; ?></span>
                            <span class="muted">待处理举报</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="card">
            <div class="section">
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
                    <div class="row" style="flex-direction:column;align-items:stretch;min-width:168px;">
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
                    <button type="submit" class="btn">搜索</button>
                </form>
                <form action="index.php" method="get" class="mobile-toolbar">
                    <select name="order" onchange="this.form.submit()">
                        <?php foreach ($template_orderOptions as $option): ?>
                            <option value="<?php echo $option['value']; ?>" <?php echo $template_order == $option['value'] ? 'selected' : ''; ?>>
                                <?php echo $option['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="keyword" placeholder="搜索..." value="<?php echo htmlspecialchars($template_keyword ?? ''); ?>">
                    <button type="submit" class="btn">搜索</button>
                </form>
            </div>

            <div class="stack">
                <?php foreach ($template_threads as $thread): ?>
                    <?php $author = $template_users[$thread['uid']] ?? null; ?>
                    <?php $forum = $template_forums[$thread['fid']] ?? null; ?>
                    <div class="list-item">
                        <div>
                            <div class="avatar">
                                <?php if ($author && !empty($author['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($author['avatar']); ?>" alt="">
                                <?php else: ?>
                                    <?php echo \Lib\Helper::getAvatarInitial($author['username'] ?? '?'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1 min-width-0">
                            <div class="font-bold">
                                <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>"><?php echo htmlspecialchars($thread['subject']); ?></a>
                                <?php if ($forum): ?>
                                    <span class="badge badge-green"><?php echo htmlspecialchars($forum['name']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="muted">
                                <span><?php echo htmlspecialchars($author['username'] ?? '匿名'); ?></span>
                                <span class="separator">•</span>
                                <span>发布于 <?php echo date('Y-m-d H:i', $thread['dateline']); ?></span>
                                <?php if (!empty($thread['reply_time']) && (int)$thread['reply_time'] !== (int)$thread['dateline']): ?>
                                    <span class="separator">•</span>
                                    <span>最后活跃 <?php echo date('Y-m-d H:i', (int)$thread['reply_time']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="grid grid-auto gap-sm">
                            <div class="box">
                                <span class="font-bold"><?php echo $thread['view_num']; ?></span>
                                <span class="muted">浏览</span>
                            </div>
                            <div class="box">
                                <span class="font-bold"><?php echo $thread['reply_num']; ?></span>
                                <span class="muted">回复</span>
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
                            <path d="M9 18l6-6-6-6" />
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
                        <div class="list-item">
                            <div>
                                <div class="avatar">
                                    <?php if ($author && !empty($author['avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($author['avatar']); ?>" alt="">
                                    <?php else: ?>
                                        <?php echo \Lib\Helper::getAvatarInitial($author['username'] ?? '?'); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex-1 min-width-0">
                                <div class="font-bold">
                                    <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>"><?php echo htmlspecialchars($thread['subject']); ?></a>
                                    <?php if ($forum): ?>
                                        <span class="badge badge-gray"><?php echo htmlspecialchars($forum['name']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="muted">
                                    <span><?php echo htmlspecialchars($author['username'] ?? '匿名'); ?></span>
                                    <span class="separator">•</span>
                                    <span><?php echo date('Y-m-d H:i', $thread['dateline']); ?></span>
                                </div>
                            </div>
                            <div class="grid grid-auto gap-sm">
                                <div class="box">
                                    <span class="font-bold"><?php echo $thread['view_num']; ?></span>
                                    <span class="muted">浏览</span>
                                </div>
                                <div class="box">
                                    <span class="font-bold"><?php echo $thread['reply_num']; ?></span>
                                    <span class="muted">回复</span>
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

    <div>
        <div class="card">
            <div class="panel">
                <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
                    <div class="row">
                        <div>
                            <div class="avatar">
                                <?php if (!empty($template_user['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($template_user['avatar']); ?>" alt="">
                                <?php else: ?>
                                    <?php echo \Lib\Helper::getAvatarInitial($template_user['username']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1 min-width-0">
                            <div class="font-bold"><?php echo htmlspecialchars($template_user['username']); ?></div>
                            <div class="muted">已登录，可参与发帖、回复、收藏与站内消息同步</div>
                        </div>
                    </div>
                    <div class="grid grid-auto gap-sm">
                        <div class="box">
                            <div class="font-bold"><?php echo (int)($template_userStats['thread_count'] ?? 0); ?></div>
                            <div class="muted">主题</div>
                        </div>
                        <div class="box">
                            <div class="font-bold"><?php echo (int)($template_userStats['post_count'] ?? 0); ?></div>
                            <div class="muted">回复</div>
                        </div>
                        <div class="box">
                            <div class="font-bold"><?php echo (int)($template_userStats['credit'] ?? 0); ?></div>
                            <div class="muted">金币</div>
                        </div>
                    </div>
                    <div>
                        <form method="post" action="index.php?c=member&a=signin">
                            <button type="submit" class="btn <?php echo !empty($template_userStats['signed_today']) ? 'btn-secondary' : 'btn-primary'; ?> btn-sm w-full" <?php echo !empty($template_userStats['signed_today']) ? 'disabled' : ''; ?>>
                                <?php echo !empty($template_userStats['signed_today']) ? '今日已签到' : '每日签到'; ?>
                            </button>
                        </form>
                        <div class="grid grid-cols-2 gap-sm">
                            <a href="index.php?c=member&a=settings" class="btn btn-secondary btn-sm">个人设置</a>
                            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=favorites" class="btn btn-secondary btn-sm">我的收藏</a>
                            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=threads" class="btn btn-secondary btn-sm">我的话题</a>
                            <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=credits" class="btn btn-secondary btn-sm">金币明细</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div>
                            <div class="avatar">G</div>
                        </div>
                        <div class="flex-1 min-width-0">
                            <div class="font-bold">访客</div>
                            <div class="muted">登录后可同步主题、收藏、通知与私信记录</div>
                        </div>
                    </div>
                    <div class="grid grid-auto gap-sm">
                        <div class="box">
                            <div class="font-bold"><?php echo count($template_noticeThreads); ?></div>
                            <div class="muted">公告</div>
                        </div>
                        <div class="box">
                            <div class="font-bold"><?php echo count($template_hotForums); ?></div>
                            <div class="muted">版块</div>
                        </div>
                        <div class="box">
                            <div class="font-bold"><?php echo (int)$template_onlineCount; ?></div>
                            <div class="muted">在线</div>
                        </div>
                    </div>
                    <div>
                        <div class="grid grid-cols-2 gap-sm">
                            <a href="index.php?c=auth&a=login" class="btn btn-primary btn-sm">登录</a>
                            <a href="index.php?c=auth&a=register" class="btn btn-secondary btn-sm">注册</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>社区公告</h3>
            </div>
            <div class="panel">
                <?php if (!empty($template_noticeThreads)): ?>
                    <div>
                        <?php foreach ($template_noticeThreads as $thread): ?>
                            <div class="list-item">
                                <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="flex-1 min-width-0 text-primary">
                                    <?php echo htmlspecialchars($thread['subject']); ?>
                                </a>
                                <div class="muted"><?php echo date('Y-m-d', $thread['dateline']); ?></div>
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
            <div class="card-header">
                <h3>热门版块</h3>
            </div>
            <div class="panel">
                <?php if (!empty($template_hotForums)): ?>
                    <div>
                        <?php foreach ($template_hotForums as $index => $forum): ?>
                            <a href="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>" class="list-item">
                                <span class="badge <?php echo $index < 3 ? 'badge-green' : 'badge-gray'; ?>"><?php echo $index + 1; ?></span>
                                <span class="flex-1 min-width-0">
                                    <span class="font-bold"><?php echo htmlspecialchars($forum['name']); ?></span>
                                    <span class="muted"><?php echo (int)$forum['thread_num']; ?> 主题 · <?php echo (int)$forum['reply_num']; ?> 回复</span>
                                </span>
                                <div class="box text-center">
                                    <strong class="font-bold"><?php echo (int)$forum['today_num']; ?></strong>
                                    <span class="muted">今日</span>
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
            <div class="card-header">
                <h3>当前在线</h3>
            </div>
            <div class="panel">
                <a href="index.php?c=member&a=online" class="box block text-center">
                    <span class="font-bold"><?php echo $template_onlineCount; ?></span>
                    <span class="muted">人在线</span>
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