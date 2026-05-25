<div class="bg-panel border border-border rounded shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border">
        <h2 class="font-semibold">发布成功</h2>
    </div>
    <div class="p-4">
        <div class="text-center py-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success-light text-success mb-4">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($template_message); ?></h3>
            <p class="text-muted mb-6">您的主题已提交，等待管理员审核后会显示在论坛中。</p>
            <a href="index.php" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">返回首页</a>
        </div>
    </div>
</div>