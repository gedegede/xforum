<div class="card">
    <div class="thread-header">
        <div class="breadcrumb">
            <a href="index.php">首页</a>
            <span>/</span>
            <a href="index.php?c=forum&a=index&fid=<?php echo $template_thread['fid']; ?>">返回版块</a>
            <span>/</span>
            <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>">主题</a>
            <span>/</span>
            <span>回复</span>
        </div>
        <div class="mt-sm">
            <h1><?php echo htmlspecialchars($template_thread['subject']); ?></h1>
        </div>
    </div>
    <div class="card-body padded">
        <div class="bg-hover border rounded p-lg mb-lg flex items-center gap-md">
            <span class="badge <?php echo isset($template_thread['status']) && $template_thread['status'] == 1 ? 'badge-green' : 'badge-red'; ?>"><?php echo isset($template_thread['status']) && $template_thread['status'] == 1 ? '开放' : '关闭'; ?></span>
            <div class="font-bold flex-1">
                <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>"><?php echo htmlspecialchars($template_thread['subject']); ?></a>
            </div>
        </div>
        
        <?php if (!empty($template_error)): ?>
            <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>
        
        <form method="post" action="index.php?c=thread&a=reply&tid=<?php echo $template_thread['tid']; ?>">
            <div class="form-group">
                <label for="message">回复内容</label>
                <textarea id="message" name="message" class="message-editor" placeholder="支持 Markdown。建议补充结论、复现场景或进一步问题。"></textarea>
            </div>
            <div class="flex justify-end gap-md mt-lg">
                <a href="index.php?c=thread&a=index&tid=<?php echo $template_thread['tid']; ?>" class="btn btn-secondary">取消</a>
                <button type="submit" class="btn btn-primary">提交回复</button>
            </div>
        </form>
    </div>
</div>
