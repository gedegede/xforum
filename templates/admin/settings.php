<?php include '_menu.php'; ?>

<div class="bg-panel border border-border rounded shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-border bg-soft">
        <h2 class="font-semibold">站点设置</h2>
    </div>
    <div class="p-4">
        <?php if ($template_error): ?>
            <div class="p-3 rounded bg-danger-light text-danger mb-4 text-sm"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>
        <?php if ($template_success): ?>
            <div class="p-3 rounded bg-success-light text-success mb-4 text-sm"><?php echo htmlspecialchars($template_success); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-8">
                <div class="text-sm font-semibold text-text mb-4">基础信息</div>

                <div class="flex flex-col lg:flex-row gap-1 mb-6">
                    <div class="flex-1 min-w-0">
                        <label class="text-sm text-muted block mb-1" for="setting_site_name">网站名称</label>
                        <p class="text-sm text-muted mb-2">显示在页面标题、导航与站点标识中。</p>
                    </div>
                    <div class="lg:w-96">
                        <input type="text" id="setting_site_name" name="setting_site_name" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary w-full" value="<?php echo htmlspecialchars($template_settings['site_name'] ?? 'XForum'); ?>" placeholder="XForum">
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-1 mb-6">
                    <div class="flex-1 min-w-0">
                        <label class="text-sm text-muted block mb-1" for="setting_site_desc">网站描述</label>
                        <p class="text-sm text-muted mb-2">用于概括社区定位，可展示在首页和 SEO 摘要中。</p>
                    </div>
                    <div class="lg:w-96">
                        <input type="text" id="setting_site_desc" name="setting_site_desc" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary w-full" value="<?php echo htmlspecialchars($template_settings['site_desc'] ?? '一个现代化的论坛系统'); ?>" placeholder="一个现代化的论坛系统">
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-1">
                    <div class="flex-1 min-w-0">
                        <label class="text-sm text-muted block mb-1" for="setting_site_keywords">关键词</label>
                        <p class="text-sm text-muted mb-2">多个关键词用逗号分隔。</p>
                    </div>
                    <div class="lg:w-96">
                        <input type="text" id="setting_site_keywords" name="setting_site_keywords" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary w-full" value="<?php echo htmlspecialchars($template_settings['site_keywords'] ?? '论坛,社区,讨论'); ?>" placeholder="论坛,社区,讨论">
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <div class="text-sm font-semibold text-text mb-4">版块展示</div>

                <div class="flex flex-col lg:flex-row gap-1 mb-6">
                    <div class="flex-1 min-w-0">
                        <label class="text-sm text-muted block mb-1" for="setting_notice_forum_fid">社区公告版块</label>
                        <p class="text-sm text-muted mb-2">该版块的话题会显示在首页右侧公告栏中。</p>
                    </div>
                    <div class="lg:w-96">
                        <select id="setting_notice_forum_fid" name="setting_notice_forum_fid" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary w-full">
                            <option value="0">请选择版块</option>
                            <?php foreach ($template_forums as $forum): ?>
                            <option value="<?php echo $forum['fid']; ?>" <?php echo (int)($template_settings['notice_forum_fid'] ?? 0) === $forum['fid'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($forum['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-1 mb-6">
                    <div class="flex-1 min-w-0">
                        <label class="text-sm text-muted block mb-1" for="setting_report_forum_fid">举报版块</label>
                        <p class="text-sm text-muted mb-2">用户举报的内容将自动发布到该版块。</p>
                    </div>
                    <div class="lg:w-96">
                        <select id="setting_report_forum_fid" name="setting_report_forum_fid" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary w-full">
                            <option value="0">请选择版块</option>
                            <?php foreach ($template_forums as $forum): ?>
                            <option value="<?php echo $forum['fid']; ?>" <?php echo (int)($template_settings['report_forum_fid'] ?? 0) === $forum['fid'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($forum['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-1">
                    <div class="flex-1 min-w-0">
                        <label class="text-sm text-muted block mb-1">折叠版块</label>
                        <p class="text-sm text-muted mb-2">选择后，这些版块的主题不会在首页展开显示。</p>
                    </div>
                    <div class="lg:w-96">
                        <input type="hidden" name="setting_collapsed_fids[]" value="">
                        <div class="flex flex-wrap gap-3">
                            <?php
                            $collapsedFids = !empty($template_settings['collapsed_fids']) ? explode(',', $template_settings['collapsed_fids']) : [];
                            foreach ($template_forums as $forum):  ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="setting_collapsed_fids[]" value="<?php echo $forum['fid']; ?>" class="rounded" <?php echo in_array((string)$forum['fid'], $collapsedFids) ? 'checked' : ''; ?>>
                                <span class="text-sm"><?php echo htmlspecialchars($forum['name']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <div class="text-sm font-semibold text-text mb-4">内容风控</div>

                <div class="flex flex-col lg:flex-row gap-1 mb-6">
                    <div class="flex-1 min-w-0">
                        <label class="text-sm text-muted block mb-1" for="setting_approve_keywords">审核关键词</label>
                        <p class="text-sm text-muted mb-2">命中这些关键词的主题或回帖需要审核，支持每行一个或用逗号分隔。</p>
                    </div>
                    <div class="lg:w-96">
                        <textarea id="setting_approve_keywords" name="setting_approve_keywords" class="w-full px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary w-full" rows="5" placeholder="例如：&#10;敏感词一&#10;敏感词二"><?php echo htmlspecialchars($template_settings['approve_keywords'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-1">
                    <div class="flex-1 min-w-0">
                        <label class="text-sm text-muted block mb-1" for="setting_block_keywords">禁止关键词</label>
                        <p class="text-sm text-muted mb-2">命中这些关键词的主题或回帖会被禁止发布，支持每行一个或用逗号分隔。</p>
                    </div>
                    <div class="lg:w-96">
                        <textarea id="setting_block_keywords" name="setting_block_keywords" class="w-full px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary w-full" rows="5" placeholder="例如：&#10;广告词一&#10;广告词二"><?php echo htmlspecialchars($template_settings['block_keywords'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <div class="text-sm font-semibold text-text mb-4">金币规则</div>

                <div class="flex flex-col lg:flex-row gap-1 mb-6">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-muted block mb-1">每日签到金币</div>
                        <p class="text-sm text-muted mb-2">设置签到时随机获得的金币范围；每天只能签到一次。</p>
                    </div>
                    <div class="lg:w-96">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm text-muted">最低获得</span>
                                <input type="number" name="signin_credit_min" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" value="<?php echo (int)($template_signinRange[0] ?? 1); ?>" min="0" step="1">
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm text-muted">最高获得</span>
                                <input type="number" name="signin_credit_max" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary" value="<?php echo (int)($template_signinRange[1] ?? 5); ?>" min="0" step="1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-1">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-muted block mb-1">动作金币规则</div>
                        <p class="text-sm text-muted mb-2">直接配置每个行为的金币变化。正数为奖励，负数为消耗；每日上限只限制奖励。</p>
                    </div>
                    <div class="lg:w-96">
                        <div class="border rounded-lg overflow-hidden">
                            <div class="flex bg-soft px-3 py-2 text-xs font-medium text-muted">
                                <span class="flex-1">行为</span>
                                <span class="w-16 text-center">状态</span>
                                <span class="w-20 text-center">单次变化</span>
                                <span class="w-24 text-center">每日上限</span>
                            </div>
                            <?php foreach ($template_creditActionLabels as $action => $label): ?>
                            <?php
                            if ($action === 'Signin') {
                                continue;
                            }
                            $rule = $template_creditRules[$action] ?? ['credit' => 0, 'daily_max' => 0];
                            $credit = (int)($rule['credit'] ?? 0);
                            $dailyMax = max(0, (int)($rule['daily_max'] ?? 0));
                            $enabled = $credit !== 0 || $dailyMax > 0;
                            ?>
                            <div class="flex items-center px-3 py-2 border-t border-border gap-1">
                                <div class="flex-1 min-w-0">
                                    <strong class="text-sm"><?php echo htmlspecialchars($label); ?></strong>
                                </div>
                                <label class="w-16 flex justify-center cursor-pointer">
                                    <input type="checkbox" name="credit_rule_enabled[<?php echo htmlspecialchars($action); ?>]" value="1" class="rounded" <?php echo $enabled ? 'checked' : ''; ?>>
                                </label>
                                <div class="w-20">
                                    <input type="number" name="credit_rule_credit[<?php echo htmlspecialchars($action); ?>]" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary text-center" value="<?php echo $credit; ?>" step="1">
                                </div>
                                <div class="w-24">
                                    <input type="number" name="credit_rule_daily_max[<?php echo htmlspecialchars($action); ?>]" class="w-full h-control px-3 border border-border rounded bg-panel text-text text-base transition-colors focus:outline-none focus:border-primary text-center" value="<?php echo $dailyMax; ?>" min="0" step="1">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed bg-primary border-primary text-white hover:bg-primary-dark">保存设置</button>
            </div>
        </form>
    </div>
</div>