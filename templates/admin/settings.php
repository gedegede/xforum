<?php include '_menu.php'; ?>

<div class="card settings-card">
    <div class="card-header settings-header">
        <div>
            <h2>站点设置</h2>
            <p>集中管理社区名称、首页展示与内容风控规则。</p>
        </div>
    </div>
    <div class="card-body padded">
        <?php if ($template_error): ?>
            <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>
        <?php if ($template_success): ?>
            <div class="success"><?php echo htmlspecialchars($template_success); ?></div>
        <?php endif; ?>

        <form method="post" class="settings-form">
            <div class="settings-section">
                <div class="settings-section-title">基础信息</div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="setting_site_name">网站名称</label>
                        <p class="help-text">显示在页面标题、导航与站点标识中。</p>
                    </div>
                    <div class="setting-control">
                        <input type="text" id="setting_site_name" name="setting_site_name" value="<?php echo htmlspecialchars($template_settings['site_name'] ?? 'XForum'); ?>" placeholder="XForum">
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="setting_site_desc">网站描述</label>
                        <p class="help-text">用于概括社区定位，可展示在首页和 SEO 摘要中。</p>
                    </div>
                    <div class="setting-control">
                        <input type="text" id="setting_site_desc" name="setting_site_desc" value="<?php echo htmlspecialchars($template_settings['site_desc'] ?? '一个现代化的论坛系统'); ?>" placeholder="一个现代化的论坛系统">
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="setting_site_keywords">关键词</label>
                        <p class="help-text">多个关键词用逗号分隔。</p>
                    </div>
                    <div class="setting-control">
                        <input type="text" id="setting_site_keywords" name="setting_site_keywords" value="<?php echo htmlspecialchars($template_settings['site_keywords'] ?? '论坛,社区,讨论'); ?>" placeholder="论坛,社区,讨论">
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <div class="settings-section-title">版块展示</div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="setting_notice_forum_fid">社区公告版块</label>
                        <p class="help-text">该版块的话题会显示在首页右侧公告栏中。</p>
                    </div>
                    <div class="setting-control">
                        <select id="setting_notice_forum_fid" name="setting_notice_forum_fid">
                            <option value="0">请选择版块</option>
                            <?php foreach ($template_forums as $forum): ?>
                                <option value="<?php echo $forum['fid']; ?>" <?php echo (int)($template_settings['notice_forum_fid'] ?? 0) === $forum['fid'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($forum['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="setting_report_forum_fid">举报版块</label>
                        <p class="help-text">用户举报的内容将自动发布到该版块。</p>
                    </div>
                    <div class="setting-control">
                        <select id="setting_report_forum_fid" name="setting_report_forum_fid">
                            <option value="0">请选择版块</option>
                            <?php foreach ($template_forums as $forum): ?>
                                <option value="<?php echo $forum['fid']; ?>" <?php echo (int)($template_settings['report_forum_fid'] ?? 0) === $forum['fid'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($forum['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label">折叠版块</label>
                        <p class="help-text">选择后，这些版块的主题不会在首页展开显示。</p>
                    </div>
                    <div class="setting-control">
                        <input type="hidden" name="setting_collapsed_fids[]" value="">
                        <div class="checkbox-group settings-checkbox-group">
                            <?php
                            $collapsedFids = !empty($template_settings['collapsed_fids']) ? explode(',', $template_settings['collapsed_fids']) : [];
                            foreach ($template_forums as $forum): 
                            ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="setting_collapsed_fids[]" value="<?php echo $forum['fid']; ?>" <?php echo in_array((string)$forum['fid'], $collapsedFids) ? 'checked' : ''; ?>>
                                    <span><?php echo htmlspecialchars($forum['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <div class="settings-section-title">内容风控</div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="setting_approve_keywords">审核关键词</label>
                        <p class="help-text">命中这些关键词的主题或回帖需要审核，支持每行一个或用逗号分隔。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_approve_keywords" name="setting_approve_keywords" class="setting-textarea" rows="5" placeholder="例如：&#10;敏感词一&#10;敏感词二"><?php echo htmlspecialchars($template_settings['approve_keywords'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-meta">
                        <label class="setting-label" for="setting_block_keywords">禁止关键词</label>
                        <p class="help-text">命中这些关键词的主题或回帖会被禁止发布，支持每行一个或用逗号分隔。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_block_keywords" name="setting_block_keywords" class="setting-textarea" rows="5" placeholder="例如：&#10;广告词一&#10;广告词二"><?php echo htmlspecialchars($template_settings['block_keywords'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="settings-actions">
                <button type="submit" class="btn btn-primary">保存设置</button>
            </div>
        </form>
    </div>
</div>
