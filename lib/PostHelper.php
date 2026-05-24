<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Permission;
use Lib\Session;
use Lib\MarkdownHelper;

class PostHelper {
    public static function renderPost(array $post, array $users, int $index, bool $isFirst = false, ?array $currentUser = null, bool $isModerator = false): string {
        $post += [
            'pid' => 0,
            'fid' => 0,
            'tid' => 0,
            'uid' => 0,
            'dateline' => time(),
            'quote_pid' => 0,
            'quote_uid' => 0,
            'quote_floor' => 0,
            'sort_order' => 0,
            'message' => '',
        ];

        $postUid = (int)($post['uid'] ?? 0);
        $postPid = (int)($post['pid'] ?? 0);
        $postDateline = (int)($post['dateline'] ?? time());
        $quoteUid = (int)($post['quote_uid'] ?? 0);
        $quotePid = (int)($post['quote_pid'] ?? 0);
        $quoteFloor = (int)($post['quote_floor'] ?? 0);
        $sortOrder = (int)($post['sort_order'] ?? 0);
        $canEdit = $currentUser ? Permission::canEditPost($post) : false;
        $isPending = $sortOrder < 0;
        $canViewContent = !$isPending || $isModerator;
        ob_start();
        ?>
<div class="entry" id="post-<?php echo $postPid; ?>" data-pid="<?php echo $postPid; ?>">
    <div class="entry-head justify-between">
        <div class="flex items-center gap-md">
            <a href="index.php?c=member&a=profile&uid=<?php echo $postUid; ?>" class="avatar">
                <?php if (!empty($users[$postUid]['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($users[$postUid]['avatar']); ?>" alt="">
                <?php else: ?>
                    <?php echo Helper::getAvatarInitial($users[$postUid]['username'] ?? '?'); ?>
                <?php endif; ?>
            </a>
            <div class="flex-1 min-width-0">
                <div class="flex items-center gap-md">
                    <a href="index.php?c=member&a=profile&uid=<?php echo $postUid; ?>" class="font-bold text-primary hover-underline"><?php echo htmlspecialchars($users[$postUid]['username'] ?? '已删除用户'); ?></a>
                    <?php if ($isFirst): ?>
                        <span class="badge badge-blue">楼主</span>
                    <?php endif; ?>
                    <?php if ($isPending): ?>
                        <span class="badge badge-warning">待审核</span>
                    <?php endif; ?>
                </div>
                <div class="text-secondary font-sm">
                    <?php echo date('Y-m-d H:i', $postDateline); ?> · #<?php echo $index; ?>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-sm">
            <button type="button" class="btn btn-ghost text-muted p-xs" aria-label="点赞">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </button>
            <?php if ($canViewContent): ?>
            <button type="button" class="btn btn-ghost text-muted p-xs quote-btn" aria-label="引用" data-pid="<?php echo $postPid; ?>" data-uid="<?php echo $postUid; ?>" data-floor="<?php echo $index; ?>" data-username="<?php echo htmlspecialchars($users[$postUid]['username'] ?? ''); ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1z"/>
                </svg>
            </button>
            <?php endif; ?>
            <?php if ($canEdit): ?>
            <a href="index.php?c=thread&a=edit&pid=<?php echo $postPid; ?>" class="btn btn-ghost text-muted p-xs" aria-label="编辑">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="entry-body">
        <div class="content">
            <?php if ($canViewContent): ?>
                <?php if ($quotePid > 0 && $quoteUid > 0): ?>
                    <span class="text-secondary">@<?php echo htmlspecialchars($users[$quoteUid]['username'] ?? '已删除用户'); ?> #<?php echo $quoteFloor; ?>：</span>
                <?php endif; ?>
                <?php echo MarkdownHelper::parse((string)$post['message']); ?>
            <?php else: ?>
                <div class="text-center py-md bg-soft rounded">
                    <p class="text-secondary">该内容正在审核中，仅管理员和版主可见</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
        <?php
        return (string)ob_get_clean();
    }
}
?>
