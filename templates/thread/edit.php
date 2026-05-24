<div class="grid grid-cols-3">
 <div class="min-width-0">
 <div class="card">
 <div class="section">
 <div class="breadcrumb">
 <a href="index.php">首页</a>
 <span>/</span>
 <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>"><?php echo htmlspecialchars($template_forum['name'] ?? '版块'); ?></a>
 <span>/</span>
 <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>">主题</a>
 <span>/</span>
 <span>编辑内容</span>
 </div>
 <div class="mt-sm">
 <h1><?php echo htmlspecialchars($template_thread['subject'] ?? '修改内容'); ?></h1>
 </div>
 </div>
 <div class="card-body padded">
 <?php if (!empty($template_error)): ?>
 <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
 <?php endif; ?>
  <form method="post" action="index.php?c=thread&a=edit&pid=<?php echo $template_post['pid']; ?>">
 <div class="form-group">
 <label for="message">内容</label>
  <textarea id="message"
 name="message"
 class="message-editor"
 placeholder="支持 Markdown 语法..."><?php echo htmlspecialchars($template_post['message']); ?></textarea>
 </div>
 <div class="flex justify-end gap-md mt-lg">
 <a href="index.php?c=thread&a=index&tid=<?php echo $template_post['tid']; ?>" class="btn btn-secondary">取消</a>
 <button type="submit" class="btn btn-primary">保存</button>
 </div>
 </form>
 </div>
 </div>
 </div>
</div>
