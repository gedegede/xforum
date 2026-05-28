<style>
.metric-grid{display:flex;flex-wrap:wrap;gap:var(--space-2)}
.metric-card{display:flex;flex:1;flex-direction:column;align-items:center;gap:var(--space-1);padding:var(--space-3);border-radius:var(--radius);background:var(--soft);text-align:center;transition:background-color .15s ease,color .15s ease}
a.metric-card:hover{background:var(--hover)}
.metric-value{font-size:16px;font-weight:700}
.metric-label{color:var(--muted);font-size:12px}
.metric-label-sm{font-size:11px}
</style>
<div class="page-grid">
    <!-- Main Content -->
    <div class="main-stack">
        <?php if ($template_canManage && !empty($template_modStats)): ?>
        <div class="card">
            <div class="card-header">
                <h3>管理提示</h3>
            </div>
            <div class="card-body">
                <div class="metric-grid">
                    <a href="index.php?c=admin&a=audits&filter=thread" class="metric-card">
                        <span class="metric-value"><?php echo $template_modStats['pending_threads'] ?? 0; ?></span>
                        <span class="metric-label">待审核主题</span>
                    </a>
                    <a href="index.php?c=admin&a=audits&filter=post" class="metric-card">
                        <span class="metric-value"><?php echo $template_modStats['pending_posts'] ?? 0; ?></span>
                        <span class="metric-label">待审核回帖</span>
                    </a>
                    <a href="index.php?c=admin&a=audits&filter=report" class="metric-card">
                        <span class="metric-value"><?php echo $template_modStats['pending_reports'] ?? 0; ?></span>
                        <span class="metric-label">待处理举报</span>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Thread List Card -->
        <div class="card">
            <!-- Card Header -->
            <div class="card-header-col">
                <div class="flex items-center justify-between gap-4 w-full">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl font-bold"><?php echo htmlspecialchars(\Models\SettingModel::get('site_name', 'XForum')); ?></h1>
                        <?php $siteDesc = \Models\SettingModel::get('site_desc'); ?>
                        <?php if (!empty($siteDesc)): ?>
                        <p class="text-sm text-muted mt-1"><?php echo htmlspecialchars($siteDesc); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col gap-2">
                        <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
                            <a href="index.php?c=forum&a=index&from=create" class="btn btn-block btn-primary">发布主题</a>
                        <?php else: ?>
                            <a href="index.php?c=auth&a=login" class="btn btn-block btn-soft">登录</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="toolbar">
                <div class="flex-1 min-w-0 flex flex-col gap-2">
                    <div class="flex items-center gap-1 hide-mobile">
                        <?php foreach ($template_orderOptions as $option): ?>
                            <a href="index.php?order=<?php echo $option['value']; ?><?php echo !empty($template_keyword) ? '&keyword=' . urlencode($template_keyword) : ''; ?>"
                                class="filter-link <?php echo $template_order == $option['value'] ? 'active' : ''; ?>">
                                <?php echo $option['label']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <form action="index.php" method="get" class="flex items-center gap-2 hide-mobile">
                    <input type="hidden" name="order" value="<?php echo $template_order; ?>">
                    <input type="text" name="keyword" class="form-control" placeholder="搜索标题、关键讨论..." value="<?php echo htmlspecialchars($template_keyword ?? ''); ?>">
                    <button type="submit" class="btn">搜索</button>
                </form>
                <form action="index.php" method="get" class="flex gap-2 mobile-only">
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

            <!-- Thread List -->
            <?php if ($template_threads): ?>
                <div class="list-stack">
                    <?php foreach ($template_threads as $thread): ?>
                        <?php echo \Lib\ThreadHelper::renderThread($thread, $template_users, $template_forums, ['show_forum' => true]); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
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
        <div class="card card-clip">
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
                    <div class="list-stack">
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
    <aside class="sidebar-stack">
        <!-- User Card -->
        <div class="card">
            <div class="card-body">
                <?php if (isset($template_user) && is_array($template_user) && !empty($template_user)): ?>
                    <div class="flex items-center gap-3 mb-4">
                        <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>">
                            <div class="avatar avatar-lg">
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
                                    <span id="user-group-title"><?php echo htmlspecialchars($template_userGroup['title'] ?? ''); ?></span>
                                    <span> · </span>
                                <?php endif; ?>
                                <span>金币：<span id="user-credit"><?php echo (int)($template_userStats['credit'] ?? 0); ?></span></span>
                            </div>
                        </div>
                    </div>

                    <div id="signin-container" class="mb-4">
                        <button type="button" id="signin-btn" onclick="doSignin()" class="btn btn-block btn-sm <?php echo !empty($template_userStats['signed_today']) ? 'btn-soft' : 'btn-primary'; ?>" <?php echo !empty($template_userStats['signed_today']) ? 'disabled' : ''; ?>>
                            <?php echo !empty($template_userStats['signed_today']) ? '今日已签到' : '每日签到'; ?>
                        </button>
                    </div>
                    <script>
                    function doSignin() {
                        var btn = document.getElementById('signin-btn');
                        if (!btn || btn.disabled) return;
                        btn.disabled = true;
                        btn.textContent = '签到中...';

                        fetch('index.php?c=member&a=signin', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                btn.textContent = '今日已签到';
                                btn.disabled = true;
                                btn.classList.remove('btn-primary');
                                btn.classList.add('btn-soft');
                                var creditEl = document.getElementById('user-credit');
                                if (creditEl && data.credit !== undefined) {
                                    creditEl.textContent = data.credit;
                                }
                                var groupEl = document.getElementById('user-group-title');
                                if (groupEl && data.group_title !== undefined) {
                                    groupEl.textContent = data.group_title;
                                }
                                if (data.got_credit > 0 && typeof window.showCreditToast === 'function') {
                                    window.showCreditToast(data.got_credit);
                                }
                            } else {
                                btn.disabled = false;
                                btn.textContent = '每日签到';
                                showMessageModal('提示', data.message || '签到失败');
                            }
                        })
                        .catch(function() {
                            btn.disabled = false;
                            btn.textContent = '每日签到';
                            showMessageModal('提示', '网络错误，请重试');
                        });
                    }
                    </script>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="index.php?c=member&a=settings" class="btn btn-soft btn-sm text-center">个人设置</a>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=favorites" class="btn btn-soft btn-sm text-center">我的收藏</a>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=threads" class="btn btn-soft btn-sm text-center">我的话题</a>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $template_user['uid']; ?>&type=credits" class="btn btn-soft btn-sm text-center">金币明细</a>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="avatar avatar-lg text-primary">G</div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold">访客</div>
                            <div class="text-sm text-muted">登录后可同步主题、收藏、通知与私信记录</div>
                        </div>
                    </div>
                    <div class="metric-grid mb-4">
                        <div class="metric-card">
                            <span class="metric-value"><?php echo count($template_noticeThreads); ?></span>
                            <span class="metric-label metric-label-sm">公告</span>
                        </div>
                        <div class="metric-card">
                            <span class="metric-value"><?php echo count($template_hotForums); ?></span>
                            <span class="metric-label metric-label-sm">版块</span>
                        </div>
                        <div class="metric-card">
                            <span class="metric-value"><?php echo (int)$template_onlineCount; ?></span>
                            <span class="metric-label metric-label-sm">在线</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="index.php?c=auth&a=login" class="btn btn-primary btn-sm text-center">登录</a>
                        <a href="index.php?c=auth&a=register" class="btn btn-soft btn-sm text-center">注册</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notice -->
        <div class="card">
            <div class="card-header">
                <h3>社区公告</h3>
            </div>
            <div class="card-body-flush">
                <?php if (!empty($template_noticeThreads)): ?>
                    <div class="list-stack">
                        <?php foreach ($template_noticeThreads as $thread): ?>
                            <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="list-link list-link-sm list-link-pad">
                                <span class="flex-1 min-w-0 font-medium truncate text-text text-sm"><?php echo htmlspecialchars($thread['subject']); ?></span>
                                <span class="text-xs text-muted flex-shrink-0 ml-2"><?php echo \Lib\Helper::formatTime((int)$thread['dateline']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state empty-state-sm">
                        <p>暂无公告</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hot Forums -->
        <div class="card">
            <div class="card-header">
                <h3>热门版块</h3>
            </div>
            <div class="card-body-flush">
                <?php if (!empty($template_hotForums)): ?>
                    <div class="list-stack">
                        <?php foreach ($template_hotForums as $index => $forum): ?>
                            <a href="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>" class="list-link list-link-sm">
                                <span class="rank-badge <?php echo $index < 3 ? 'success' : ''; ?>"><?php echo $index + 1; ?></span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-sm"><?php echo htmlspecialchars($forum['name']); ?></div>
                                    <div class="text-xs text-muted">
                                        <?php echo (int)$forum['thread_num']; ?> 主题<?php if ((int)$forum['reply_num'] > 0): ?> · <?php echo (int)$forum['reply_num']; ?> 回复<?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-center flex-shrink-0 ml-2">
                                    <div class="font-semibold text-sm"><?php echo (int)$forum['today_num']; ?></div>
                                    <div class="text-xs text-muted">今日</div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state empty-state-sm">
                        <p>暂无数据</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Online Count -->
        <div class="card">
            <div class="card-header">
                <h3>当前在线</h3>
            </div>
            <div class="card-body-flush">
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
