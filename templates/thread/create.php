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
                    placeholder=""
                    required>
            </div>

            <div class="form-group">
                <label for="message">内容</label>

                <textarea id="message"
                    name="message"
                    class="message-editor"
                    placeholder=""></textarea>
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

