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
        $compactMode = (bool)($options['compact_mode'] ?? false);
        $badge = $options['badge'] ?? null;

        ob_start();
        ?>
<a href="index.php?c=thread&amp;a=index&amp;tid=<?php echo $thread['tid']; ?>" class="flex items-center gap-3 p-3 border-b border-border last:border-b-0 hover:bg-hover transition-colors">
    <?php if ($showAvatar && !$compactMode): ?>
        <div class="w-8 h-8 rounded-full bg-primary-light text-muted flex items-center justify-center font-semibold text-sm flex-shrink-0 overflow-hidden">
            <?php if ($author && !empty($author['avatar'])): ?>
                <img src="<?php echo htmlspecialchars($author['avatar']); ?>" alt="" class="w-full h-full object-cover">
            <?php else: ?>
                <?php echo Helper::getAvatarInitial($author['username'] ?? '?'); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="font-semibold <?php echo $truncateSubject ? 'truncate' : ''; ?>"><?php echo htmlspecialchars($thread['subject']); ?></span>
            <?php if ($showForum && $forum): ?>
                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-soft text-sub flex-shrink-0"><?php echo htmlspecialchars($forum['name']); ?></span>
            <?php endif; ?>
            <?php if ($badge): ?>
                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-medium <?php echo $badge['class'] ?? 'bg-primary-light text-primary'; ?> flex-shrink-0"><?php echo htmlspecialchars($badge['text']); ?></span>
            <?php endif; ?>
        </div>
        <?php if (!$compactMode): ?>
            <div class="flex items-center gap-2 text-sm text-muted mt-1 hide-mobile">
                <span><?php echo htmlspecialchars($author['username'] ?? '匿名'); ?></span>
                <span>·</span>
                <span><?php echo date('Y-m-d H:i', $thread['dateline']); ?></span>
                <?php if (!empty($thread['reply_time']) && (int)$thread['reply_time'] !== (int)$thread['dateline']): ?>
                    <span>·</span>
                    <span>最后活跃 <?php echo date('Y-m-d H:i', (int)$thread['reply_time']); ?></span>
                <?php endif; ?>
            </div>
            <div class="mobile-only text-sm text-muted mt-1">
                <span><?php echo htmlspecialchars($author['username'] ?? '匿名'); ?></span>
                <span>·</span>
                <span><?php echo date('Y-m-d', $thread['dateline']); ?></span>
                <?php if ($showStats): ?>
                    <span>·</span>
                    <span><?php echo $thread['reply_num']; ?> 回复</span>
                    <span>·</span>
                    <span><?php echo $thread['view_num']; ?> 浏览</span>
                <?php endif; ?>
            </div>
        <?php elseif ($showStats): ?>
            <div class="flex items-center gap-3 text-sm text-muted mt-1">
                <span><?php echo date('Y-m-d', $thread['dateline']); ?></span>
                <span><?php echo $thread['reply_num']; ?> 回复</span>
                <span><?php echo $thread['view_num']; ?> 浏览</span>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($showStats && !$compactMode): ?>
        <div class="hide-mobile flex items-center gap-4 text-sm flex-shrink-0">
            <div class="text-center">
                <div class="font-semibold"><?php echo $thread['view_num']; ?></div>
                <div class="text-xs text-muted">浏览</div>
            </div>
            <div class="text-center">
                <div class="font-semibold"><?php echo $thread['reply_num']; ?></div>
                <div class="text-xs text-muted">回复</div>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($compactMode): ?>
        <svg class="w-4 h-4 text-muted flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 18l6-6-6-6"/>
        </svg>
    <?php endif; ?>
</a>
        <?php
        return (string)ob_get_clean();
    }
}
