<?php
/**
 * Posts Management - List all posts
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
Auth::requireLogin('/admin/login.php');

$user = Auth::user();
$error = '';
$success = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $post_id = $_GET['id'];
    $csrf_token = $_GET['token'] ?? '';

    if (Auth::verifyCsrfToken($csrf_token)) {
        try {
            $stmt = db()->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$post_id]);
            $success = 'Post deleted successfully!';
        } catch (PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            $error = 'Error deleting post.';
        }
    } else {
        $error = 'Security validation failed.';
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = ADMIN_POSTS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Filters
$status_filter = $_GET['status'] ?? 'all';

// Build query
$where = [];
$params = [];

if ($status_filter !== 'all') {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
try {
    $count_sql = "SELECT COUNT(*) FROM posts $where_clause";
    $count_stmt = db()->prepare($count_sql);
    $count_stmt->execute($params);
    $total_posts = $count_stmt->fetchColumn();
    $total_pages = ceil($total_posts / $per_page);

    // Get posts
    $sql = "SELECT p.*, u.username, u.full_name
            FROM posts p
            JOIN users u ON p.user_id = u.id
            $where_clause
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?";

    $params[] = $per_page;
    $params[] = $offset;

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Posts list error: " . $e->getMessage());
    $error = 'Database error loading posts.';
    $posts = [];
    $total_posts = 0;
    $total_pages = 0;
}

$csrf_token = Auth::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts | Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Consolas, monospace;
            background: #0a0e27;
            color: #00d9ff;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(180deg, #0a0e27 0%, #1a1e3f 100%);
            border-bottom: 2px solid #00d9ff;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.5rem;
            color: #00d9ff;
            text-shadow: 0 0 20px rgba(0, 217, 255, 0.5);
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid #ff4444;
            color: #ff6b6b;
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid #00ff88;
            color: #00ff88;
        }

        .filters {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-group label {
            font-size: 0.85rem;
            color: #00ff88;
        }

        select {
            padding: 8px 12px;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid #00d9ff;
            border-radius: 6px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            overflow: hidden;
        }

        thead {
            background: rgba(0, 217, 255, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 217, 255, 0.2);
        }

        th {
            color: #00ff88;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:hover {
            background: rgba(0, 217, 255, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .badge-published {
            background: rgba(0, 255, 136, 0.2);
            color: #00ff88;
            border: 1px solid #00ff88;
        }

        .badge-draft {
            background: rgba(255, 165, 0, 0.2);
            color: #ffa500;
            border: 1px solid #ffa500;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            background: linear-gradient(135deg, #00d9ff, #00ff88);
            border: none;
            border-radius: 6px;
            color: #0a0e27;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 217, 255, 0.4);
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.7rem;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #00d9ff;
            border: 1px solid #00d9ff;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff4444, #ff6b6b);
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state h3 {
            color: #00d9ff;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📝 Manage Posts</h1>
        <div class="header-actions">
            <a href="/admin/editor.php" class="btn">+ New Post</a>
            <a href="/admin/" class="btn btn-secondary">← Dashboard</a>
        </div>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>🚨 ERROR:</strong> <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>✓ SUCCESS:</strong> <?php echo e($success); ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <div class="filter-group">
                <label>Status:</label>
                <select onchange="window.location.href='?status='+this.value">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Drafts</option>
                </select>
            </div>
            <div style="margin-left: auto; color: #6b7280; font-size: 0.85rem;">
                Total: <?php echo number_format($total_posts); ?> posts
            </div>
        </div>

        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <h3>No posts found</h3>
                <p>Start writing your first transmission!</p>
                <br>
                <a href="/admin/editor.php" class="btn">+ Create First Post</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <strong><?php echo e($post['title']); ?></strong>
                                <?php if ($post['excerpt']): ?>
                                    <br><small style="color: #6b7280;"><?php echo e(truncate($post['excerpt'], 80)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($post['full_name'] ?? $post['username']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $post['status']; ?>">
                                    <?php echo e(ucfirst($post['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo number_format($post['views']); ?></td>
                            <td><?php echo timeAgo($post['created_at']); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="/admin/editor.php?id=<?php echo $post['id']; ?>" class="btn btn-small">Edit</a>
                                    <a href="/post.php?slug=<?php echo e($post['slug']); ?>" class="btn btn-small btn-secondary" target="_blank">View</a>
                                    <a href="?action=delete&id=<?php echo $post['id']; ?>&token=<?php echo e($csrf_token); ?>"
                                       class="btn btn-small btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo e($status_filter); ?>" class="btn btn-small">← Prev</a>
                    <?php endif; ?>

                    <span style="padding: 8px 16px; color: #6b7280;">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </span>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo e($status_filter); ?>" class="btn btn-small">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        console.log('%c📝 POSTS MANAGEMENT ACTIVE', 'color: #00d9ff; font-size: 14px; font-weight: bold;');
    </script>
</body>
</html>
