<div class="grid grid-cols-3">
    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h2>编辑帖子</h2>
            </div>
            <div class="card-body padded">
                <?php if (!empty($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="post" action="index.php?c=thread&a=edit&pid=<?php echo $post['pid']; ?>">
                    <div class="form-group">
                        <label for="message">内容</label>
                        
                        <textarea id="message"
                            name="message"
                            class="message-editor"
                            placeholder="支持 Markdown 语法..."><?php echo htmlspecialchars($post['message']); ?></textarea>
                    </div>
                    <div class="flex justify-end gap-md mt-lg">
                        <a href="index.php?c=thread&a=index&tid=<?php echo $post['tid']; ?>" class="btn btn-secondary">取消</a>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.css">
<script src="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const simplemde = new SimpleMDE({
        element: document.getElementById('message'),
        toolbar: [
            'bold', 'italic', 'strikethrough', '|',
            'heading-1', 'heading-2', 'heading-3', '|',
            'code', 'quote', '|',
            'unordered-list', 'ordered-list', '|',
            'link', 'image', '|',
            'preview', '|',
            'guide'
        ],
        status: false,
        placeholder: '支持 Markdown 语法...'
    });
});
</script>