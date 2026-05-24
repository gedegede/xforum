<div class="card">
 <div class="section">
 <div class="flex items-center justify-between gap-md">
 <div>
 <h2>发件箱</h2>
 </div>
 <a href="index.php?c=pm&a=send" class="btn btn-primary">发送私信</a>
 </div>
 </div>
 <div class="card-body">
 <?php if (!empty($template_messages)): ?>
 <?php foreach ($template_messages as $message): ?>
 <a href="index.php?c=pm&a=view&pmid=<?php echo $message['pmid']; ?>" class="list-item">
 <div class="avatar avatar-sm">
 <?php echo \Lib\Helper::getAvatarInitial($template_users[$message['to_uid']]['username'] ?? '?'); ?>
 </div>
 <div class="flex-1 min-width-0">
 <div class="font-bold">
 <span class="font-bold" style="white-space:normal;">发送给：<?php echo htmlspecialchars($template_users[$message['to_uid']]['username'] ?? '已删除用户'); ?></span>
 <span class="badge badge-gray">已发送</span>
 </div>
 <div class="muted">
 <?php echo date('Y-m-d H:i', $message['dateline']); ?> ·
 <?php echo htmlspecialchars(mb_substr($message['content'], 0, 100)) . (mb_strlen($message['content']) > 100 ? '...' : ''); ?>
 </div>
 </div>
 </a>
 <?php endforeach; ?>
 <?php else: ?>
 <div class="empty-state">
 <p>暂无发送的私信</p>
 </div>
 <?php endif; ?>

 <?php if ($template_pages > 1): ?>
 <?php echo \Lib\Helper::renderPagination($template_page, $template_pages, 'index.php?c=pm&a=outbox'); ?>
 <?php endif; ?>
 </div>
</div>
