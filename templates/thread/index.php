<div class="grid grid-cols-3">
    <div class="main-content">
        <div class="card">
            <div class="thread-hero">
                <div class="breadcrumb">
                    <a href="index.php">首页</a>
                    <span>/</span>
                    <a href="index.php?c=forum&a=index&fid=<?php echo $template_thread['fid']; ?>"><?php echo htmlspecialchars($template_forum['name'] ?? '未命名版块'); ?></a>
                    <span>/</span>
                    <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>">主题</a>
                </div>
                <div class="thread-title-row">
                    <div class="min-width-0">
                        <div class="flex flex-wrap gap-sm mb-sm">
                            <span class="badge badge-green"><?php echo htmlspecialchars($template_forum['name'] ?? '未命名版块'); ?></span>
                            <span class="badge badge-gray"><?php echo (int)$template_thread['reply_num']; ?> 条回复</span>
                            <span class="badge badge-gray"><?php echo (int)$template_thread['view_num']; ?> 次浏览</span>
                        </div>
                        <h1><?php echo htmlspecialchars($template_thread['subject']); ?></h1>
                        <div class="thread-meta mt-sm">
                            <span>发布于 <?php echo date('Y-m-d H:i', $template_thread['dateline']); ?></span>
                            <?php if (!empty($template_thread['reply_time']) && (int)$template_thread['reply_time'] !== (int)$template_thread['dateline']): ?>
                                <span class="separator">•</span>
                                <span>最后活跃 <?php echo date('Y-m-d H:i', (int)$template_thread['reply_time']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (isset($template_user)): ?>
                        <button type="button" id="fav-btn-mobile" class="btn fav-btn-mobile <?php echo $template_isFavorited ? 'btn-secondary' : 'btn-primary'; ?>" data-tid="<?php echo $template_thread['tid']; ?>" data-favorited="<?php echo $template_isFavorited ? '1' : '0'; ?>">
                            <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $template_isFavorited ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <span id="fav-text-mobile"><?php echo $template_isFavorited ? '已收藏' : '收藏'; ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="post-list">
                <?php foreach ($template_posts as $index => $post): ?>
                    <?php $floor = ($template_page - 1) * 20 + $index + 1; ?>
                    <?php $isAuthor = $post['uid'] == $template_thread['uid']; ?>
                    <?php echo Lib\PostHelper::renderPost($post, $template_users, $floor, $isAuthor, $template_user ?? null, $template_isModerator ?? false); ?>
                <?php endforeach; ?>
            </div>

            <div class="reply-section"></div>

            <?php if ($template_pages > 1): ?>
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=thread&a=index&tid=' . $template_thread['tid']); ?>
            <?php endif; ?>

            <div class="reply-box">
                <div class="reply-header">
                    <h3 id="reply-title">回复</h3>
                </div>
                <div class="reply-body">
                <?php if (isset($template_user)): ?>
                    <form id="reply-form" action="index.php?c=thread&a=reply&tid=<?php echo $template_thread['tid']; ?>" method="post">
                        <input type="hidden" name="quote_pid" id="quote-pid" value="">
                        <input type="hidden" name="quote_uid" id="quote-uid" value="">

                        <textarea name="message" id="reply-message" class="reply-textarea" placeholder="支持 Markdown 语法，欢迎补充细节、经验与上下文..."></textarea>

                        <div class="flex justify-end gap-md mt-sm">
                            <button type="button" id="cancel-quote" class="btn btn-secondary hide">取消引用</button>
                            <button type="submit" id="reply-submit" class="btn btn-primary">发表回复</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="text-center py-lg text-secondary">
                        <p>请先登录后回复</p>
                        <a href="index.php?c=auth&a=login" class="btn btn-primary mt-sm">立即登录</a>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar">
        <div class="card">
            <div class="sidebar-header">
                <h3>主题信息</h3>
            </div>
            <div class="sidebar-body">
                <?php if (isset($template_user)): ?>
                    <button type="button" id="fav-btn" class="btn <?php echo $template_isFavorited ? 'btn-secondary' : 'btn-primary'; ?> w-full mb-md" data-tid="<?php echo $template_thread['tid']; ?>" data-favorited="<?php echo $template_isFavorited ? '1' : '0'; ?>">
                        <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $template_isFavorited ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span id="fav-text"><?php echo $template_isFavorited ? '已收藏' : '收藏主题'; ?></span>
                    </button>
                <?php else: ?>
                    <a href="index.php?c=auth&a=login" class="btn btn-primary w-full mb-md">
                        <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span>登录后收藏</span>
                    </a>
                <?php endif; ?>
                <div class="user-stats">
                    <div class="user-stat">
                        <div class="user-stat-value"><?php echo $template_thread['view_num']; ?></div>
                        <div class="user-stat-label">浏览</div>
                    </div>
                    <div class="user-stat">
                        <div class="user-stat-value" id="reply-count"><?php echo $template_thread['reply_num']; ?></div>
                        <div class="user-stat-label">回复</div>
                    </div>
                    <div class="user-stat">
                        <div class="user-stat-value" id="fav-count"><?php echo $template_thread['fav_num'] ?? 0; ?></div>
                        <div class="user-stat-label">收藏</div>
                    </div>
                </div>
                <div class="mt-lg text-secondary font-sm">
                    当前主题位于
                    <a href="index.php?c=forum&a=index&fid=<?php echo $template_thread['fid']; ?>"><?php echo htmlspecialchars($template_forum['name'] ?? '未命名版块'); ?></a>
                    ，适合继续补充经验、追问与后续结果。
                </div>
            </div>
        </div>

        <div class="card">
            <div class="sidebar-header">
                <h3>版块内热门主题</h3>
            </div>
            <div class="sidebar-body">
                <?php if (!empty($template_hotThreads)): ?>
                    <div class="related-topics">
                        <?php foreach ($template_hotThreads as $hotThread): ?>
                            <a href="index.php?c=thread&a=index&tid=<?php echo $hotThread['tid']; ?>" class="related-item">
                                <span class="related-title"><?php echo htmlspecialchars($hotThread['subject']); ?></span>
                                <span class="related-meta">
                                    <?php echo (int)$hotThread['reply_num']; ?> 回复 · <?php echo (int)$hotThread['view_num']; ?> 浏览
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>暂无热门主题</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($template_targetPid)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetPost = document.getElementById('post-<?php echo (int)$template_targetPid; ?>');
    if (!targetPost) return;

    requestAnimationFrame(function() {
        targetPost.scrollIntoView({ behavior: 'smooth', block: 'center' });
        targetPost.classList.add('post-target-highlight');
        setTimeout(function() {
            targetPost.classList.remove('post-target-highlight');
        }, 2600);
    });
});
</script>
<?php endif; ?>

