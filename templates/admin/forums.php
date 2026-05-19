<?php include '_menu.php'; ?>

<div class="card">
    <div class="card-header">
        <h2>版块管理</h2>
        <a href="index.php?c=admin&a=forumAdd" class="btn btn-primary">添加版块</a>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>版块名称</th>
                    <th>上级版块</th>
                    <th>主题数</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forums as $forum): ?>
                    <tr>
                        <td><?php echo $forum['fid']; ?></td>
                        <td><?php echo str_repeat('→ ', $forum['depth'] ?? 0) . htmlspecialchars($forum['name']); ?></td>
                        <td><?php echo $forum['up_fid'] ? $forum['parent_name'] : '无'; ?></td>
                        <td><?php echo $forum['thread_num']; ?></td>
                        <td><?php echo $forum['status'] ? '启用' : '禁用'; ?></td>
                        <td>
                            <a href="index.php?c=admin&a=forumEdit&fid=<?php echo $forum['fid']; ?>" class="btn btn-secondary">编辑</a>
                            <a href="index.php?c=admin&a=forumDelete&fid=<?php echo $forum['fid']; ?>" class="btn btn-secondary" onclick="return confirm('确定删除该版块？')">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>