<div class="card">
    <div class="card-header">
        <h2>发布新主题</h2>
    </div>
    <div class="card-body padded">
        <div class="bg-hover border rounded p-lg mb-lg">
            <div class="font-lg font-bold"><?php echo htmlspecialchars($forum['name']); ?></div>
            <div class="text-secondary font-sm">在此版块发布新主题</div>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post" action="index.php?c=thread&a=create&fid=<?php echo $forum['fid']; ?>">
            <div class="form-group">
                <label for="subject">标题</label>
                <input type="text" id="subject" name="subject" placeholder="请输入主题标题" required>
            </div>
            <div class="form-group">
                <label for="message">内容</label>
                <textarea id="message" name="message" class="message-editor" placeholder="支持 Markdown 语法..." required></textarea>
            </div>
            <div class="flex justify-end gap-md mt-lg">
                <a href="index.php?c=forum&a=index&fid=<?php echo $forum['fid']; ?>" class="btn btn-secondary">取消</a>
                <button type="submit" class="btn btn-primary">发布主题</button>
            </div>
        </form>
    </div>
</div>