<?php if (isset($template_user)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-btn');
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

    const form = document.getElementById('reply-form');
    const messageInput = document.getElementById('reply-message');
    const submitBtn = document.getElementById('reply-submit');
    const cancelQuoteBtn = document.getElementById('cancel-quote');
    const quotePidInput = document.getElementById('quote-pid');
    const quoteUidInput = document.getElementById('quote-uid');
    const replyTitle = document.getElementById('reply-title');
    const originalPlaceholder = messageInput.placeholder;

    if (!form) return;

    document.addEventListener('click', function(e) {
        const quoteBtn = e.target.closest('.quote-btn');
        if (quoteBtn) {
            const pid = quoteBtn.dataset.pid;
            const uid = quoteBtn.dataset.uid;
            const floor = quoteBtn.dataset.floor;
            const username = quoteBtn.dataset.username;

            setQuoteReply(pid, uid, floor, username);
        }
    });

    if (cancelQuoteBtn) {
        cancelQuoteBtn.addEventListener('click', function() {
            clearQuoteReply();
        });
    }

    function setQuoteReply(pid, uid, floor, username) {
        quotePidInput.value = pid;
        quoteUidInput.value = uid;
        if (replyTitle) replyTitle.textContent = '引用回复 #' + floor;
        if (cancelQuoteBtn) cancelQuoteBtn.classList.remove('hide');
        messageInput.placeholder = '回复 @' + username + '...';
        messageInput.focus();
        messageInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearQuoteReply() {
        quotePidInput.value = '';
        quoteUidInput.value = '';
        if (replyTitle) replyTitle.textContent = '回复';
        if (cancelQuoteBtn) cancelQuoteBtn.classList.add('hide');
        messageInput.placeholder = originalPlaceholder;
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
        const replySection = document.querySelector('.reply-section');
        replySection.insertAdjacentHTML('beforebegin', html);

        const existingPosts = document.querySelectorAll('.post-item');
        const newPost = existingPosts[existingPosts.length - 1];
        if (newPost) {
            newPost.scrollIntoView({ behavior: 'smooth', block: 'center' });
            newPost.style.backgroundColor = 'var(--bg-hover)';
            setTimeout(() => {
                newPost.style.transition = 'background-color 0.5s';
                newPost.style.backgroundColor = '';
            }, 2000);
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
        const favCount = document.getElementById('fav-count');

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
                    if (favBtn === mobileBtn) {
                        favBtn.classList.remove('btn-secondary');
                        favBtn.classList.add('btn-primary');
                        favBtn.querySelector('svg').setAttribute('fill', 'none');
                        if (favText) favText.textContent = '收藏';
                    } else {
                        favBtn.classList.remove('btn-secondary');
                        favBtn.classList.add('btn-primary');
                        favBtn.querySelector('svg').setAttribute('fill', 'none');
                        if (favText) favText.textContent = '收藏主题';
                    }
                    favBtn.dataset.favorited = '0';

                    if (mobileBtn && mobileBtn !== favBtn) {
                        mobileBtn.classList.remove('btn-secondary');
                        mobileBtn.classList.add('btn-primary');
                        mobileBtn.querySelector('svg').setAttribute('fill', 'none');
                        if (mobileText) mobileText.textContent = '收藏';
                        mobileBtn.dataset.favorited = '0';
                    }
                    if (desktopBtn && desktopBtn !== favBtn) {
                        desktopBtn.classList.remove('btn-secondary');
                        desktopBtn.classList.add('btn-primary');
                        desktopBtn.querySelector('svg').setAttribute('fill', 'none');
                        if (desktopText) desktopText.textContent = '收藏主题';
                        desktopBtn.dataset.favorited = '0';
                    }

                    if (favCount) {
                        favCount.textContent = Math.max(0, parseInt(favCount.textContent) - 1);
                    }
                } else {
                    if (favBtn === mobileBtn) {
                        favBtn.classList.remove('btn-primary');
                        favBtn.classList.add('btn-secondary');
                        favBtn.querySelector('svg').setAttribute('fill', 'currentColor');
                        if (favText) favText.textContent = '已收藏';
                    } else {
                        favBtn.classList.remove('btn-primary');
                        favBtn.classList.add('btn-secondary');
                        favBtn.querySelector('svg').setAttribute('fill', 'currentColor');
                        if (favText) favText.textContent = '已收藏';
                    }
                    favBtn.dataset.favorited = '1';

                    if (mobileBtn && mobileBtn !== favBtn) {
                        mobileBtn.classList.remove('btn-primary');
                        mobileBtn.classList.add('btn-secondary');
                        mobileBtn.querySelector('svg').setAttribute('fill', 'currentColor');
                        if (mobileText) mobileText.textContent = '已收藏';
                        mobileBtn.dataset.favorited = '1';
                    }
                    if (desktopBtn && desktopBtn !== favBtn) {
                        desktopBtn.classList.remove('btn-primary');
                        desktopBtn.classList.add('btn-secondary');
                        desktopBtn.querySelector('svg').setAttribute('fill', 'currentColor');
                        if (desktopText) desktopText.textContent = '已收藏';
                        desktopBtn.dataset.favorited = '1';
                    }

                    if (favCount) {
                        favCount.textContent = parseInt(favCount.textContent) + 1;
                    }
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
