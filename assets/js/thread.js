document.addEventListener('DOMContentLoaded', function() {
    const pageData = document.getElementById('thread-page-data');
    const targetPid = pageData ? pageData.dataset.targetPid : '';
    const creditChange = pageData ? Number(pageData.dataset.creditChange || 0) : 0;
    const reportModal = document.getElementById('report-modal');
    const reportPidInput = document.getElementById('report-pid');
    const reportReasonInput = document.getElementById('report-reason');
    const reportSubmit = document.getElementById('report-submit');
    const creditPostModal = document.getElementById('credit-post-modal');
    const creditPostPidInput = document.getElementById('credit-post-pid');
    const creditPostAmountInput = document.getElementById('credit-post-amount');
    const creditPostReasonInput = document.getElementById('credit-post-reason');
    const creditPostSubmit = document.getElementById('credit-post-submit');

    if (targetPid) {
        const targetPost = document.getElementById('post-' + targetPid);
        if (targetPost) {
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
        }
    }

    if (creditChange && typeof window.showCreditToast === 'function') {
        window.showCreditToast(creditChange);
    }

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
                        showMessageModal('提示', data.message || '操作失败，请重试');
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
                .catch(() => {
                    window.location.href = 'index.php?c=auth&a=login';
                });
            return;
        }

        const deleteBtn = e.target.closest('[data-action="delete-post"]');
        if (deleteBtn) {
            const pid = deleteBtn.dataset.pid;
            const isThreadDelete = deleteBtn.classList.contains('thread-delete-btn');
            showConfirmModal('确认删除', isThreadDelete ? '确定要删除该主题吗？' : '确定要删除这条回复吗？', function() {
                fetch('index.php?c=thread&a=deletePost&pid=' + pid, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (error) {
                            return {success: false, message: text.replace(/<[^>]+>/g, '').trim() || '删除失败，请重试'};
                        }
                    })
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
                            showMessageModal('提示', data.message || '删除失败，请重试');
                        }
                    })
                    .catch(() => {
                        showMessageModal('提示', '网络错误，请重试');
                    });
            });
        }

        const creditPostBtn = e.target.closest('[data-action="credit-post"]');
        if (creditPostBtn) {
            e.preventDefault();
            e.stopPropagation();
            if (!creditPostModal || !creditPostPidInput || !creditPostAmountInput || !creditPostReasonInput) {
                showMessageModal('提示', '评分弹窗加载失败');
                return;
            }
            creditPostPidInput.value = creditPostBtn.dataset.pid || '';
            creditPostAmountInput.value = '';
            creditPostReasonInput.value = '';
            creditPostModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            creditPostAmountInput.focus();
        }

        const closeCreditPostBtn = e.target.closest('[data-action="close-credit-post"]');
        if (closeCreditPostBtn) {
            closeCreditPostModal();
        }

        const reportBtn = e.target.closest('[data-action="report-post"]');
        if (reportBtn) {
            if (!reportModal || !reportPidInput || !reportReasonInput) return;
            reportPidInput.value = reportBtn.dataset.pid || '';
            reportReasonInput.value = '';
            reportModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            reportReasonInput.focus();
        }

        const closeReportBtn = e.target.closest('[data-action="close-report"]');
        if (closeReportBtn) {
            closeReportModal();
        }
    });

    if (reportModal) {
        reportModal.addEventListener('click', function(e) {
            if (e.target === reportModal) closeReportModal();
        });
    }

    if (creditPostModal) {
        creditPostModal.addEventListener('click', function(e) {
            if (e.target === creditPostModal) closeCreditPostModal();
        });
    }

    if (creditPostSubmit) {
        creditPostSubmit.addEventListener('click', function() {
            const pid = creditPostPidInput ? creditPostPidInput.value : '';
            const amount = creditPostAmountInput ? Number(creditPostAmountInput.value || 0) : 0;
            const reason = creditPostReasonInput ? creditPostReasonInput.value.trim() : '';
            if (!pid || !amount) {
                showMessageModal('提示', '请填写金币数量');
                return;
            }
            if (!reason) {
                showMessageModal('提示', '请填写评分理由');
                return;
            }

            const form = new FormData();
            form.append('credit', String(amount));
            form.append('reason', reason);
            form.append('csrf_token', window.getCsrfToken ? window.getCsrfToken() : '');
            creditPostSubmit.disabled = true;
            fetch('index.php?c=thread&a=creditPost&pid=' + pid, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: form
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        showMessageModal('提示', data.message || '评分失败');
                    }
                })
                .catch(() => showMessageModal('提示', '网络错误，请重试'))
                .finally(() => {
                    creditPostSubmit.disabled = false;
                });
        });
    }

    if (reportSubmit) {
        reportSubmit.addEventListener('click', function() {
            const pid = reportPidInput ? reportPidInput.value : '';
            const reason = reportReasonInput ? reportReasonInput.value.trim() : '';
            if (!pid || !reason) {
                showMessageModal('提示', '请填写举报理由');
                return;
            }
            const form = new FormData();
            form.append('reason', reason);
            form.append('csrf_token', window.getCsrfToken ? window.getCsrfToken() : '');
            fetch('index.php?c=thread&a=report&pid=' + pid, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: form
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeReportModal();
                        window.showTip ? window.showTip(data.message || '举报已提交', 'success') : showMessageModal('提示', data.message || '举报已提交');
                    } else {
                        showMessageModal('提示', data.message || '举报失败');
                    }
                })
                .catch(() => showMessageModal('提示', '网络错误，请重试'));
        });
    }

    function closeReportModal() {
        if (!reportModal) return;
        reportModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function closeCreditPostModal() {
        if (!creditPostModal) return;
        creditPostModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('click', function(e) {
        const quoteBtn = e.target.closest('[data-action="quote"]');
        if (quoteBtn) {
            e.preventDefault();
            setQuoteReply(quoteBtn.dataset.pid, quoteBtn.dataset.uid, quoteBtn.dataset.floor, quoteBtn.dataset.username);
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

    if (cancelQuoteBtn) {
        cancelQuoteBtn.addEventListener('click', function() {
            clearQuoteReply();
        });
    }

    function setQuoteReply(pid, uid, floor, username) {
        if (!quotePidInput || !quoteUidInput || !messageInput) return;
        quotePidInput.value = pid;
        quoteUidInput.value = uid;
        if (replyTitle) replyTitle.textContent = '引用回复 #' + floor;
        if (cancelQuoteBtn) cancelQuoteBtn.classList.remove('hidden');
        const mention = '@' + username + ' #' + floor + ' ';
        if (!messageInput.value.trim().startsWith(mention.trim())) {
            messageInput.value = mention + messageInput.value.replace(new RegExp('^@' + username.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '(\\s+#\\d+)?\\s*'), '');
        }
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

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const message = messageInput.value.trim();
            if (!message) {
                showMessageModal('提示', '请填写回复内容');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = '提交中...';

            fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        addReplyToPage(data.html, data.pid);
                        messageInput.value = '';
                        clearQuoteReply();
                        updateReplyCount();
                        if (data.credit_change && typeof window.showCreditToast === 'function') {
                            window.showCreditToast(data.credit_change);
                        }
                    } else {
                        showThreadTip(data.message || '回复失败，请重试');
                    }
                })
                .catch(() => {
                    showThreadTip('网络错误，请重试');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '发表回复';
                });
        });
    }

    function showThreadTip(message) {
        if (typeof window.showTip === 'function') {
            window.showTip(message, 'danger');
            return;
        }
        showMessageModal('提示', message);
    }

    function addReplyToPage(html, pid) {
        const replySection = document.getElementById('reply-section');
        if (!replySection) return;
        replySection.insertAdjacentHTML('beforebegin', html);

        const newPost = pid ? document.getElementById('post-' + pid) : replySection.previousElementSibling;
        if (newPost) {
            requestAnimationFrame(function() {
                newPost.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });

            newPost.classList.add('animate-highlight');

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
                        [favBtn, mobileBtn, desktopBtn].forEach(btn => {
                            if (!btn) return;
                            btn.classList.add('badge-soft');
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
                        [favBtn, mobileBtn, desktopBtn].forEach(btn => {
                            if (!btn) return;
                            btn.classList.add('badge-soft');
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
                    showMessageModal('提示', data.message || '操作失败，请重试');
                }
            })
            .catch(() => {
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
