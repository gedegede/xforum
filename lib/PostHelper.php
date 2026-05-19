<?php
/**
 * 帖子渲染辅助类
 */
class PostHelper {

    /**
     * 渲染单个帖子 HTML
     *
     * @param array $post 帖子数据
     * @param array $users 用户数据数组
     * @param int $index 帖子索引（从1开始）
     * @param bool $isFirst 是否为楼主
     * @return string 渲染后的 HTML
     */
    public static function renderPost($post, $users, $index, $isFirst = false) {
        ob_start();
        ?>
<div class="post-item" id="post-<?php echo $post['pid']; ?>">
    <div class="post-header justify-between">
        <div class="flex items-center gap-md">
            <div class="avatar avatar-post">
                <?php echo strtoupper(substr($users[$post['uid']]['username'] ?? '?', 0, 1)); ?>
            </div>
            <div class="flex-1 min-width-0">
                <div class="flex items-center gap-md">
                    <span class="font-bold"><?php echo htmlspecialchars($users[$post['uid']]['username'] ?? '未知用户'); ?></span>
                    <?php if ($isFirst): ?>
                        <span class="badge badge-blue">楼主</span>
                    <?php endif; ?>
                </div>
                <div class="text-secondary font-sm">
                    <?php echo date('Y-m-d H:i', $post['dateline']); ?> · #<?php echo $index; ?>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-md">
            <button class="btn btn-ghost text-muted">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </button>
            <button class="btn btn-ghost text-muted quote-btn" data-pid="<?php echo $post['pid']; ?>" data-uid="<?php echo $post['uid']; ?>" data-floor="<?php echo $index; ?>" data-username="<?php echo htmlspecialchars($users[$post['uid']]['username'] ?? ''); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1z"/>
                </svg>
            </button>
        </div>
    </div>
    <div class="post-body">
        <div class="post-content">
            <?php if (!empty($post['quote_pid']) && !empty($post['quote_uid'])): ?>
                <span class="text-secondary">@<?php echo htmlspecialchars($users[$post['quote_uid']]['username'] ?? '未知用户'); ?> #<?php echo $post['quote_floor']; ?>：</span>
            <?php endif; ?>
            <?php echo nl2br(htmlspecialchars($post['message'])); ?>
        </div>
    </div>
</div>
        <?php
        return ob_get_clean();
    }
}
