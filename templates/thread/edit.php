<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex flex-col items-start gap-4 px-4 py-3.5 border-b border-border">
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

    <div class="p-4">
        <?php if (!empty($template_error)): ?>
            <div class="p-3 rounded bg-danger-light text-danger mb-4 text-sm"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?c=thread&a=edit&pid=<?php echo $template_post['pid']; ?>">
            <div class="mb-6 flex flex-col gap-1.5">
                <label for="message" class="text-sm font-medium text-text">内容</label>
                <textarea id="message" name="message" class="w-full h-auto min-h-50 p-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary resize-y" placeholder="支持 Markdown 语法..."><?php echo htmlspecialchars($template_post['message']); ?></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="index.php?c=thread&a=index&tid=<?php echo $template_post['tid']; ?>" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover">取消</a>
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">保存</button>
            </div>
        </form>
    </div>
</div>
