<div class="card">
 <div class="section">
 <div class="row">
 <div class="avatar avatar-lg">
 <?php echo \Lib\Helper::getAvatarInitial($template_member['username']); ?>
 </div>
 <div class="flex-1 min-width-0">
 <h2><?php echo htmlspecialchars($template_member['username']); ?></h2>
 <div class="muted">
 注册于 <?php echo date('Y-m-d', $template_member['reg_date']); ?> ·  <?php echo $template_member['thread_num']; ?> 主题 ·  <?php echo $template_member['reply_num']; ?> 回复 ·
 <?php echo (int)($template_member['credit'] ?? 0); ?> 金币
 </div>
 <div>
 <span class="badge badge-gray">资料管理</span>
 <span class="badge badge-gray">账号安全</span>
 <span class="badge badge-green"><?php echo (int)($template_member['credit'] ?? 0); ?> 金币</span>
 </div>
 </div>
 </div>
 </div>
</div>

<div class="card">
 <div class="tabs">
 <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=threads" class="tab<?php echo (isset($_GET['c']) && $_GET['c'] == 'member' && $_GET['a'] == 'profile') ? ' active' : ''; ?>">我的主题</a>
 <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=replies" class="tab">我的回复</a>
 <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=favorites" class="tab">我的收藏</a>
 <a href="index.php?c=member&a=profile&uid=<?php echo $template_member['uid']; ?>&type=credits" class="tab">金币明细</a>
 <a href="index.php?c=member&a=settings" class="tab active">个人设置</a>
 <a href="index.php?c=admin&a=index" class="tab<?php echo (isset($_GET['c']) && $_GET['c'] == 'admin') ? ' active' : ''; ?>">站点设置</a>
 </div>
</div>

<div class="card">
 <div class="card-header">
 <div>
 <h2>个人设置</h2>
 <p>维护账号资料、安全信息和界面偏好。</p>
 </div>
 </div>
 <div class="card-body padded">
 <?php if (!empty($template_error)): ?>
 <div class="error"><?php echo htmlspecialchars($template_error); ?></div>
 <?php endif; ?>
 <?php if (!empty($template_success)): ?>
 <div class="success"><?php echo htmlspecialchars($template_success); ?></div>
 <?php endif; ?>

 <?php
 $jsonData = isset($template_member['json_data']) ? json_decode($template_member['json_data'], true) : [];
 $currentTheme = $jsonData['theme'] ?? 'light';
 ?>

 <div class="tabs form-tabs">
 <button class="tab active" data-tab="profile">基本信息</button>
 <button class="tab" data-tab="password">修改密码</button>
 <button class="tab" data-tab="theme">主题设置</button>
 </div>

 <form method="post" class="form-panel" id="profile-form">
 <input type="hidden" name="action" value="profile">
 <div class="form-section">
 <div class="form-section-title">公开资料</div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="member_username">用户名</label>
 <p class="muted">用于发帖、回复和个人主页展示。</p>
 </div>
 <div class="form-control">
 <input type="text" id="member_username" name="username" value="<?php echo htmlspecialchars($template_member['username']); ?>" required>
 </div>
 </div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="member_email">邮箱</label>
 <p class="muted">用于账号识别和后续通知能力。</p>
 </div>
 <div class="form-control">
 <input type="email" id="member_email" name="email" value="<?php echo htmlspecialchars($template_member['email']); ?>" required>
 </div>
 </div>
 </div>
 <div class="actions">
 <button type="submit" class="btn btn-primary">保存修改</button>
 </div>
 </form>

 <form method="post" class="form-panel hide" id="password-form">
 <input type="hidden" name="action" value="password">
 <div class="form-section">
 <div class="form-section-title">账号安全</div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="old_password">原密码</label>
 <p class="muted">修改密码前需要验证当前密码。</p>
 </div>
 <div class="form-control">
 <input type="password" id="old_password" name="old_password" required>
 </div>
 </div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="new_password">新密码</label>
 <p class="muted">建议至少 6 位，避免与其他站点重复。</p>
 </div>
 <div class="form-control">
 <input type="password" id="new_password" name="new_password" required>
 </div>
 </div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted" for="confirm_password">确认密码</label>
 <p class="muted">再次输入新密码，确认没有输错。</p>
 </div>
 <div class="form-control">
 <input type="password" id="confirm_password" name="confirm_password" required>
 </div>
 </div>
 </div>
 <div class="actions">
 <button type="submit" class="btn btn-primary">修改密码</button>
 </div>
 </form>

 <form method="post" class="form-panel hide" id="theme-form">
 <input type="hidden" name="action" value="theme">
 <div class="form-section">
 <div class="form-section-title">界面偏好</div>
 <div class="form-row">
 <div class="flex-1 min-width-0">
 <label class="muted">主题模式</label>
 <p class="muted">系统会在后续访问中保持你的选择。</p>
 </div>
 <div class="form-control">
 <div class="grid grid-cols-2 gap-sm">
 <label class="box checkbox-item">
 <span class="swatch"></span>
 <span>
 <strong>日间模式</strong>
 <small>明亮背景，适合白天浏览。</small>
 </span>
 <input type="radio" name="theme" value="light" <?php echo $currentTheme == 'light' ? 'checked' : ''; ?>>
 </label>
 <label class="box checkbox-item">
 <span class="swatch swatch-dark"></span>
 <span>
 <strong>夜间模式</strong>
 <small>深色背景，适合低光环境。</small>
 </span>
 <input type="radio" name="theme" value="dark" <?php echo $currentTheme == 'dark' ? 'checked' : ''; ?>>
 </label>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
 const tabs = document.querySelectorAll('.form-tabs .tab');
 const forms = document.querySelectorAll('.form-panel');
  tabs.forEach(tab => {
 tab.addEventListener('click', function() {
 tabs.forEach(t => t.classList.remove('active'));
 this.classList.add('active');
  const tabName = this.dataset.tab;
 forms.forEach(form => {
 form.classList.toggle('hide', form.id !== tabName + '-form');
 });
 });
 });
});
</script>
