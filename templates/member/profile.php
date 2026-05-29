<?php include __DIR__ . '/_profile_header.php'; ?>
<?php include __DIR__ . '/_profile_nav.php'; ?>

<style>
.credit-coin{position:relative;display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:50%;background:radial-gradient(circle at 30% 24%,#fff4b8 0,#ffd66d 35%,#d99a19 72%,#9b6510 100%);border:2px solid #f6c24a;box-shadow:inset 0 2px 3px rgba(255,255,255,.65),inset 0 -4px 6px rgba(114,71,7,.28),0 2px 8px rgba(116,76,15,.18);color:#6b4200;font-size:12px;font-weight:800;letter-spacing:-.4px;line-height:1;white-space:nowrap}
.credit-coin::after{content:"";position:absolute;inset:6px;border:1px solid rgba(130,82,8,.28);border-radius:50%}
.credit-coin-negative{background:radial-gradient(circle at 30% 24%,#ffe4e6 0,#fb7185 42%,#be123c 100%);border-color:#fb7185;color:#fff}
</style>

<div class="card card-clip">
    <div class="card-header">
        <div>
            <h3 class="font-semibold">
                <?php if ($template_type == 'threads'): ?>
                    <?php echo $template_isSelf ? '我的主题' : 'Ta 的主题'; ?>
                <?php elseif ($template_type == 'replies'): ?>
                    <?php echo $template_isSelf ? '我的回复' : 'Ta 的回复'; ?>
                <?php elseif ($template_type == 'favorites'): ?>
                    我的收藏
                <?php else: ?>
                    金币明细
                <?php endif; ?>
            </h3>
        </div>
        <?php if ($template_type == 'credits' && $template_isSelf): ?>
            <form id="signin-form" method="post" action="index.php?c=member&a=signin">
                <button type="submit" id="signin-btn" class="btn btn-sm <?php echo !empty($template_signedToday) ? 'btn-soft' : 'btn-primary'; ?>" <?php echo !empty($template_signedToday) ? 'disabled' : ''; ?>>
                    <?php echo !empty($template_signedToday) ? '今日已签到' : '每日签到'; ?>
                </button>
            </form>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.getElementById('signin-form');
                var btn = document.getElementById('signin-btn');
                if (!form || !btn) return;

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    btn.disabled = true;
                    btn.textContent = '签到中...';

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data.success) {
                            btn.textContent = '今日已签到';
                            btn.classList.remove('btn-primary');
                            btn.classList.add('btn-soft');
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
                });
            });
            </script>
        <?php endif; ?>
    </div>

    <div class="card-body-flush">
        <?php if ($template_type == 'threads'): ?>
            <?php if ($template_threads): ?>
                <div class="list-stack">
                    <?php foreach ($template_threads as $thread): ?>
                        <?php echo \Lib\ThreadHelper::renderThread($thread, $template_users, $template_forums, ['show_avatar' => true, 'show_forum' => true]); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>暂无主题</p>
                </div>
            <?php endif; ?>

        <?php elseif ($template_type == 'replies'): ?>
            <?php if ($template_posts): ?>
                <div class="list-stack">
                    <?php foreach ($template_posts as $post): ?>
                        <?php
                        $thread = $template_threads[$post['tid']] ?? null;
                        if ($thread):
                            echo \Lib\ThreadHelper::renderThread($thread, $template_users, $template_forums, ['show_forum' => true, 'badge' => ['text' => '回复', 'class' => 'bg-soft text-sub']]);
                        endif;
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>暂无回复</p>
                </div>
            <?php endif; ?>

        <?php elseif ($template_type == 'favorites'): ?>
            <?php if ($template_favorites): ?>
                <div class="list-stack">
                    <?php foreach ($template_favorites as $fav): ?>
                        <?php
                        $thread = $template_threads[$fav['tid']] ?? null;
                        if ($thread):
                            echo \Lib\ThreadHelper::renderThread($thread, $template_users, $template_forums, ['show_forum' => true, 'badge' => ['text' => '收藏', 'class' => 'bg-success-light text-success']]);
                        endif;
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>暂无收藏</p>
                </div>
            <?php endif; ?>

        <?php elseif ($template_type == 'credits'): ?>
            <?php if ($template_credits): ?>
                <div class="list-stack">
                    <?php foreach ($template_credits as $credit): ?>
                        <?php $creditValue = (int)$credit['credit']; ?>
                        <?php $creditUrl = trim((string)($credit['url'] ?? '')); ?>
                        <<?php echo $creditUrl !== '' ? 'a href="' . htmlspecialchars($creditUrl) . '"' : 'div'; ?> class="list-link">
                            <div class="w-16 flex-shrink-0">
                                <span class="credit-coin <?php echo $creditValue < 0 ? 'credit-coin-negative' : ''; ?>">
                                    <?php echo $creditValue > 0 ? '+' . $creditValue : $creditValue; ?>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-text"><?php echo htmlspecialchars($credit['message']); ?></div>
                                <div class="text-sm text-muted mt-1">
                                    <span><?php echo \Lib\Helper::formatTime((int)$credit['dateline']); ?></span>
                                </div>
                            </div>
                        </<?php echo $creditUrl !== '' ? 'a' : 'div'; ?>>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>暂无金币明细</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($template_pages > 1): ?>
            <div class="section-footer">
                <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=member&a=profile&uid=' . $template_member['uid'] . '&type=' . $template_type); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
