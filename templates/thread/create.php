<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex flex-col items-start gap-4 px-4 py-3.5 border-b border-border">
        <div class="flex items-center gap-2 text-sm text-muted">
            <a href="index.php" class="hover:text-primary">首页</a>
            <span>/</span>
            <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>" class="hover:text-primary"><?php echo htmlspecialchars($template_forum['name']); ?></a>
            <span>/</span>
            <span>发布主题</span>
        </div>
        <h1 class="text-xl font-bold">在「<?php echo htmlspecialchars($template_forum['name']); ?>」发布内容</h1>
    </div>

    <div class="p-4">
        <?php if (!empty($template_error)): ?>
            <div class="p-3 rounded bg-danger-light text-danger mb-4 text-sm"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?c=thread&a=create&fid=<?php echo $template_forum['fid']; ?>">
            <div class="mb-4 flex flex-col gap-1.5">
                <label for="subject" class="text-sm font-medium text-text">标题</label>
                <input type="text" id="subject" name="subject" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" placeholder="" required>
            </div>

            <div class="mb-6 flex flex-col gap-1.5">
                <label for="message" class="text-sm font-medium text-text">内容</label>
                <textarea id="message" name="message" class="w-full h-auto min-h-50 p-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary resize-y" placeholder="支持 Markdown 语法，欢迎补充细节、经验与上下文..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover">取消</a>
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">发布主题</button>
            </div>
        </form>
    </div>
</div>
