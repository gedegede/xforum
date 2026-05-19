<div class="card">
    <div class="card-header">
        <h2>回复主题</h2>
    </div>
    <div class="card-body padded">
        <div class="bg-hover border rounded p-lg mb-lg flex items-center gap-md">
            <span class="badge <?php echo isset($thread['status']) && $thread['status'] == 1 ? 'badge-green' : 'badge-red'; ?>"><?php echo isset($thread['status']) && $thread['status'] == 1 ? '开放' : '关闭'; ?></span>
            <div class="font-bold flex-1">
                <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>"><?php echo htmlspecialchars($thread['subject']); ?></a>
            </div>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post" action="index.php?c=thread&a=reply&tid=<?php echo $thread['tid']; ?>">
            <div class="form-group">
                <label for="message">回复内容</label>
                <textarea id="message" name="message" class="message-editor" placeholder="请输入回复内容..." required></textarea>
            </div>
            <div class="flex justify-end gap-md mt-lg">
                <a href="index.php?c=thread&a=index&tid=<?php echo $thread['tid']; ?>" class="btn btn-secondary">取消</a>
                <button type="submit" class="btn btn-primary">提交回复</button>
            </div>
        </form>
    </div>
</div>
