<?php include '_menu.php'; ?>

<div class="card card-clip">
    <div class="card-header">
        <h2 class="font-semibold">站点设置</h2>
    </div>
    <div class="card-body">
        <?php if ($template_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($template_error); ?></div>
        <?php endif; ?>
        <?php if ($template_success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($template_success); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="setting-section">
                <div class="setting-section-title">基础信息</div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_site_name">网站名称</label>
                        <p class="setting-help">显示在页面标题、导航与站点标识中。</p>
                    </div>
                    <div class="setting-control">
                        <input type="text" id="setting_site_name" name="setting_site_name" class="form-control w-full" value="<?php echo htmlspecialchars($template_settings['site_name'] ?? 'XForum'); ?>" placeholder="XForum">
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_site_desc">网站描述</label>
                        <p class="setting-help">用于概括社区定位，可展示在首页和 SEO 摘要中。</p>
                    </div>
                    <div class="setting-control">
                        <input type="text" id="setting_site_desc" name="setting_site_desc" class="form-control w-full" value="<?php echo htmlspecialchars($template_settings['site_desc'] ?? '一个现代化的论坛系统'); ?>" placeholder="一个现代化的论坛系统">
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_site_keywords">关键词</label>
                        <p class="setting-help">多个关键词用逗号分隔。</p>
                    </div>
                    <div class="setting-control">
                        <input type="text" id="setting_site_keywords" name="setting_site_keywords" class="form-control w-full" value="<?php echo htmlspecialchars($template_settings['site_keywords'] ?? '论坛,社区,讨论'); ?>" placeholder="论坛,社区,讨论">
                    </div>
                </div>

                <div class="setting-row setting-row-last">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_timezone">默认时区</label>
                        <p class="setting-help">影响站点显示时间、后台系统时间和日期格式。</p>
                    </div>
                    <div class="setting-control">
                        <select id="setting_timezone" name="setting_timezone" class="form-control w-full">
                            <?php foreach ($template_timezoneOptions as $timezone): ?>
                            <option value="<?php echo htmlspecialchars($timezone); ?>" <?php echo $template_timezone === $timezone ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($timezone); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="setting-section">
                <div class="setting-section-title">自定义代码</div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_head_code">头部代码</label>
                        <p class="setting-help">插入到页面&lt;head&gt;&lt;/head&gt;部分，支持HTML。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_head_code" name="setting_head_code" class="form-control w-full" rows="4" placeholder="例如：&lt;script&gt;...&lt;/script&gt;"><?php echo htmlspecialchars($template_settings['head_code'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="setting-row setting-row-last">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_footer_code">页脚代码</label>
                        <p class="setting-help">插入到页面&lt;footer&gt;&lt;/footer&gt;部分，支持HTML。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_footer_code" name="setting_footer_code" class="form-control w-full" rows="4" placeholder="例如：&lt;script&gt;...&lt;/script&gt;"><?php echo htmlspecialchars($template_settings['footer_code'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="setting-section">
                <div class="setting-section-title">站点状态</div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label">站点是否关闭</label>
                        <p class="setting-help">关闭后普通用户将无法访问站点。</p>
                    </div>
                    <div class="setting-control">
                        <label class="check-row">
                            <input type="hidden" name="setting_site_closed" value="0">
                            <input type="checkbox" name="setting_site_closed" value="1" class="rounded" <?php echo !empty($template_settings['site_closed']) ? 'checked' : ''; ?>>
                            <span class="text-sm">关闭站点</span>
                        </label>
                    </div>
                </div>

                <div class="setting-row setting-row-last">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_site_closed_reason">关闭理由</label>
                        <p class="setting-help">向用户展示的站点关闭原因。</p>
                    </div>
                    <div class="setting-control">
                        <input type="text" id="setting_site_closed_reason" name="setting_site_closed_reason" class="form-control w-full" value="<?php echo htmlspecialchars($template_settings['site_closed_reason'] ?? ''); ?>" placeholder="站点维护中，敬请期待">
                    </div>
                </div>
            </div>

            <div class="setting-section">
                <div class="setting-section-title">注册设置</div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label">关闭注册</label>
                        <p class="setting-help">关闭后新用户无法注册账号。</p>
                    </div>
                    <div class="setting-control">
                        <label class="check-row">
                            <input type="hidden" name="setting_close_register" value="0">
                            <input type="checkbox" name="setting_close_register" value="1" class="rounded" <?php echo !empty($template_settings['close_register']) ? 'checked' : ''; ?>>
                            <span class="text-sm">关闭注册</span>
                        </label>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_register_default_gid">新注册用户默认用户组</label>
                        <p class="setting-help">新用户注册成功后会自动加入该用户组，具备管理权限的用户组不会用于注册。</p>
                    </div>
                    <div class="setting-control">
                        <select id="setting_register_default_gid" name="setting_register_default_gid" class="form-control w-full">
                            <?php $registerDefaultGid = (int)($template_settings['register_default_gid'] ?? 2); ?>
                            <?php foreach ($template_usergroups as $group): ?>
                            <?php if (!\Models\UsergroupModel::canAssignOnRegister($group)) continue; ?>
                            <option value="<?php echo $group['gid']; ?>" <?php echo $registerDefaultGid === (int)$group['gid'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($group['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_username_reserved">用户名保留关键字</label>
                        <p class="setting-help">禁止使用的用户名关键词，每行一个。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_username_reserved" name="setting_username_reserved" class="form-control w-full" rows="3" placeholder="admin&#10;administrator&#10;root"><?php echo htmlspecialchars($template_settings['username_reserved'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_allowed_email_domains">允许使用的邮箱域名后缀</label>
                        <p class="setting-help">限定注册邮箱的域名，每行一个，留空则不限制。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_allowed_email_domains" name="setting_allowed_email_domains" class="form-control w-full" rows="3" placeholder="qq.com&#10;163.com&#10;gmail.com"><?php echo htmlspecialchars($template_settings['allowed_email_domains'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_register_ip_interval">同一IP注册间隔限制(秒)</label>
                        <p class="setting-help">防止恶意注册，0表示不限制。</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" id="setting_register_ip_interval" name="setting_register_ip_interval" class="form-control w-full" value="<?php echo (int)($template_settings['register_ip_interval'] ?? 3600); ?>" min="0" placeholder="3600">
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_close_register_bypass_ips">关闭注册时允许注册的IP</label>
                        <p class="setting-help">即使关闭注册，这些IP仍然可以注册。每行一个，支持*通配符。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_close_register_bypass_ips" name="setting_close_register_bypass_ips" class="form-control w-full" rows="3" placeholder="192.168.1.*&#10;10.0.0.*"><?php echo htmlspecialchars($template_settings['close_register_bypass_ips'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="setting-row setting-row-last">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_block_register_ips">禁止注册的IP列表</label>
                        <p class="setting-help">每行一个IP，支持*通配符，如192.168.*.*。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_block_register_ips" name="setting_block_register_ips" class="form-control w-full" rows="3" placeholder="192.168.1.100&#10;10.0.0.50"><?php echo htmlspecialchars($template_settings['block_register_ips'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="setting-section">
                <div class="setting-section-title">安全设置</div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_login_ip_interval">同一IP密码尝试间隔限制(秒)</label>
                        <p class="setting-help">防止暴力破解密码。</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" id="setting_login_ip_interval" name="setting_login_ip_interval" class="form-control w-full" value="<?php echo (int)($template_settings['login_ip_interval'] ?? 5); ?>" min="0" placeholder="5">
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_login_max_fail">同一IP每24小时失败登录次数限制</label>
                        <p class="setting-help">0表示不限制。</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" id="setting_login_max_fail" name="setting_login_max_fail" class="form-control w-full" value="<?php echo (int)($template_settings['login_max_fail'] ?? 10); ?>" min="0" placeholder="10">
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_allow_access_ips">允许访问论坛的IP列表</label>
                        <p class="setting-help">每行一个IP，支持*通配符，留空则不限制。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_allow_access_ips" name="setting_allow_access_ips" class="form-control w-full" rows="3" placeholder="192.168.1.*&#10;10.0.0.*"><?php echo htmlspecialchars($template_settings['allow_access_ips'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_block_access_ips">禁止访问论坛的IP列表</label>
                        <p class="setting-help">每行一个IP，支持*通配符。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_block_access_ips" name="setting_block_access_ips" class="form-control w-full" rows="3" placeholder="192.168.1.100&#10;10.0.0.50"><?php echo htmlspecialchars($template_settings['block_access_ips'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="setting-row setting-row-last">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_block_access_time">禁止访问时间段</label>
                        <p class="setting-help">格式: HH:MM-HH:MM，如23:00-06:00</p>
                    </div>
                    <div class="setting-control">
                        <input type="text" id="setting_block_access_time" name="setting_block_access_time" class="form-control w-full" value="<?php echo htmlspecialchars($template_settings['block_access_time'] ?? ''); ?>" placeholder="23:00-06:00">
                    </div>
                </div>
            </div>

            <div class="setting-section">
                <div class="setting-section-title">发帖与互动</div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_newbie_wait_hours">新手发帖见习期限(小时)</label>
                        <p class="setting-help">新注册用户需要等待多少小时才能发帖，0表示不限制。</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" id="setting_newbie_wait_hours" name="setting_newbie_wait_hours" class="form-control w-full" value="<?php echo (int)($template_settings['newbie_wait_hours'] ?? 0); ?>" min="0" placeholder="0">
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_post_interval">发帖灌水预防(秒)</label>
                        <p class="setting-help">用户两次发帖之间的最小时间间隔。</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" id="setting_post_interval" name="setting_post_interval" class="form-control w-full" value="<?php echo (int)($template_settings['post_interval'] ?? 30); ?>" min="0" placeholder="30">
                    </div>
                </div>

                <div class="setting-row setting-row-last">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_search_interval">两次搜索时间间隔(秒)</label>
                        <p class="setting-help">防止频繁搜索影响性能。</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" id="setting_search_interval" name="setting_search_interval" class="form-control w-full" value="<?php echo (int)($template_settings['search_interval'] ?? 10); ?>" min="0" placeholder="10">
                    </div>
                </div>
            </div>

            <div class="setting-section">
                <div class="setting-section-title">显示设置</div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_threads_per_page">每页显示主题数</label>
                        <p class="setting-help">论坛列表每页显示的主题数量。</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" id="setting_threads_per_page" name="setting_threads_per_page" class="form-control w-full" value="<?php echo (int)($template_settings['threads_per_page'] ?? 20); ?>" min="1" max="100" placeholder="20">
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_posts_per_page">每页显示回帖数</label>
                        <p class="setting-help">主题详情页每页显示的回帖数量。</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" id="setting_posts_per_page" name="setting_posts_per_page" class="form-control w-full" value="<?php echo (int)($template_settings['posts_per_page'] ?? 20); ?>" min="1" max="100" placeholder="20">
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_users_per_page">每页显示会员数</label>
                        <p class="setting-help">会员列表每页显示的用户数量。</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" id="setting_users_per_page" name="setting_users_per_page" class="form-control w-full" value="<?php echo (int)($template_settings['users_per_page'] ?? 20); ?>" min="1" max="100" placeholder="20">
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_date_format">默认时间格式</label>
                        <p class="setting-help">PHP日期格式，如Y-m-d H:i:s</p>
                    </div>
                    <div class="setting-control">
                        <input type="text" id="setting_date_format" name="setting_date_format" class="form-control w-full" value="<?php echo htmlspecialchars($template_settings['date_format'] ?? 'Y-m-d H:i:s'); ?>" placeholder="Y-m-d H:i:s">
                    </div>
                </div>

                <div class="setting-row setting-row-last">
                    <div class="setting-copy">
                        <label class="setting-label">是否启用人性化时间格式</label>
                        <p class="setting-help">开启后显示"刚刚"、"5分钟前"等友好格式。</p>
                    </div>
                    <div class="setting-control">
                        <label class="check-row">
                            <input type="hidden" name="setting_human_time" value="0">
                            <input type="checkbox" name="setting_human_time" value="1" class="rounded" <?php echo !empty($template_settings['human_time']) ? 'checked' : ''; ?>>
                            <span class="text-sm">启用人性化时间</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="setting-section">
                <div class="setting-section-title">版块展示</div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_notice_forum_fid">社区公告版块</label>
                        <p class="setting-help">该版块的话题会显示在首页右侧公告栏中。</p>
                    </div>
                    <div class="setting-control">
                        <select id="setting_notice_forum_fid" name="setting_notice_forum_fid" class="form-control w-full">
                            <option value="0">请选择版块</option>
                            <?php foreach ($template_forums as $forum): ?>
                            <option value="<?php echo $forum['fid']; ?>" <?php echo (int)($template_settings['notice_forum_fid'] ?? 0) === $forum['fid'] ? 'selected' : ''; ?>>
                                <?php echo str_repeat('→ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_report_forum_fid">举报版块</label>
                        <p class="setting-help">用户举报的内容将自动发布到该版块。</p>
                    </div>
                    <div class="setting-control">
                        <select id="setting_report_forum_fid" name="setting_report_forum_fid" class="form-control w-full">
                            <option value="0">请选择版块</option>
                            <?php foreach ($template_forums as $forum): ?>
                            <option value="<?php echo $forum['fid']; ?>" <?php echo (int)($template_settings['report_forum_fid'] ?? 0) === $forum['fid'] ? 'selected' : ''; ?>>
                                <?php echo str_repeat('→ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="setting-row setting-row-last">
                    <div class="setting-copy">
                        <label class="setting-label">折叠版块</label>
                        <p class="setting-help">选择后，这些版块的主题不会在首页展开显示。</p>
                    </div>
                    <div class="setting-control">
                        <input type="hidden" name="setting_collapsed_fids[]" value="">
                        <div class="check-grid">
                            <?php
                            $collapsedFids = !empty($template_settings['collapsed_fids']) ? explode(',', $template_settings['collapsed_fids']) : [];
                            foreach ($template_forums as $forum):  ?>
                            <label class="check-row">
                                <input type="checkbox" name="setting_collapsed_fids[]" value="<?php echo $forum['fid']; ?>" class="rounded" <?php echo in_array((string)$forum['fid'], $collapsedFids) ? 'checked' : ''; ?>>
                                <span class="text-sm"><?php echo str_repeat('→ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="setting-section">
                <div class="setting-section-title">内容风控</div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_approve_keywords">审核关键词</label>
                        <p class="setting-help">命中这些关键词的主题或回帖需要审核，支持每行一个或用逗号分隔。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_approve_keywords" name="setting_approve_keywords" class="form-control w-full" rows="5" placeholder="例如：&#10;敏感词一&#10;敏感词二"><?php echo htmlspecialchars($template_settings['approve_keywords'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="setting-row setting-row-last">
                    <div class="setting-copy">
                        <label class="setting-label" for="setting_block_keywords">禁止关键词</label>
                        <p class="setting-help">命中这些关键词的主题或回帖会被禁止发布，支持每行一个或用逗号分隔。</p>
                    </div>
                    <div class="setting-control">
                        <textarea id="setting_block_keywords" name="setting_block_keywords" class="form-control w-full" rows="5" placeholder="例如：&#10;广告词一&#10;广告词二"><?php echo htmlspecialchars($template_settings['block_keywords'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="setting-section">
                <div class="setting-section-title">金币规则</div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <div class="setting-label">每日签到金币</div>
                        <p class="setting-help">设置签到时随机获得的金币范围；每天只能签到一次。</p>
                    </div>
                    <div class="setting-control">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm text-muted">最低获得</span>
                                <input type="number" name="signin_credit_min" class="form-control" value="<?php echo (int)($template_signinRange[0] ?? 1); ?>" min="0" step="1">
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm text-muted">最高获得</span>
                                <input type="number" name="signin_credit_max" class="form-control" value="<?php echo (int)($template_signinRange[1] ?? 5); ?>" min="0" step="1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="setting-row setting-row-last">
                    <div class="setting-copy">
                        <div class="setting-label">动作金币规则</div>
                        <p class="setting-help">直接配置每个行为的金币变化。正数为奖励，负数为消耗；每日上限只限制奖励。</p>
                    </div>
                    <div class="setting-control">
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
                                <div class="setting-copy">
                                    <strong class="text-sm"><?php echo htmlspecialchars($label); ?></strong>
                                </div>
                                <label class="w-16 flex justify-center cursor-pointer">
                                    <input type="checkbox" name="credit_rule_enabled[<?php echo htmlspecialchars($action); ?>]" value="1" class="rounded" <?php echo $enabled ? 'checked' : ''; ?>>
                                </label>
                                <div class="w-20">
                                    <input type="number" name="credit_rule_credit[<?php echo htmlspecialchars($action); ?>]" class="form-control text-center" value="<?php echo $credit; ?>" step="1">
                                </div>
                                <div class="w-24">
                                    <input type="number" name="credit_rule_daily_max[<?php echo htmlspecialchars($action); ?>]" class="form-control text-center" value="<?php echo $dailyMax; ?>" min="0" step="1">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">保存设置</button>
            </div>
        </form>
    </div>
</div>
