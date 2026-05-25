<div class="max-w-xl mx-auto bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <h2 class="font-semibold">发送私信</h2>
    </div>
    <div class="p-4">
        <?php if (!empty($template_error)): ?>
            <div class="p-3 rounded bg-danger-light text-danger mb-4 text-sm"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="to_uid" value="<?php echo intval($template_toUid); ?>">

            <div class="mb-4 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">收件人</label>
                <?php if (!empty($template_receiver)): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold text-sm">
                            <?php echo \Lib\Helper::getAvatarInitial($template_receiver['username']); ?>
                        </div>
                        <span class="font-semibold"><?php echo htmlspecialchars($template_receiver['username']); ?></span>
                    </div>
                <?php else: ?>
                    <select name="to_uid" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" required>
                        <option value="">请选择收件人</option>
                        <?php foreach ($template_members as $m): ?>
                            <option value="<?php echo $m['uid']; ?>"><?php echo htmlspecialchars($m['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="mb-6 flex flex-col gap-1.5">
                <label class="text-sm font-medium text-text">消息内容</label>
                <textarea name="content" class="w-full h-auto min-h-40 p-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary resize-y" required placeholder="请输入私信内容..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">发送私信</button>
                <a href="index.php?c=pm&a=inbox" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-soft border-border text-text hover:bg-hover">取消</a>
            </div>
        </form>
    </div>
</div>
