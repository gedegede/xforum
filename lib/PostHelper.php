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
<div class="p-4 border-b border-border last:border-b-0" id="post-<?php echo $postPid; ?>" data-entry="post" data-pid="<?php echo $postPid; ?>">
    <div class="flex items-start justify-between gap-3 mb-3">
        <div class="flex items-start gap-3">
            <a href="index.php?c=member&a=profile&uid=<?php echo $postUid; ?>" class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold text-base flex-shrink-0 overflow-hidden">
                    <?php if (!empty($users[$postUid]['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($users[$postUid]['avatar']); ?>" alt="" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?php echo Helper::getAvatarInitial($users[$postUid]['username'] ?? '?'); ?>
                    <?php endif; ?>
                </div>
            </a>
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <a href="index.php?c=member&a=profile&uid=<?php echo $postUid; ?>" class="font-semibold text-primary hover:underline"><?php echo htmlspecialchars($users[$postUid]['username'] ?? '已删除用户'); ?></a>
                    <?php if ($isFirst): ?>
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-primary-light text-primary">楼主</span>
                    <?php endif; ?>
                    <?php if ($isPending): ?>
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-warning-light text-warning">待审核</span>
                    <?php endif; ?>
                </div>
                <div class="text-sm text-muted">
                    <?php echo date('Y-m-d H:i', $postDateline); ?> · #<?php echo $index; ?>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1 text-muted">
            <?php if ($canViewContent): ?>
            <button type="button" class="flex items-center justify-center w-7 h-7 rounded-sm cursor-pointer hover:bg-hover hover:text-text transition-colors" data-action="quote" data-pid="<?php echo $postPid; ?>" data-uid="<?php echo $postUid; ?>" data-floor="<?php echo $index; ?>" data-username="<?php echo htmlspecialchars($users[$postUid]['username'] ?? ''); ?>" title="引用">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1z"/>
                </svg>
            </button>
            <?php endif; ?>
            <?php if ($canEdit): ?>
            <a href="index.php?c=thread&a=edit&pid=<?php echo $postPid; ?>" class="flex items-center justify-center w-7 h-7 rounded-sm hover:bg-hover hover:text-text transition-colors" title="编辑">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="leading-relaxed">
        <?php if ($canViewContent): ?>
            <?php if ($quotePid > 0 && $quoteUid > 0): ?>
                <div class="p-3 mb-3 bg-soft border-l-3 border-primary rounded-r text-sm">
                    <div class="flex items-center gap-2 mb-2 text-xs text-muted">
                        <span>@<?php echo htmlspecialchars($users[$quoteUid]['username'] ?? '已删除用户'); ?> #<?php echo $quoteFloor; ?>：</span>
                    </div>
                </div>
            <?php endif; ?>
            <?php echo MarkdownHelper::parse((string)$post['message']); ?>
        <?php else: ?>
            <div class="text-center py-6 bg-soft rounded">
                <p class="text-muted">该内容正在审核中，仅管理员和版主可见</p>
            </div>
        <?php endif; ?>
    </div>
</div>
        <?php
        return (string)ob_get_clean();
    }
}
?>
