<div class="grid grid-cols-3">
    <div class="main-content">
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
            <div class="card-body">
                <div class="tabs">
                    <a href="index.php?c=member&a=profile&uid=<?php echo $member['uid']; ?>" class="tab <?php echo $type == 'threads' ? 'active' : ''; ?>">
                        <span>主题</span>
                        <span class="badge badge-gray"><?php echo $member['thread_num']; ?></span>
                    </a>
                    <a href="index.php?c=member&a=profile&uid=<?php echo $member['uid']; ?>&type=replies" class="tab <?php echo $type == 'replies' ? 'active' : ''; ?>">
                        <span>回复</span>
                        <span class="badge badge-gray"><?php echo $member['reply_num']; ?></span>
                    </a>
                    <?php if ($isSelf): ?>
                        <a href="index.php?c=member&a=profile&uid=<?php echo $member['uid']; ?>&type=favorites" class="tab <?php echo $type == 'favorites' ? 'active' : ''; ?>">
                            <span>收藏</span>
                            <span class="badge badge-gray"><?php echo $member['fav_num']; ?></span>
                        </a>
                    <?php endif; ?>
                </div>

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
    </div>
    
    <?php if ($isSelf): ?>
    <div class="sidebar">
        <div class="card">
            <div class="card-header">
                <h3>个人菜单</h3>
            </div>
            <div class="card-body">
                <div class="menu-list">
                    <a href="index.php?c=member&a=profile&uid=<?php echo $member['uid']; ?>&type=threads" class="menu-item <?php echo $type == 'threads' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        <span>我的主题</span>
                    </a>
                    <a href="index.php?c=member&a=profile&uid=<?php echo $member['uid']; ?>&type=replies" class="menu-item <?php echo $type == 'replies' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        <span>我的回复</span>
                    </a>
                    <a href="index.php?c=member&a=profile&uid=<?php echo $member['uid']; ?>&type=favorites" class="menu-item <?php echo $type == 'favorites' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        <span>我的收藏</span>
                    </a>
                    <div class="menu-divider"></div>
                    <a href="index.php?c=member&a=settings" class="menu-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span>个人设置</span>
                    </a>
                    <a href="index.php?c=admin&a=index" class="menu-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11l2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6"/>
                        </svg>
                        <span>站点设置</span>
                    </a>
                    <div class="menu-divider"></div>
                    <a href="index.php?c=auth&a=logout" class="menu-item danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        <span>退出登录</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
