<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>用户管理</h2>
    </div>
    <div class="card-body padded">
        <form method="get">
            <input type="hidden" name="c" value="admin">
            <input type="hidden" name="a" value="users">
            <div class="flex gap-sm flex-wrap">
                <input type="text" name="keyword" placeholder="搜索用户名或邮箱..." value="<?php echo htmlspecialchars($keyword); ?>">
                <select name="gid">
                    <option value="0">全部用户组</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['gid']; ?>" <?php echo $gid == $group['gid'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($group['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">搜索</button>
            </div>
        </form>

        <table class="table mt-lg">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>邮箱</th>
                    <th>用户组</th>
                    <th>状态</th>
                    <th>注册时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['uid']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo $user['gid']; ?></td>
                        <td><span class="badge <?php echo $user['status'] ? 'badge-green' : 'badge-red'; ?>"><?php echo $user['status'] ? '正常' : '禁用'; ?></span></td>
                        <td><?php echo date('Y-m-d', $user['reg_date']); ?></td>
                        <td>
                            <a href="index.php?c=admin&a=userEdit&uid=<?php echo $user['uid']; ?>" class="btn btn-secondary">编辑</a>
                            <a href="index.php?c=admin&a=userDelete&uid=<?php echo $user['uid']; ?>" class="btn btn-secondary" onclick="return confirm('确定删除该用户？')">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary">暂无用户</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($pages > 1): ?>
            <div class="pagination mt-lg">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a href="index.php?c=admin&a=users&keyword=<?php echo urlencode($keyword); ?>&gid=<?php echo $gid; ?>&page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
