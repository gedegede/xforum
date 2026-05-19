<div class="card">
    <div class="card-header">
        <div class="flex items-center gap-lg min-width-0">
            <div class="avatar avatar-lg flex-shrink-0">
                <?php echo strtoupper(substr($member['username'], 0, 1)); ?>
            </div>
            <div class="min-width-0">
                <h2><?php echo htmlspecialchars($member['username']); ?></h2>
                <div class="text-secondary font-sm">
                    注册于 <?php echo date('Y-m-d', $member['reg_date']); ?> · 
                    <?php echo $member['thread_num']; ?> 主题 · 
                    <?php echo $member['reply_num']; ?> 回复
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($isSelf): ?>
<div class="card">
    <div class="tabs">
        <a href="index.php?c=member&a=profile&uid=<?php echo $member['uid']; ?>&type=threads" class="tab<?php echo $type == 'threads' ? ' active' : ''; ?>">我的主题</a>
        <a href="index.php?c=member&a=profile&uid=<?php echo $member['uid']; ?>&type=replies" class="tab<?php echo $type == 'replies' ? ' active' : ''; ?>">我的回复</a>
        <a href="index.php?c=member&a=profile&uid=<?php echo $member['uid']; ?>&type=favorites" class="tab<?php echo $type == 'favorites' ? ' active' : ''; ?>">我的收藏</a>
        <a href="index.php?c=member&a=settings" class="tab<?php echo (isset($_GET['c']) && $_GET['c'] == 'member' && $_GET['a'] == 'settings') ? ' active' : ''; ?>">个人设置</a>
        <a href="index.php?c=admin&a=index" class="tab<?php echo (isset($_GET['c']) && $_GET['c'] == 'admin') ? ' active' : ''; ?>">站点设置</a>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">

        <?php if ($type == 'threads'): ?>
            <?php if ($threads): ?>
                <?php foreach ($threads as $thread): ?>
                    <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="list-item">
                        <div class="item-info">
                            <div class="item-title"><?php echo htmlspecialchars($thread['subject']); ?></div>
                            <div class="item-meta">
                                <?php echo date('M j, Y', $thread['dateline']); ?> · <?php echo $thread['reply_num']; ?> 回复 · <?php echo $thread['view_num']; ?> 浏览
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>暂无主题</p>
                </div>
            <?php endif; ?>
        <?php elseif ($type == 'replies'): ?>
            <?php if ($posts): ?>
                <?php foreach ($posts as $post): ?>
                    <?php $thread = $threads[$post['tid']] ?? null; ?>
                    <a href="index.php?c=thread&a=index&tid=<?php echo $post['tid']; ?>" class="list-item">
                        <div class="item-info">
                            <div class="item-title"><?php echo $thread ? htmlspecialchars($thread['subject']) : '已删除'; ?></div>
                            <div class="item-meta"><?php echo date('M j, Y', $post['dateline']); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>暂无回复</p>
                </div>
            <?php endif; ?>
        <?php elseif ($type == 'favorites'): ?>
            <?php if ($favorites): ?>
                <?php foreach ($favorites as $fav): ?>
                    <?php $thread = $threads[$fav['tid']] ?? null; ?>
                    <a href="index.php?c=thread&a=index&tid=<?php echo $fav['tid']; ?>" class="list-item">
                        <div class="item-info">
                            <div class="item-title"><?php echo $thread ? htmlspecialchars($thread['subject']) : '已删除'; ?></div>
                            <div class="item-meta"><?php echo date('M j, Y', $fav['dateline']); ?> · <?php echo $thread ? $thread['reply_num'] : 0; ?> 回复</div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>暂无收藏</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($pages > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $member['uid']; ?>&type=<?php echo $type; ?>&page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
