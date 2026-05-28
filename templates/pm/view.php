<?php $template_lastMessage = !empty($template_messages) ? $template_messages[array_key_last($template_messages)] : null; ?>
<?php $template_offset = (max(1, (int)($template_page ?? 1)) - 1) * (int)($template_pageSize ?? 20); ?>

<div class="card pm-chat">
    <div class="pm-chat-header">
        <div class="avatar avatar-md text-primary"><?php echo \Lib\Helper::getAvatarInitial($template_partner['username']); ?></div>
        <div class="flex-1 min-w-0">
            <h2 class="font-semibold"><?php echo htmlspecialchars($template_partner['username']); ?></h2>
            <div class="text-sm text-muted">私信聊天记录</div>
        </div>
        <a href="index.php?c=pm&a=inbox" class="btn btn-soft btn-sm pm-back">返回会话列表</a>
        <div class="pm-refresh">
            <span id="pm-refresh-count">60s</span>
            <button type="button" class="btn btn-soft btn-sm" id="pm-refresh-now">刷新</button>
        </div>
    </div>

    <div class="pm-chat-body" id="pm-chat-body" data-current-uid="<?php echo (int)$template_user['uid']; ?>" data-partner-uid="<?php echo (int)$template_partner['uid']; ?>" data-last-pmid="<?php echo (int)($template_lastPmid ?? ($template_lastMessage['pmid'] ?? 0)); ?>" data-page="<?php echo (int)$template_page; ?>" data-pages="<?php echo (int)$template_pages; ?>">
        <?php $template_unreadStart = max(0, (int)($template_total ?? 0) - (int)($template_unreadNum ?? 0)); ?>
        <?php foreach ($template_messages as $index => $message): ?>
            <?php $isMine = (int)$message['uid'] === (int)$template_user['uid']; ?>
            <?php $isUnread = !$isMine && ($template_offset + $index) >= $template_unreadStart; ?>
            <div class="pm-bubble-row <?php echo $isMine ? 'is-mine' : ''; ?> <?php echo $isUnread ? 'is-unread' : ''; ?>" data-pmid="<?php echo (int)$message['pmid']; ?>">
                <div class="pm-bubble">
                    <?php if ($isUnread): ?><span class="pm-unread-dot"></span><?php endif; ?>
                    <div class="pm-bubble-text"><?php echo nl2br(htmlspecialchars($message['content'])); ?></div>
                    <div class="pm-bubble-time"><?php echo date('Y-m-d H:i:s', (int)$message['dateline']); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ((int)($template_pages ?? 1) > 1): ?>
        <div class="pm-chat-pagination">
            <?php
            $template_pageBaseUrl = 'index.php?c=pm&a=view&uid=' . (int)$template_partner['uid'];
            if ((int)($template_unreadNum ?? 0) > 0) {
                $template_pageBaseUrl .= '&unread=' . (int)$template_unreadNum;
            }
            echo \Lib\Helper::renderPagination((int)$template_page, (int)$template_pages, $template_pageBaseUrl);
            ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?c=pm&a=send" class="pm-chat-form" id="pm-chat-form">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($template_partner['username']); ?>">
        <textarea name="content" class="form-control" rows="3" required placeholder="输入私信内容"></textarea>
        <button type="submit" class="btn btn-primary">发送</button>
    </form>
</div>

<script src="assets/js/pm.js"></script>
