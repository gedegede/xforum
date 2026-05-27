<div class="card">
    <div class="card-header-col">
        <div class="flex items-center gap-2 text-sm text-muted">
            <a href="index.php" class="hover:text-primary">首页</a>
            <span>/</span>
            <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>" class="hover:text-primary"><?php echo htmlspecialchars($template_forum['name']); ?></a>
            <span>/</span>
            <span>发布主题</span>
        </div>
        <h1 class="text-xl font-bold">在「<?php echo htmlspecialchars($template_forum['name']); ?>」发布内容</h1>
    </div>

    <div class="card-body">
        <?php if (!empty($template_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?c=thread&a=create&fid=<?php echo $template_forum['fid']; ?>">
            <div class="form-field">
                <label for="subject" class="form-label">标题</label>
                <input type="text" id="subject" name="subject" class="form-control" placeholder="" required>
            </div>

            <div class="form-field form-field-lg">
                <label for="message" class="form-label">内容</label>
                <textarea id="message" rows="20" name="message" class="form-control min-h-50" placeholder="支持 Markdown 语法"></textarea>
            </div>

            <div class="form-actions">
                <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>" class="btn btn-soft">取消</a>
                <button type="submit" class="btn btn-primary">发布主题</button>
            </div>
        </form>
    </div>
</div>
