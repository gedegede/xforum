<div class="bg-panel border border-border rounded shadow-sm mb-4">
    <div class="p-0">
        <div class="flex flex-wrap border-b">
            <a href="index.php?c=admin&a=index"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (!isset($_GET['a']) || $_GET['a'] == 'index') ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                首页
            </a>
            <a href="index.php?c=admin&a=settings"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (isset($_GET['a']) && ($_GET['a'] == 'settings')) ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                站点设置
            </a>
            <a href="index.php?c=admin&a=forums"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (isset($_GET['a']) && ($_GET['a'] == 'forums' || $_GET['a'] == 'forumAdd' || $_GET['a'] == 'forumEdit' || $_GET['a'] == 'forumDelete')) ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                版块管理
            </a>
            <a href="index.php?c=admin&a=threads"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (isset($_GET['a']) && ($_GET['a'] == 'threads' || $_GET['a'] == 'threadDelete' || $_GET['a'] == 'threadBatch')) ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                主题管理
            </a>
            <a href="index.php?c=admin&a=usergroups"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (isset($_GET['a']) && ($_GET['a'] == 'usergroups' || $_GET['a'] == 'usergroupAdd' || $_GET['a'] == 'usergroupEdit' || $_GET['a'] == 'usergroupDelete')) ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                用户组管理
            </a>
            <a href="index.php?c=admin&a=users"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (isset($_GET['a']) && ($_GET['a'] == 'users' || $_GET['a'] == 'userEdit' || $_GET['a'] == 'userDelete')) ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                用户管理
            </a>
            <a href="index.php?c=admin&a=logs"
               class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo (isset($_GET['a']) && $_GET['a'] == 'logs') ? 'border-primary text-primary' : 'border-transparent text-sub hover:text-text hover:border-border'; ?>">
                管理日志
            </a>
        </div>
    </div>
</div>
