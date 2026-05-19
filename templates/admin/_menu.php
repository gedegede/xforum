<div class="card">
    <div class="tabs">
        <a href="index.php?c=admin&a=index" class="tab<?php echo (!isset($_GET['a']) || $_GET['a'] == 'index') ? ' active' : ''; ?>">首页</a>
        <a href="index.php?c=admin&a=settings" class="tab<?php echo (isset($_GET['a']) && ($_GET['a'] == 'settings')) ? ' active' : ''; ?>">站点设置</a>
        <a href="index.php?c=admin&a=forums" class="tab<?php echo (isset($_GET['a']) && ($_GET['a'] == 'forums' || $_GET['a'] == 'forumAdd' || $_GET['a'] == 'forumEdit' || $_GET['a'] == 'forumDelete')) ? ' active' : ''; ?>">版块管理</a>
        <a href="index.php?c=admin&a=threads" class="tab<?php echo (isset($_GET['a']) && ($_GET['a'] == 'threads' || $_GET['a'] == 'threadDelete' || $_GET['a'] == 'threadBatch')) ? ' active' : ''; ?>">主题管理</a>
        <a href="index.php?c=admin&a=usergroups" class="tab<?php echo (isset($_GET['a']) && ($_GET['a'] == 'usergroups' || $_GET['a'] == 'usergroupAdd' || $_GET['a'] == 'usergroupEdit' || $_GET['a'] == 'usergroupDelete')) ? ' active' : ''; ?>">用户组管理</a>
        <a href="index.php?c=admin&a=users" class="tab<?php echo (isset($_GET['a']) && ($_GET['a'] == 'users' || $_GET['a'] == 'userEdit' || $_GET['a'] == 'userDelete')) ? ' active' : ''; ?>">用户管理</a>
        <a href="index.php?c=admin&a=logs" class="tab<?php echo (isset($_GET['a']) && $_GET['a'] == 'logs') ? ' active' : ''; ?>">管理日志</a>
    </div>
</div>
