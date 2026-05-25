<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex flex-col items-start gap-4 px-4 py-3.5 border-b border-border">
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

    <div class="p-4">
        <div class="flex items-center gap-3 p-4 mb-4 rounded border bg-soft">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium <?php echo isset($template_thread['status']) && $template_thread['status'] == 1 ? 'bg-success-light text-success' : 'bg-danger-light text-danger'; ?>">
                <?php echo isset($template_thread['status']) && $template_thread['status'] == 1 ? '开放' : '关闭'; ?>
            </span>
            <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>" class="font-semibold flex-1">
                <?php echo htmlspecialchars($template_thread['subject']); ?>
            </a>
        </div>

        <?php if (!empty($template_error)): ?>
            <div class="p-3 rounded bg-danger-light text-danger mb-4 text-sm"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?c=thread&a=reply&tid=<?php echo $template_thread['tid']; ?>">
            <div class="mb-6 flex flex-col gap-1.5">
                <label for="message" class="text-sm font-medium text-text">回复内容</label>
                <textarea id="message" name="message" class="w-full h-auto min-h-40 p-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary resize-y" placeholder="支持 Markdown。建议补充结论、复现场景或进一步问题。"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover">取消</a>
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">提交回复</button>
            </div>
        </form>
    </div>
</div>
