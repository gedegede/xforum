<div class="card center-card">
    <div class="thread-hero">
        <h2>发送私信</h2>
    </div>
    <div class="card-body padded">
        <?php if (!empty($template_error)): ?>
            <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="to_uid" value="<?php echo intval($template_toUid); ?>">
            
            <div class="form-group">
                <label>收件人</label>
                <?php if (!empty($template_receiver)): ?>
                    <div class="flex items-center gap-md">
                        <div class="avatar avatar-sm"><?php echo \Lib\Helper::getAvatarInitial($template_receiver['username']); ?></div>
                        <span class="font-bold"><?php echo htmlspecialchars($template_receiver['username']); ?></span>
                    </div>
                <?php else: ?>
                    <select name="to_uid" required>
                        <option value="">请选择收件人</option>
                        <?php foreach ($template_members as $m): ?>
                            <option value="<?php echo $m['uid']; ?>"><?php echo htmlspecialchars($m['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>消息内容</label>
                <textarea name="content" required placeholder="请输入私信内容..."></textarea>
            </div>

            <div class="flex justify-end gap-md mt-lg">
                <button type="submit" class="btn btn-primary">发送私信</button>
                <a href="index.php?c=pm&a=inbox" class="btn btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>
