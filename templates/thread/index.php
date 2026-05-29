<link rel="stylesheet" href="assets/css/thread.css">

<div class="page-grid">
    <!-- Main Content -->
    <div class="main-stack">
        <!-- Thread Detail Card -->
        <div class="card">
            <!-- Card Header -->
            <div class="card-header-col">
                <div class="flex flex-wrap items-center gap-2 text-sm text-muted">
                    <a href="index.php" class="hover:text-primary">首页</a>
                    <span>/</span>
                    <a href="index.php?c=forum&a=index&fid=<?php echo $template_thread['fid']; ?>" class="hover:text-primary"><?php echo htmlspecialchars($template_forum['name'] ?? '未命名版块'); ?></a>
                    <span>/</span>
                    <span>主题</span>
                </div>
                <div class="flex items-start justify-between gap-4 w-full">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($template_thread['subject']); ?></h1>
                        <div class="flex flex-wrap gap-2 items-center">
                            <?php if ((int)$template_thread['reply_num'] > 0): ?>
                                <span class="badge badge-soft"><?php echo (int)$template_thread['reply_num']; ?> 回复</span>
                            <?php endif; ?>
                            <?php if ((int)$template_thread['view_num'] > 0): ?>
                                <span class="badge badge-soft"><?php echo (int)$template_thread['view_num']; ?> 浏览</span>
                            <?php endif; ?>
                            <?php if (isset($template_user) && \Lib\Permission::canFavorite()): ?>
                                <button type="button" id="fav-btn-mobile" class="badge badge-soft border-0 cursor-pointer" data-tid="<?php echo $template_thread['tid']; ?>" data-favorited="<?php echo $template_isFavorited ? '1' : '0'; ?>">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="<?php echo $template_isFavorited ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    <span id="fav-text-mobile"><?php echo $template_isFavorited ? '已收藏' : '收藏'; ?></span>
                                </button>
                            <?php endif; ?>
                            <?php if (\Lib\Permission::canDeleteThread($template_thread)): ?>
                                <button type="button" class="badge badge-soft border-0 cursor-pointer thread-delete-btn" data-action="delete-post" data-pid="<?php echo (int)($template_posts[0]['pid'] ?? 0); ?>" title="删除主题" aria-label="删除主题">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18"></path>
                                        <path d="M8 6V4h8v2"></path>
                                        <path d="M6 6l1 14h10l1-14"></path>
                                        <path d="M10 11v5"></path>
                                        <path d="M14 11v5"></path>
                                    </svg>
                                    <span>删除</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post List -->
            <div class="list-stack">
                <?php foreach ($template_posts as $index => $post): ?>
                    <?php $floor = (int)($post['_floor'] ?? (($template_page - 1) * 20 + $index + 1)); ?>
                    <?php $isAuthor = $post['uid'] == $template_thread['uid']; ?>
                    <?php echo Lib\PostHelper::renderPost($post, $template_users, $floor, $isAuthor, $template_user ?? null, $template_isModerator ?? false, $template_ratedPids ?? [], (int)$template_page); ?>
                <?php endforeach; ?>

                <!-- Reply Section Anchor -->
                <div id="reply-section"></div>
            </div>



            <!-- Pagination -->
            <?php if ($template_pages > 1): ?>
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=thread&a=index&tid=' . $template_thread['tid']); ?>
            <?php endif; ?>
        </div>
        <div class="card">
            <!-- Reply Form -->
            <div class="card-header">
                <h3 id="reply-title">回复</h3>
            </div>
            <div class="card-body">
                <?php if (isset($template_user) && \Lib\Permission::canReplyThread((int)$template_thread['fid'])): ?>
                    <form id="reply-form" action="index.php?c=thread&a=reply&tid=<?php echo $template_thread['tid']; ?>" method="post">
                        <input type="hidden" name="quote_pid" id="quote-pid" value="">
                        <input type="hidden" name="quote_uid" id="quote-uid" value="">

                        <textarea name="message" id="reply-message" class="form-control min-h-30 mb-3" placeholder="我来说两句"></textarea>

                        <div class="form-actions">
                            <button type="button" id="cancel-quote" class="btn btn-soft hidden">取消引用</button>
                            <button type="submit" id="reply-submit" class="btn btn-primary">发表回复</button>
                        </div>
                    </form>
                <?php elseif (!isset($template_user)): ?>
                    <div class="text-center py-8">
                        <p class="text-muted mb-4">请先登录后回复</p>
                        <a href="index.php?c=auth&a=login" class="btn btn-primary">立即登录</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <?php if (!empty($template_hotThreads)): ?>
    <aside class="sidebar-stack">
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
    </aside>
    <?php endif; ?>
</div>

<div id="credit-post-modal" class="modal hidden">
    <div class="modal-panel">
        <div class="modal-header">
            <h3 class="font-semibold">帖子评分</h3>
            <button type="button" class="modal-close" data-action="close-credit-post">&times;</button>
        </div>
        <div class="modal-body">
            <div class="credit-modal-grid">
                <input type="hidden" id="credit-post-pid">
                <div class="form-field">
                    <label for="credit-post-amount" class="form-label">金币数量</label>
                    <input type="number" id="credit-post-amount" class="form-control" step="1" placeholder="正数增加，负数减少">
                </div>
                <div class="form-field">
                    <label for="credit-post-reason" class="form-label">评分理由</label>
                    <textarea id="credit-post-reason" class="form-control" rows="4" placeholder="请输入评分理由"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-soft" data-action="close-credit-post">取消</button>
            <button type="button" class="btn btn-primary" id="credit-post-submit">提交评分</button>
        </div>
    </div>
</div>

<div id="report-modal" class="modal hidden">
    <div class="modal-panel">
        <div class="modal-header">
            <h3 class="font-semibold">举报回帖</h3>
            <button type="button" class="modal-close" data-action="close-report">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="report-pid">
            <textarea id="report-reason" class="form-control" rows="5" placeholder="请输入举报理由"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-soft" data-action="close-report">取消</button>
            <button type="button" class="btn btn-primary" id="report-submit">提交举报</button>
        </div>
    </div>
</div>

<div id="thread-page-data" data-target-pid="<?php echo (int)($template_targetPid ?? 0); ?>" data-credit-change="<?php echo (int)($template_creditChange ?? 0); ?>" hidden></div>
<script src="assets/js/thread.js"></script>
