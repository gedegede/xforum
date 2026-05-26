<div class="grid grid-cols-main-3 gap-4 md:grid-cols-1">
    <!-- Main Content -->
    <div class="min-w-0 flex flex-col gap-4">
        <!-- Thread Detail Card -->
        <div class="bg-panel border border-border rounded shadow-sm">
            <!-- Card Header -->
            <div class="flex flex-col items-stretch gap-3 px-4 py-3.5 border-b border-border">
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
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-soft text-sub"><?php echo (int)$template_thread['reply_num']; ?> 回复</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-soft text-sub"><?php echo (int)$template_thread['view_num']; ?> 浏览</span>
                            <?php if (isset($template_user)): ?>
                                <button type="button" id="fav-btn-mobile" class="inline-flex items-center border-0 gap-1 px-2 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-colors bg-soft text-sub hover:bg-hover" data-tid="<?php echo $template_thread['tid']; ?>" data-favorited="<?php echo $template_isFavorited ? '1' : '0'; ?>">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="<?php echo $template_isFavorited ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    <span id="fav-text-mobile"><?php echo $template_isFavorited ? '已收藏' : '收藏'; ?></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post List -->
            <div class="flex flex-col">
                <?php foreach ($template_posts as $index => $post): ?>
                    <?php $floor = (int)($post['_floor'] ?? (($template_page - 1) * 20 + $index + 1)); ?>
                    <?php $isAuthor = $post['uid'] == $template_thread['uid']; ?>
                    <?php echo Lib\PostHelper::renderPost($post, $template_users, $floor, $isAuthor, $template_user ?? null, $template_isModerator ?? false, $template_ratedPids ?? []); ?>
                <?php endforeach; ?>
            </div>

            <!-- Reply Section Anchor -->
            <div id="reply-section"></div>

            <!-- Pagination -->
            <?php if ($template_pages > 1): ?>
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=thread&a=index&tid=' . $template_thread['tid']); ?>
            <?php endif; ?>

            <!-- Reply Form -->
            <div class="border-t">
                <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
                    <h3 id="reply-title">回复</h3>
                </div>
                <div class="p-4">
                    <?php if (isset($template_user)): ?>
                        <form id="reply-form" action="index.php?c=thread&a=reply&tid=<?php echo $template_thread['tid']; ?>" method="post">
                            <input type="hidden" name="quote_pid" id="quote-pid" value="">
                            <input type="hidden" name="quote_uid" id="quote-uid" value="">

                            <textarea name="message" id="reply-message" class="w-full h-auto min-h-30 p-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary resize-y mb-3" placeholder="支持 Markdown 语法，欢迎补充细节、经验与上下文..."></textarea>

                            <div class="flex justify-end gap-3">
                                <button type="button" id="cancel-quote" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover hidden">取消引用</button>
                                <button type="submit" id="reply-submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">发表回复</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-muted mb-4">请先登录后回复</p>
                            <a href="index.php?c=auth&a=login" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">立即登录</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <?php if (!empty($template_hotThreads)): ?>
    <aside class="md:hidden flex flex-col gap-4">
        <div class="bg-panel border border-border rounded shadow-sm">
            <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
                <h3 class="flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                    本版热帖
                </h3>
            </div>
            <div class="p-0">
                <div class="flex flex-col">
                    <?php foreach ($template_hotThreads as $index => $thread): ?>
                        <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="flex items-center gap-3 p-2 hover:bg-hover transition-colors">
                            <span class="w-5 h-5 flex items-center justify-center rounded-sm text-xs font-semibold flex-shrink-0 <?php echo $index < 3 ? 'bg-primary-light text-primary' : 'bg-soft text-muted'; ?>"><?php echo $index + 1; ?></span>
                            <div class="flex-1 min-w-0 flex flex-col gap-0.5">
                                <span class="text-sm text-text truncate"><?php echo htmlspecialchars($thread['subject']); ?></span>
                                <span class="text-xs text-muted"><?php echo $thread['reply_num']; ?> 回复</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </aside>
    <?php endif; ?>
</div>

