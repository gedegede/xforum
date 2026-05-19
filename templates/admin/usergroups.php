<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>用户组管理</h2>
        <a href="index.php?c=admin&a=usergroupAdd" class="btn btn-primary">添加用户组</a>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户组名称</th>
                    <th>类型</th>
                    <th>积分下限</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><?php echo $group['gid']; ?></td>
                        <td><?php echo htmlspecialchars($group['title']); ?></td>
                        <td>
                            <?php 
                            $types = ['system' => '系统', 'special' => '特殊', 'member' => '会员'];
                            echo $types[$group['group_type']] ?? '未知';
                            ?>
                        </td>
                        <td><?php echo $group['credit_lower']; ?></td>
                        <td>
                            <a href="index.php?c=admin&a=usergroupEdit&gid=<?php echo $group['gid']; ?>" class="btn btn-secondary">编辑</a>
                            <a href="index.php?c=admin&a=usergroupDelete&gid=<?php echo $group['gid']; ?>" class="btn btn-secondary" onclick="return confirm('确定删除该用户组？')">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>