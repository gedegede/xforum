<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>站点设置</h2>
    </div>
    <div class="card-body padded">
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>网站名称</label>
                <input type="text" name="setting_site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'XForum'); ?>">
            </div>
            <div class="form-group">
                <label>网站描述</label>
                <input type="text" name="setting_site_desc" value="<?php echo htmlspecialchars($settings['site_desc'] ?? '一个现代化的论坛系统'); ?>">
            </div>
            <div class="form-group">
                <label>关键词</label>
                <input type="text" name="setting_site_keywords" value="<?php echo htmlspecialchars($settings['site_keywords'] ?? '论坛,社区,讨论'); ?>">
            </div>
            <div class="flex justify-end mt-lg">
                <button type="submit" class="btn btn-primary">保存设置</button>
            </div>
        </form>
    </div>
</div>
