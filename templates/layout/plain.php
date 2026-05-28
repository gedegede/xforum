<!DOCTYPE html>
<html lang="zh-CN" class="<?php
    $theme = 'light';
    if (isset($template_user) && is_array($template_user) && !empty($template_user) && isset($template_user['json_data'])) {
        $jsonData = json_decode($template_user['json_data'], true);
        $theme = $jsonData['theme'] ?? 'light';
    }
    if (!in_array($theme, ['light', 'dark'], true)) {
        $theme = 'light';
    }
    echo $theme;
?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(isset($template_title) ? $template_title : \Models\SettingModel::get('site_name', 'XForum')); ?></title>
    <?php $csrfToken = \Lib\CsrfHelper::generate(); ?>
    <?php if ($csrfToken !== ''): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pm.css">
    <?php
    $customCss = '';
    if (isset($template_user) && is_array($template_user) && !empty($template_user) && isset($template_user['json_data'])) {
        $jsonData = json_decode($template_user['json_data'], true);
        $customCss = $jsonData['custom_css'] ?? '';
    }
    if (!empty($customCss)):
    ?>
    <style id="user-custom-css"><?php echo str_ireplace('</style', '<\\/style', $customCss); ?></style>
    <?php endif; ?>
    <?php echo \Models\SettingModel::get('head_code', ''); ?>
</head>
<body class="pm-plain-body">
    <main class="pm-plain-main">
        <?php echo $content; ?>
    </main>

    <div id="credit-toast" style="display:none; position:fixed; top:24px; left:50%; transform:translateX(-50%) translateY(-20px); z-index:9999; opacity:0; transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <div style="display:flex; align-items:center; gap:8px; padding:12px 20px; border-radius:12px; background:linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow:0 10px 40px rgba(16, 185, 129, 0.3), 0 2px 8px rgba(0, 0, 0, 0.1); color:white; font-size:14px; font-weight:600;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v8M8 12h8"/>
            </svg>
            <span id="credit-toast-text"></span>
        </div>
    </div>

    <div id="message-modal" data-modal-overlay class="modal hidden" onclick="closeMessageModal()">
        <div class="modal-panel" onclick="event.stopPropagation()">
            <div class="modal-header">
                <span id="message-modal-title" class="font-semibold">提示</span>
                <button onclick="closeMessageModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p id="message-modal-content" class="text-text leading-relaxed"></p>
            </div>
            <div id="message-modal-footer" class="modal-footer hidden">
                <button onclick="closeMessageModal()" class="cancel-btn btn btn-soft">取消</button>
                <button onclick="closeMessageModal()" class="confirm-btn btn btn-primary">确定</button>
            </div>
        </div>
    </div>

    <script src="assets/js/base.js"></script>
</body>
</html>
