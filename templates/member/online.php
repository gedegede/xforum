<div class="card">
 <div class="card-header">
 <h2>在线用户列表</h2>
 <p>查看当前活跃成员、用户组与最近浏览主题。</p>
 </div>
 <div class="card-body padded">
 <div class="table-container">
 <table class="table">
 <thead>
 <tr>
 <th class="nowrap">用户名</th>
 <th class="nowrap">用户组</th>
 <th class="nowrap">活跃时间</th>
 <th>最后浏览主题</th>
 </tr>
 </thead>
 <tbody>
 <?php if (!empty($template_onlineUsers)): ?>
 <?php foreach ($template_onlineUsers as $online): ?>
 <tr>
 <td>
 <?php if ($online['uid'] > 0): ?>
 <a href="index.php?c=member&a=profile&uid=<?php echo $online['uid']; ?>" class="flex items-center gap-sm font-bold">
 <span class="avatar avatar-sm"><?php echo \Lib\Helper::getAvatarInitial($online['username']); ?></span>
 <span><?php echo htmlspecialchars($online['username']); ?></span>
 </a>
 <?php else: ?>
 <span class="flex items-center gap-sm font-bold">
 <span class="avatar avatar-sm">游</span>
 <span><?php echo htmlspecialchars($online['username']); ?></span>
 </span>
 <?php endif; ?>
 </td>
 <td><span class="badge badge-gray"><?php echo htmlspecialchars($online['group_name']); ?></span></td>
 <td class="nowrap">
 <span class="flex items-center gap-sm muted">
 <span class="status-dot"></span>
 <?php echo date('Y-m-d H:i:s', $online['dateline']); ?>
 </span>
 </td>
 <td>
 <?php if (!empty($online['thread_subject'])): ?>
 <a href="index.php?c=thread&a=index&tid=<?php echo $online['tid']; ?>" class="font-bold">
 <?php echo htmlspecialchars($online['thread_subject']); ?>
 </a>
 <?php else: ?>
 <span class="text-muted">暂无记录</span>
 <?php endif; ?>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php else: ?>
 <tr>
 <td colspan="4" class="text-center text-secondary py-lg">暂无在线用户</td>
 </tr>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
</div>
