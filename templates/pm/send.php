<div class="max-w-xl mx-auto card">
    <div class="card-header">
        <h2 class="font-semibold">发送私信</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($template_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="to_uid" value="<?php echo intval($template_toUid); ?>">

            <div class="form-field">
                <label class="form-label">收件人</label>
                <?php if (!empty($template_receiver)): ?>
                    <div class="flex items-center gap-3">
                        <div class="avatar avatar-md text-primary">
                            <?php echo \Lib\Helper::getAvatarInitial($template_receiver['username']); ?>
                        </div>
                        <span class="font-semibold"><?php echo htmlspecialchars($template_receiver['username']); ?></span>
                    </div>
                <?php else: ?>
                    <select name="to_uid" class="form-control" required>
                        <option value="">请选择收件人</option>
                        <?php foreach ($template_members as $m): ?>
                            <option value="<?php echo $m['uid']; ?>"><?php echo htmlspecialchars($m['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
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
