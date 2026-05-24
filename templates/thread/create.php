<div class="card">
    <div class="thread-header">
        <div class="breadcrumb">
            <a href="index.php">首页</a>
            <span>/</span>
            <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>"><?php echo htmlspecialchars($template_forum['name']); ?></a>
            <span>/</span>
            <span>发布主题</span>
        </div>
        <div class="mt-sm">
            <h1>在「<?php echo htmlspecialchars($template_forum['name']); ?>」发布内容</h1>
        </div>
    </div>

    <div class="card-body padded">

        <?php if (!empty($template_error)): ?>
            <div class="error">
                <?php echo htmlspecialchars($template_error); ?>
            </div>
        <?php endif; ?>

        <form method="post"
            action="index.php?c=thread&a=create&fid=<?php echo $template_forum['fid']; ?>">

            <div class="form-group">
                <label for="subject">标题</label>

                <input type="text"
                    id="subject"
                    name="subject"
                    placeholder="用一句清晰的话概括你的问题或观点"
                    required>
            </div>

            <div class="form-group">
                <label for="message">内容</label>

                <textarea id="message"
                    name="message"
                    class="message-editor"
                    placeholder="支持 Markdown。建议补充背景、现象、已尝试方案和预期结果。"></textarea>
            </div>

            <div class="flex justify-end gap-md mt-lg">
                <a href="index.php?c=forum&a=index&fid=<?php echo $template_forum['fid']; ?>"
                    class="btn btn-secondary">
                    取消
                </a>

                <button type="submit"
                    class="btn btn-primary">
                    发布主题
                </button>
            </div>
        </form>

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