<!-- Scroll to target post -->
<?php if (!empty($template_targetPid)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const targetPost = document.getElementById('post-<?php echo (int)$template_targetPid; ?>');
        if (!targetPost) return;

        requestAnimationFrame(function() {
            targetPost.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            targetPost.classList.add('animate-highlight');
            setTimeout(function() {
                targetPost.classList.remove('animate-highlight');
            }, 2600);
        });
    });
</script>
<?php endif; ?>

<!-- Reply and Favorite JS -->
<?php if (isset($template_user)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete post handler
        document.addEventListener('click', function(e) {
            const rateBtn = e.target.closest('[data-action="rate"]');
            if (rateBtn) {
                e.preventDefault();
                const pid = rateBtn.dataset.pid;

                fetch('index.php?c=thread&a=rate&pid=' + pid, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert(data.message || '操作失败，请重试');
                            return;
                        }

                        rateBtn.dataset.rated = data.rated ? '1' : '0';
                        rateBtn.classList.toggle('text-primary', data.rated);
                        const rateNum = Number(data.rate_num || 0);
                        rateBtn.title = (data.rated ? '取消点赞' : '点赞') + (rateNum > 0 ? ' (' + rateNum + ')' : '');
                        const rateCount = rateBtn.closest('[data-rate-group]')?.querySelector('[data-role="rate-count"]');
                        if (rateCount) {
                            rateCount.textContent = rateNum > 0 ? String(rateNum) : '';
                            rateCount.classList.toggle('hidden', rateNum <= 0);
                        }

                        const svg = rateBtn.querySelector('svg');
                        if (svg) {
                            svg.setAttribute('fill', data.rated ? 'currentColor' : 'none');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        window.location.href = 'index.php?c=auth&a=login';
                    });
                return;
            }

            const deleteBtn = e.target.closest('[data-action="delete-post"]');
            if (deleteBtn) {
                const pid = deleteBtn.dataset.pid;
                if (confirm('确定要删除这条回复吗？')) {
                    fetch('index.php?c=thread&a=deletePost&pid=' + pid, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const postEl = document.getElementById('post-' + pid);
                                if (postEl) {
                                    postEl.remove();
                                }
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                } else {
                                    updateReplyCount(-1);
                                }
                            } else {
                                alert(data.message || '删除失败，请重试');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('网络错误，请重试');
                        });
                }
            }
        });

        // Quote handler
        document.addEventListener('click', function(e) {
            const quoteBtn = e.target.closest('[data-action="quote"]');
            if (quoteBtn) {
                e.preventDefault();
                const pid = quoteBtn.dataset.pid;
                const uid = quoteBtn.dataset.uid;
                const floor = quoteBtn.dataset.floor;
                const username = quoteBtn.dataset.username;
                setQuoteReply(pid, uid, floor, username);
            }
        });

        const form = document.getElementById('reply-form');
        const messageInput = document.getElementById('reply-message');
        const submitBtn = document.getElementById('reply-submit');
        const cancelQuoteBtn = document.getElementById('cancel-quote');
        const quotePidInput = document.getElementById('quote-pid');
        const quoteUidInput = document.getElementById('quote-uid');
        const replyTitle = document.getElementById('reply-title');
        const originalPlaceholder = messageInput ? messageInput.placeholder : '';

        if (!form) return;

        if (cancelQuoteBtn) {
            cancelQuoteBtn.addEventListener('click', function() {
                clearQuoteReply();
            });
        }

        function setQuoteReply(pid, uid, floor, username) {
            if (!quotePidInput || !messageInput) return;
            quotePidInput.value = pid;
            quoteUidInput.value = uid;
            if (replyTitle) replyTitle.textContent = '引用回复 #' + floor;
            if (cancelQuoteBtn) cancelQuoteBtn.classList.remove('hidden');
            messageInput.placeholder = '回复 @' + username + '...';
            messageInput.focus();
            messageInput.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        function clearQuoteReply() {
            if (!quotePidInput || !quoteUidInput) return;
            quotePidInput.value = '';
            quoteUidInput.value = '';
            if (replyTitle) replyTitle.textContent = '回复';
            if (cancelQuoteBtn) cancelQuoteBtn.classList.add('hidden');
            if (messageInput) messageInput.placeholder = originalPlaceholder;
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const message = messageInput.value.trim();
            if (!message) {
                alert('请填写回复内容');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = '提交中...';

            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        addReplyToPage(data.html, data.postIndex);
                        messageInput.value = '';
                        clearQuoteReply();
                        updateReplyCount();
                        if (data.credit_change && typeof window.showCreditToast === 'function') {
                            window.showCreditToast(data.credit_change);
                        }
                    } else {
                        alert(data.message || '回复失败，请重试');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('网络错误，请重试');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '发表回复';
                });
        });

        function addReplyToPage(html, postIndex) {
            const replySection = document.getElementById('reply-section');
            replySection.insertAdjacentHTML('beforebegin', html);

            const existingPosts = document.querySelectorAll('[data-entry="post"]');
            const newPost = existingPosts[existingPosts.length - 1];
            if (newPost) {
                newPost.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                newPost.classList.add('animate-highlight');
                newPost.classList.remove('border-b');
                newPost.classList.add('border-t');
                setTimeout(() => {
                    newPost.classList.remove('animate-highlight');
                }, 2600);
            }
        }

        function updateReplyCount(delta) {
            const replyNumElement = document.getElementById('reply-count');
            if (replyNumElement) {
                const currentCount = parseInt(replyNumElement.textContent) || 0;
                replyNumElement.textContent = Math.max(0, currentCount + (delta || 1));
            }
        }

        function handleFavoriteClick(favBtn, favTextId) {
            const tid = favBtn.dataset.tid;
            const isFavorited = favBtn.dataset.favorited === '1';
            const favText = document.getElementById(favTextId);

            fetch('index.php?c=thread&a=favorite&tid=' + tid, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const mobileBtn = document.getElementById('fav-btn-mobile');
                        const desktopBtn = document.getElementById('fav-btn');
                        const mobileText = document.getElementById('fav-text-mobile');
                        const desktopText = document.getElementById('fav-text');

                        if (isFavorited) {
                            // Unfavorite
                            [favBtn, mobileBtn, desktopBtn].forEach(btn => {
                                if (!btn) return;
                                btn.classList.remove('border-border', 'text-text');
                                btn.classList.add('bg-soft', 'text-sub', 'hover:bg-hover');
                                const svg = btn.querySelector('svg');
                                if (svg) svg.setAttribute('fill', 'none');
                            });
                            [favText, mobileText, desktopText].forEach(text => {
                                if (text) text.textContent = text === mobileText ? '收藏' : '收藏主题';
                            });
                            [favBtn, mobileBtn, desktopBtn].forEach(btn => {
                                if (btn) btn.dataset.favorited = '0';
                            });
                        } else {
                            // Favorite
                            [favBtn, mobileBtn, desktopBtn].forEach(btn => {
                                if (!btn) return;
                                btn.classList.remove('border-border', 'text-text');
                                btn.classList.add('bg-soft', 'text-sub', 'hover:bg-hover');
                                const svg = btn.querySelector('svg');
                                if (svg) svg.setAttribute('fill', 'currentColor');
                            });
                            [favText, mobileText, desktopText].forEach(text => {
                                if (text) text.textContent = '已收藏';
                            });
                            [favBtn, mobileBtn, desktopBtn].forEach(btn => {
                                if (btn) btn.dataset.favorited = '1';
                            });
                        }
                    } else {
                        alert(data.message || '操作失败，请重试');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = 'index.php?c=auth&a=login';
                });
        }

        const favBtn = document.getElementById('fav-btn');
        if (favBtn) {
            favBtn.addEventListener('click', function() {
                handleFavoriteClick(this, 'fav-text');
            });
        }

        const favBtnMobile = document.getElementById('fav-btn-mobile');
        if (favBtnMobile) {
            favBtnMobile.addEventListener('click', function() {
                handleFavoriteClick(this, 'fav-text-mobile');
            });
        }
    });
</script>
<?php endif; ?>
