<div class="card">
    <div class="card-header-col">
        <div class="flex items-center gap-2 text-sm text-muted">
            <a href="index.php" class="hover:text-primary">首页</a>
            <span>/</span>
            <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>" class="hover:text-primary"><?php echo htmlspecialchars($template_forum['name'] ?? '版块'); ?></a>
            <span>/</span>
            <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>" class="hover:text-primary">主题</a>
            <span>/</span>
            <span>编辑内容</span>
        </div>
        <h1 class="text-xl font-bold"><?php echo htmlspecialchars($template_thread['subject'] ?? '修改内容'); ?></h1>
    </div>

    <div class="card-body">
        <?php if (!empty($template_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?c=thread&a=edit&pid=<?php echo $template_post['pid']; ?>">
            <div class="form-field form-field-lg">
                <label for="message" class="form-label">内容</label>
                <textarea id="message" rows="20" name="message" class="form-control min-h-50" placeholder=""><?php echo htmlspecialchars($template_post['message']); ?></textarea>
            </div>

            <div class="form-actions">
                <a href="index.php?c=thread&a=index&tid=<?php echo $template_post['tid']; ?>" class="btn btn-soft">取消</a>
                <button type="submit" class="btn btn-primary">保存</button>
            </div>
        </form>
    </div>
</div>
