<?php include __DIR__ . '/../member/_profile_header.php'; ?>
<?php include __DIR__ . '/../member/_profile_nav.php'; ?>

<div class="max-w-xl mx-auto card">
    <div class="card-header">
        <h2 class="font-semibold">发送私信</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($template_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-field">
                <label class="form-label" for="username">收件人用户名</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($template_username ?? ''); ?>" required>
            </div>

            <div class="form-field form-field-lg">
                <label class="form-label">消息内容</label>
                <textarea name="content" class="form-control min-h-40" required placeholder="请输入私信内容..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">发送私信</button>
                <a href="index.php?c=pm&a=inbox" class="btn btn-soft">取消</a>
            </div>
        </form>
    </div>
</div>
