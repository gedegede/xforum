<style>
.summary-box{margin-bottom:var(--space-4);padding:var(--space-4);border:1px solid var(--border);border-radius:var(--radius);background:var(--soft)}
.summary-row{display:flex;align-items:center;gap:var(--space-3)}
</style>
<div class="card">
    <div class="card-header-col">
        <div class="flex items-center gap-2 text-sm text-muted">
            <a href="index.php" class="hover:text-primary">首页</a>
            <span>/</span>
            <a href="index.php?c=forum&a=index&fid=<?php echo $template_thread['fid']; ?>" class="hover:text-primary">返回版块</a>
            <span>/</span>
            <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>" class="hover:text-primary">主题</a>
            <span>/</span>
            <span>回复</span>
        </div>
        <h1 class="text-xl font-bold"><?php echo htmlspecialchars($template_thread['subject']); ?></h1>
    </div>

    <div class="card-body">
        <div class="summary-box">
            <div class="summary-row">
            <span class="badge <?php echo isset($template_thread['status']) && $template_thread['status'] == 1 ? 'badge-success' : 'badge-danger'; ?>">
                <?php echo isset($template_thread['status']) && $template_thread['status'] == 1 ? '开放' : '关闭'; ?>
            </span>
            <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>" class="font-semibold flex-1">
                <?php echo htmlspecialchars($template_thread['subject']); ?>
            </a>
            </div>
        </div>

        <?php if (!empty($template_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?c=thread&a=reply&tid=<?php echo $template_thread['tid']; ?>">
            <div class="form-field form-field-lg">
                <label for="message" class="form-label">回复内容</label>
                <textarea id="message" name="message" class="form-control min-h-40" placeholder="支持 Markdown。建议补充结论、复现场景或进一步问题。"></textarea>
            </div>

            <div class="form-actions">
                <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>" class="btn btn-soft">取消</a>
                <button type="submit" class="btn btn-primary">提交回复</button>
            </div>
        </form>
    </div>
</div>
