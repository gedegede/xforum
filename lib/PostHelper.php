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
    public static function renderPost(array $post, array $users, int $index, bool $isFirst = false, ?array $currentUser = null, bool $isModerator = false, array $ratedPids = [], int $page = 1): string {
        $post += [
            'pid' => 0,
            'fid' => 0,
            'tid' => 0,
            'is_thread' => 0,
            'uid' => 0,
            'dateline' => time(),
            'quote_pid' => 0,
            'quote_uid' => 0,
            'quote_floor' => 0,
            'message' => '',
        ];

        $postUid = (int)($post['uid'] ?? 0);
        $postPid = (int)($post['pid'] ?? 0);
        $postDateline = (int)($post['dateline'] ?? time());
        $quoteUid = (int)($post['quote_uid'] ?? 0);
        $quotePid = (int)($post['quote_pid'] ?? 0);
        $quoteFloor = (int)($post['quote_floor'] ?? 0);
        $rateNum = (int)($post['rate_num'] ?? 0);
        $isThreadPost = (int)($post['is_thread'] ?? 0) === 1;
        $isRated = isset($ratedPids[$postPid]) || in_array($postPid, $ratedPids, true);
        $canEdit = $currentUser ? Permission::canEditPost($post) : false;
        $canDelete = $currentUser ? Permission::canDeletePost($post) : false;
        $canCreditPost = $currentUser && (int)$currentUser['uid'] !== $postUid && ($isModerator || Permission::canCreditPost($post));
        $canReport = $currentUser && Permission::canReport();
        $canQuote = $currentUser && Permission::canReplyThread((int)($post['fid'] ?? 0));
        $creditLogs = json_decode((string)($post['credit_log'] ?? '[]'), true);
        $creditLogs = is_array($creditLogs) ? $creditLogs : [];
        ob_start();
        ?>
<div class="post-item" id="post-<?php echo $postPid; ?>" data-entry="post" data-pid="<?php echo $postPid; ?>">
    <div class="post-item-header">
        <div class="post-author">
            <a href="index.php?c=member&a=profile&uid=<?php echo $postUid; ?>" class="post-author-avatar-link">
                <div class="avatar avatar-md">
                    <?php if (!empty($users[$postUid]['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($users[$postUid]['avatar']); ?>" alt="" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?php echo Helper::getAvatarInitial($users[$postUid]['username'] ?? '?'); ?>
                    <?php endif; ?>
                </div>
            </a>
            <div class="post-author-main">
                <div class="post-author-name-row">
                    <a href="index.php?c=member&a=profile&uid=<?php echo $postUid; ?>" class="post-author-name"><?php echo htmlspecialchars($users[$postUid]['username'] ?? '已删除用户'); ?></a>
                    <?php if ($isFirst): ?>
                        <span class="badge badge-xs badge-primary">楼主</span>
                    <?php endif; ?>
                </div>
                <div class="post-meta">
                    <?php echo Helper::formatTime($postDateline); ?> · #<?php echo $index; ?>
                </div>
            </div>
        </div>
        <div class="post-actions">
            <?php if ($canCreditPost): ?>
            <button type="button" class="post-action" data-action="credit-post" data-pid="<?php echo $postPid; ?>" title="评分">
                <svg class="post-credit-action-icon" width="12" height="12" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="8"></circle>
                    <circle cx="12" cy="12" r="4"></circle>
                </svg>
            </button>
            <?php endif; ?>
            <div class="post-rate" data-rate-group="<?php echo $postPid; ?>">
                <?php if ($currentUser && Permission::canRate()): ?>
                <a href="index.php?c=thread&a=rate&pid=<?php echo $postPid; ?>" class="post-action<?php echo $isRated ? ' is-active' : ''; ?>" data-action="rate" data-pid="<?php echo $postPid; ?>" data-rated="<?php echo $isRated ? '1' : '0'; ?>" title="<?php echo $isRated ? '取消点赞' : '点赞'; ?><?php echo $rateNum > 0 ? ' (' . $rateNum . ')' : ''; ?>">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="<?php echo $isRated ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 10v11"></path>
                        <path d="M15 5.5 14 10h5.7a2 2 0 0 1 1.9 2.5l-2 7A2 2 0 0 1 17.7 21H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3l4.6-7a1.8 1.8 0 0 1 3.4 1.1v1.4z"></path>
                    </svg>
                </a>
                <?php endif; ?>
                <span class="post-rate-count <?php echo $rateNum > 0 ? '' : 'hidden'; ?>" data-role="rate-count"><?php echo $rateNum > 0 ? $rateNum : ''; ?></span>
            </div>
            <?php if ($canQuote): ?>
            <a href="#reply-section" class="post-action" data-action="quote" data-pid="<?php echo $postPid; ?>" data-uid="<?php echo $postUid; ?>" data-floor="<?php echo $index; ?>" data-username="<?php echo htmlspecialchars($users[$postUid]['username'] ?? ''); ?>" title="引用">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1z"/>
                </svg>
            </a>
            <?php endif; ?>
            <?php if ($canEdit): ?>
            <a href="index.php?c=thread&a=edit&pid=<?php echo $postPid; ?>" class="post-action" title="编辑">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </a>
            <?php endif; ?>
            <?php if ($canDelete && !$isThreadPost): ?>
            <button type="button" class="post-action" data-action="delete-post" data-pid="<?php echo $postPid; ?>" title="删除">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18"></path>
                    <path d="M8 6V4h8v2"></path>
                    <path d="M6 6l1 14h10l1-14"></path>
                    <path d="M10 11v5"></path>
                    <path d="M14 11v5"></path>
                </svg>
            </button>
            <?php endif; ?>
            <?php if ($canReport): ?>
            <button type="button" class="post-action" data-action="report-post" data-pid="<?php echo $postPid; ?>" title="举报">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                    <path d="M4 22V15"></path>
                </svg>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="post-content">
        <?php if (!empty($creditLogs)): ?>
            <div class="post-credit-log">
                <?php foreach ($creditLogs as $creditLog): ?>
                    <?php
                    $creditAmount = (int)($creditLog['credit'] ?? 0);
                    $creditUid = (int)($creditLog['uid'] ?? 0);
                    $creditUsername = (string)($creditLog['username'] ?? ($users[$creditUid]['username'] ?? '未知用户'));
                    ?>
                    <div class="post-credit-log-item <?php echo $creditAmount >= 0 ? 'is-plus' : 'is-minus'; ?>">
                        <span class="post-credit-log-coin"><?php echo $creditAmount > 0 ? '+' . $creditAmount : $creditAmount; ?></span>
                        <span class="post-credit-log-main">
                            <a href="index.php?c=member&a=profile&uid=<?php echo $creditUid; ?>" class="post-credit-log-user"><?php echo htmlspecialchars($creditUsername); ?></a>
                            <span><?php echo htmlspecialchars((string)($creditLog['reason'] ?? '评分')); ?></span>
                        </span>
                        <span class="post-credit-log-time"><?php echo Helper::formatTime((int)($creditLog['time'] ?? 0)); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php echo MarkdownHelper::parse((string)$post['message']); ?>
    </div>
</div>
        <?php
        return (string)ob_get_clean();
    }
}
?>
