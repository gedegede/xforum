<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class ThreadHelper {
    public static function renderThread(array $thread, array $users = [], array $forums = [], array $options = []): string {
        $thread += [
            'tid' => 0,
            'uid' => 0,
            'fid' => 0,
            'subject' => '',
            'dateline' => time(),
            'reply_time' => 0,
            'view_num' => 0,
            'reply_num' => 0,
        ];

        $author = $users[$thread['uid']] ?? null;
        $forum = $forums[$thread['fid']] ?? null;
        $showAvatar = (bool)($options['show_avatar'] ?? true);
        $showForum = (bool)($options['show_forum'] ?? false);
        $showStats = (bool)($options['show_stats'] ?? true);
        $truncateSubject = (bool)($options['truncate_subject'] ?? false);
        $badge = $options['badge'] ?? null;
        $replyNum = (int)$thread['reply_num'];
        $viewNum = (int)$thread['view_num'];
        $userIcon = '<svg class="thread-meta-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>';
        $postTimeIcon = '<svg class="thread-meta-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M5 3h10l4 4v14H5V3zm9 1.5V8h3.5L14 4.5zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/></svg>';
        $replyTimeIcon = '<svg class="thread-meta-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H5a2 2 0 00-2 2v14l4-4h10a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>';
        $replyIcon = '<svg class="thread-meta-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M4 5h16v10H8l-4 4V5zm4 4v2h8V9H8z"/></svg>';
        $viewIcon = '<svg class="thread-meta-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 12.5a5 5 0 110-10 5 5 0 010 10z"/></svg>';
        $badgeClass = $badge['class'] ?? 'badge-primary';
        if (str_contains((string)$badgeClass, 'bg-')) {
            $badgeClass = match ((string)$badgeClass) {
                'bg-success-light text-success' => 'badge-success',
                'bg-danger-light text-danger' => 'badge-danger',
                'bg-warning-light text-warning' => 'badge-warning',
                'bg-soft text-sub' => 'badge-soft',
                default => 'badge-primary',
            };
        }

        ob_start();
        ?>
<a href="index.php?c=thread&amp;a=index&amp;tid=<?php echo $thread['tid']; ?>" class="thread-item">
    <?php if ($showAvatar): ?>
        <div class="thread-item-avatar avatar avatar-sm">
            <?php if ($author && !empty($author['avatar'])): ?>
                <img src="<?php echo htmlspecialchars($author['avatar']); ?>" alt="" class="w-full h-full object-cover">
            <?php else: ?>
                <?php echo Helper::getAvatarInitial($author['username'] ?? '?'); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="thread-item-body">
        <div class="thread-item-title">
            <span class="thread-item-subject font-semibold <?php echo $truncateSubject ? 'truncate' : ''; ?>"><?php echo htmlspecialchars($thread['subject']); ?></span>
            <?php if ($showForum && $forum): ?>
                <span class="badge badge-xs badge-soft thread-item-badge"><?php echo htmlspecialchars($forum['name']); ?></span>
            <?php endif; ?>
            <?php if ($badge): ?>
                <span class="badge badge-xs thread-item-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($badge['text']); ?></span>
            <?php endif; ?>
        </div>

        <div class="thread-item-meta">
            <span><?php echo $userIcon; ?><?php echo htmlspecialchars($author['username'] ?? '匿名'); ?></span>
            <span><?php echo $postTimeIcon; ?><?php echo Helper::formatTime((int)$thread['dateline']); ?></span>
            <?php if (!empty($thread['reply_time']) && (int)$thread['reply_time'] !== (int)$thread['dateline']): ?>
                <span><?php echo $replyTimeIcon; ?><?php echo Helper::formatTime((int)$thread['reply_time']); ?></span>
            <?php endif; ?>
            <?php if ($showStats): ?>

                <?php if ($viewNum > 0): ?>
                    <span><?php echo $viewIcon; ?><?php echo $viewNum; ?></span>
                <?php endif; ?>
                
                <?php if ($replyNum > 0): ?>
                    <span><?php echo $replyIcon; ?><?php echo $replyNum; ?></span>
                <?php endif; ?>
                
            <?php endif; ?>
        </div>
    </div>
</a>
        <?php
        return (string)ob_get_clean();
    }
}
