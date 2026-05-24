<div class="card">
 <div class="section">
 <h2><?php echo $template_from === 'create' ? '选择发布版块' : '论坛导航'; ?></h2>
 <div class="flex flex-wrap gap-sm mt-lg">
 <span class="badge badge-gray"><?php echo count($template_forums); ?> 个版块</span>
 <span class="badge badge-gray"><?php echo $template_from === 'create' ? '优先选择最匹配的话题分区' : '支持层级版块浏览'; ?></span>
 </div>
 </div>
 <div class="card-body padded">
 <?php if (!empty($template_forums)): ?>
 <div>
 <?php foreach ($template_forums as $forum): ?>
 <a href="<?php echo $template_from === 'create' ? 'index.php?c=thread&a=create&fid=' . $forum['fid'] : 'index.php?c=forum&a=index&fid=' . $forum['fid']; ?>" class="list-item">
 <div class="flex-1 min-width-0">
 <div class="font-bold">
 <?php if ((int)$forum['depth'] > 0): ?>
 <span class="badge badge-gray">Lv <?php echo (int)$forum['depth'] + 1; ?></span>
 <?php else: ?>
 <span class="badge badge-green">主版块</span>
 <?php endif; ?>
 <span class="font-bold" style="white-space:normal;"><?php echo htmlspecialchars($forum['name']); ?></span>
 </div>
 <div class="muted">
 <?php if (!empty($forum['description'])): ?>
 <?php echo htmlspecialchars($forum['description']); ?>
 <?php else: ?>
 暂无版块简介
 <?php endif; ?>
 <span class="separator">•</span>
 <?php echo (int)($forum['thread_num'] ?? 0); ?> 主题
 <span class="separator">•</span>
 <?php echo (int)($forum['reply_num'] ?? 0); ?> 回复
 </div>
 </div>
 <div class="arrow"></div>
 </a>
 <?php endforeach; ?>
 </div>
 <?php else: ?>
 <div class="empty-state">暂无论坛版块</div>
 <?php endif; ?>
 </div>
</div>
