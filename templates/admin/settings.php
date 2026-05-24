<?php include '_menu.php'; ?>

<div class="card">
 <div class="card-header section">
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

 <form method="post">
 <div class="form-section">
 <div class="form-section-title">基础信息</div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="setting_site_name">网站名称</label>
 <p class="muted">显示在页面标题、导航与站点标识中。</p>
 </div>
 <div class="form-control">
 <input type="text" id="setting_site_name" name="setting_site_name" value="<?php echo htmlspecialchars($template_settings['site_name'] ?? 'XForum'); ?>" placeholder="XForum">
 </div>
 </div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="setting_site_desc">网站描述</label>
 <p class="muted">用于概括社区定位，可展示在首页和 SEO 摘要中。</p>
 </div>
 <div class="form-control">
 <input type="text" id="setting_site_desc" name="setting_site_desc" value="<?php echo htmlspecialchars($template_settings['site_desc'] ?? '一个现代化的论坛系统'); ?>" placeholder="一个现代化的论坛系统">
 </div>
 </div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="setting_site_keywords">关键词</label>
 <p class="muted">多个关键词用逗号分隔。</p>
 </div>
 <div class="form-control">
 <input type="text" id="setting_site_keywords" name="setting_site_keywords" value="<?php echo htmlspecialchars($template_settings['site_keywords'] ?? '论坛,社区,讨论'); ?>" placeholder="论坛,社区,讨论">
 </div>
 </div>
 </div>

 <div class="form-section">
 <div class="form-section-title">版块展示</div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="setting_notice_forum_fid">社区公告版块</label>
 <p class="muted">该版块的话题会显示在首页右侧公告栏中。</p>
 </div>
 <div class="form-control">
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
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="setting_report_forum_fid">举报版块</label>
 <p class="muted">用户举报的内容将自动发布到该版块。</p>
 </div>
 <div class="form-control">
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
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted">折叠版块</label>
 <p class="muted">选择后，这些版块的主题不会在首页展开显示。</p>
 </div>
 <div class="form-control">
 <input type="hidden" name="setting_collapsed_fids[]" value="">
 <div class="checkbox-group">
 <?php
 $collapsedFids = !empty($template_settings['collapsed_fids']) ? explode(',', $template_settings['collapsed_fids']) : [];
 foreach ($template_forums as $forum):  ?>
 <label class="checkbox-item">
 <input type="checkbox" name="setting_collapsed_fids[]" value="<?php echo $forum['fid']; ?>" <?php echo in_array((string)$forum['fid'], $collapsedFids) ? 'checked' : ''; ?>>
 <span><?php echo htmlspecialchars($forum['name']); ?></span>
 </label>
 <?php endforeach; ?>
 </div>
 </div>
 </div>
 </div>

 <div class="form-section">
 <div class="form-section-title">内容风控</div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="setting_approve_keywords">审核关键词</label>
 <p class="muted">命中这些关键词的主题或回帖需要审核，支持每行一个或用逗号分隔。</p>
 </div>
 <div class="form-control">
 <textarea id="setting_approve_keywords" name="setting_approve_keywords"  rows="5" placeholder="例如：&#10;敏感词一&#10;敏感词二"><?php echo htmlspecialchars($template_settings['approve_keywords'] ?? ''); ?></textarea>
 </div>
 </div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="setting_block_keywords">禁止关键词</label>
 <p class="muted">命中这些关键词的主题或回帖会被禁止发布，支持每行一个或用逗号分隔。</p>
 </div>
 <div class="form-control">
 <textarea id="setting_block_keywords" name="setting_block_keywords"  rows="5" placeholder="例如：&#10;广告词一&#10;广告词二"><?php echo htmlspecialchars($template_settings['block_keywords'] ?? ''); ?></textarea>
 </div>
 </div>
 </div>

 <div class="form-section">
 <div class="form-section-title">金币规则</div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <div class="muted">每日签到金币</div>
 <p class="muted">设置签到时随机获得的金币范围；每天只能签到一次。</p>
 </div>
 <div class="form-control">
 <div class="grid grid-cols-2 gap-sm">
 <label class="box">
 <span class="muted">最低获得</span>
 <input type="number" name="signin_credit_min" value="<?php echo (int)($template_signinRange[0] ?? 1); ?>" min="0" step="1">
 </label>
 <label class="box">
 <span class="muted">最高获得</span>
 <input type="number" name="signin_credit_max" value="<?php echo (int)($template_signinRange[1] ?? 5); ?>" min="0" step="1">
 </label>
 </div>
 </div>
 </div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <div class="muted">动作金币规则</div>
 <p class="muted">直接配置每个行为的金币变化。正数为奖励，负数为消耗；每日上限只限制奖励。</p>
 </div>
 <div class="form-control">
 <div class="data-grid">
 <div class="data-grid-head" aria-hidden="true">
 <span>行为</span>
 <span>状态</span>
 <span>单次变化</span>
 <span>每日奖励上限</span>
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
 <div class="data-grid-row">
 <div class="flex-1 min-width-0">
 <strong><?php echo htmlspecialchars($label); ?></strong>
 <span class="muted"><?php echo htmlspecialchars($action); ?></span>
 </div>
 <label class="checkbox-item">
 <input type="checkbox" name="credit_rule_enabled[<?php echo htmlspecialchars($action); ?>]" value="1" <?php echo $enabled ? 'checked' : ''; ?>>
 <span>启用</span>
 </label>
 <label class="box">
 <span class="muted">金币</span>
 <input type="number" name="credit_rule_credit[<?php echo htmlspecialchars($action); ?>]" value="<?php echo $credit; ?>" step="1">
 </label>
 <label class="box">
 <span class="muted">上限</span>
 <input type="number" name="credit_rule_daily_max[<?php echo htmlspecialchars($action); ?>]" value="<?php echo $dailyMax; ?>" min="0" step="1">
 </label>
 </div>
 <?php endforeach; ?>
 </div>
 </div>
 </div>
 </div>

 <div class="actions">
 <button type="submit" class="btn btn-primary">保存设置</button>
 </div>
 </form>
 </div>
</div>
