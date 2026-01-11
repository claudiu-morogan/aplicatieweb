<?php
/**
 * Admin Dashboard - Starship Command Bridge
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

// Require authentication
Auth::requireLogin('/admin/login.php');

$user = Auth::user();

// Get stats
try {
    $stats = [
        'total_posts' => db()->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
        'published_posts' => db()->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn(),
        'draft_posts' => db()->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn(),
        'total_views' => db()->query("SELECT SUM(views) FROM posts")->fetchColumn() ?: 0,
        'total_media' => db()->query("SELECT COUNT(*) FROM media")->fetchColumn(),
        'total_tags' => db()->query("SELECT COUNT(*) FROM tags")->fetchColumn(),
    ];

    // Recent posts
    $stmt = db()->query("SELECT id, title, status, views, created_at FROM posts ORDER BY created_at DESC LIMIT 5");
    $recent_posts = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = ['total_posts' => 0, 'published_posts' => 0, 'draft_posts' => 0, 'total_views' => 0, 'total_media' => 0, 'total_tags' => 0];
    $recent_posts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Bridge | AplicatieWeb Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Consolas, monospace;
            background: #0a0e27;
            color: #00d9ff;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Starfield Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(2px 2px at 20% 30%, white, transparent),
                radial-gradient(2px 2px at 60% 70%, white, transparent),
                radial-gradient(1px 1px at 50% 50%, white, transparent),
                radial-gradient(1px 1px at 80% 10%, white, transparent),
                radial-gradient(2px 2px at 90% 60%, white, transparent),
                radial-gradient(1px 1px at 33% 80%, white, transparent),
                radial-gradient(1px 1px at 15% 90%, white, transparent);
            background-size: 200% 200%;
            background-position: 0% 0%;
            animation: starfield 120s linear infinite;
            opacity: 0.3;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes starfield {
            0% { background-position: 0% 0%; }
            100% { background-position: 100% 100%; }
        }

        /* Scanning line effect */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #00d9ff, transparent);
            box-shadow: 0 0 20px #00d9ff;
            animation: scan 4s linear infinite;
            pointer-events: none;
            z-index: 9999;
        }

        @keyframes scan {
            0% { transform: translateY(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(100vh); opacity: 0; }
        }

        .admin-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, rgba(10, 14, 39, 0.95) 0%, rgba(26, 30, 63, 0.95) 100%);
            border-right: 2px solid #00d9ff;
            padding: 20px;
            box-shadow: 0 0 40px rgba(0, 217, 255, 0.3), inset 0 0 100px rgba(0, 217, 255, 0.05);
            backdrop-filter: blur(10px);
            position: relative;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 2px;
            height: 100%;
            background: linear-gradient(180deg, transparent, #00d9ff, transparent);
            animation: sideglow 3s ease-in-out infinite;
        }

        @keyframes sideglow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0, 217, 255, 0.3);
            position: relative;
        }

        .logo h1 {
            font-size: 1.2rem;
            color: #00d9ff;
            text-shadow: 0 0 20px rgba(0, 217, 255, 0.8), 0 0 40px rgba(0, 217, 255, 0.4);
            margin-bottom: 5px;
            animation: textglow 2s ease-in-out infinite;
        }

        @keyframes textglow {
            0%, 100% { text-shadow: 0 0 20px rgba(0, 217, 255, 0.8), 0 0 40px rgba(0, 217, 255, 0.4); }
            50% { text-shadow: 0 0 30px rgba(0, 217, 255, 1), 0 0 60px rgba(0, 217, 255, 0.6); }
        }

        .logo .subtitle {
            font-size: 0.7rem;
            color: #00ff88;
            letter-spacing: 2px;
            animation: blink 3s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .nav-menu {
            list-style: none;
        }

        .nav-menu li {
            margin-bottom: 10px;
        }

        .nav-menu a {
            display: block;
            padding: 12px 15px;
            color: #00d9ff;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .nav-menu a::before {
            content: '';
            position: absolute;
            left: -100%;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 217, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .nav-menu a:hover::before {
            left: 100%;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(0, 217, 255, 0.1);
            border-color: #00d9ff;
            box-shadow: 0 0 15px rgba(0, 217, 255, 0.4), inset 0 0 10px rgba(0, 217, 255, 0.1);
            transform: translateX(5px);
        }

        .user-info {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 217, 255, 0.3);
            font-size: 0.85rem;
        }

        .user-info strong {
            color: #00ff88;
        }

        /* Main content */
        .main-content {
            padding: 30px;
            overflow-y: auto;
        }

        .header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(0, 217, 255, 0.3);
        }

        .header h2 {
            font-size: 2rem;
            color: #00d9ff;
            text-shadow: 0 0 20px rgba(0, 217, 255, 0.5);
        }

        .status-badge {
            padding: 8px 16px;
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid #00ff88;
            border-radius: 20px;
            color: #00ff88;
            font-size: 0.85rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(0, 217, 255, 0.1), rgba(185, 104, 255, 0.1));
            border: 1px solid #00d9ff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 217, 255, 0.2);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(0, 217, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: cardshine 3s linear infinite;
        }

        @keyframes cardshine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        .stat-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 217, 255, 0.4), inset 0 0 20px rgba(0, 217, 255, 0.1);
            border-color: #00ff88;
        }

        .stat-card .label {
            font-size: 0.8rem;
            color: #00ff88;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .stat-card .value {
            font-size: 2.5rem;
            color: #00d9ff;
            font-weight: bold;
            text-shadow: 0 0 15px rgba(0, 217, 255, 0.8), 0 0 30px rgba(0, 217, 255, 0.5);
            position: relative;
            z-index: 1;
            animation: valuepulse 2s ease-in-out infinite;
        }

        @keyframes valuepulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Recent posts table */
        .section {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 217, 255, 0.1);
            position: relative;
        }

        .section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #00d9ff, #b968ff, #00d9ff);
            background-size: 200% 100%;
            animation: borderflow 3s linear infinite;
        }

        @keyframes borderflow {
            0% { background-position: 0% 0%; }
            100% { background-position: 200% 0%; }
        }

        .section h3 {
            color: #00d9ff;
            margin-bottom: 20px;
            font-size: 1.3rem;
            text-shadow: 0 0 10px rgba(0, 217, 255, 0.5);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: rgba(0, 217, 255, 0.1);
        }

        th, td {
            padding: 12px;
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

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #00d9ff, #00ff88);
            color: #0a0e27;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.85rem;
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 217, 255, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        /* Quick Actions Circular Buttons */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 25px;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .action-button {
            position: relative;
            text-align: center;
            text-decoration: none;
        }

        .action-circle {
            width: 140px;
            height: 140px;
            margin: 0 auto 12px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
            background: radial-gradient(circle at 30% 30%, rgba(0, 217, 255, 0.2), rgba(0, 217, 255, 0.05));
            border: 3px solid #00d9ff;
            box-shadow: 0 0 25px rgba(0, 217, 255, 0.5), inset 0 0 25px rgba(0, 217, 255, 0.1);
        }

        .action-circle::before {
            content: '';
            position: absolute;
            top: 10%;
            left: 10%;
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background: radial-gradient(circle at 50% 50%, rgba(0, 217, 255, 0.2), transparent);
            animation: action-pulse 2s ease-in-out infinite;
        }

        @keyframes action-pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .action-circle::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            animation: circle-scan 3s linear infinite;
            opacity: 0.6;
        }

        @keyframes circle-scan {
            0% {
                transform: translateY(0) scaleX(0.5);
                opacity: 0;
            }
            50% {
                opacity: 0.8;
                transform: translateY(70px) scaleX(1);
            }
            100% {
                transform: translateY(140px) scaleX(0.5);
                opacity: 0;
            }
        }

        .action-circle:hover {
            transform: scale(1.08) rotate(2deg);
            box-shadow: 0 0 40px rgba(0, 217, 255, 0.8), inset 0 0 30px rgba(0, 217, 255, 0.2);
        }

        .action-circle:active {
            transform: scale(0.95);
        }

        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 0 10px rgba(0, 217, 255, 0.8));
        }

        .action-name {
            font-size: 0.85rem;
            font-weight: bold;
            color: #00d9ff;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
            text-shadow: 0 0 10px rgba(0, 217, 255, 0.8);
        }

        .action-label {
            font-size: 0.7rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Special color for database action */
        .action-circle.database {
            background: radial-gradient(circle at 30% 30%, rgba(185, 104, 255, 0.2), rgba(185, 104, 255, 0.05));
            border-color: #b968ff;
            box-shadow: 0 0 25px rgba(185, 104, 255, 0.5), inset 0 0 25px rgba(185, 104, 255, 0.1);
        }

        .action-circle.database::before {
            background: radial-gradient(circle at 50% 50%, rgba(185, 104, 255, 0.2), transparent);
        }

        .action-circle.database:hover {
            box-shadow: 0 0 40px rgba(185, 104, 255, 0.8), inset 0 0 30px rgba(185, 104, 255, 0.2);
        }

        .action-circle.database .action-name {
            color: #b968ff;
            text-shadow: 0 0 10px rgba(185, 104, 255, 0.8);
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h1>🚀 USS APLICATIEWEB</h1>
                <div class="subtitle">COMMAND BRIDGE</div>
            </div>

            <nav>
                <ul class="nav-menu">
                    <li><a href="/admin/" class="active">📊 Dashboard</a></li>
                    <li><a href="/admin/posts.php">📝 Posts</a></li>
                    <li><a href="/admin/editor.php">✍️ New Post</a></li>
                    <li><a href="/admin/categories.php">📂 Categories</a></li>
                    <li><a href="/admin/media.php">🖼️ Media</a></li>
                    <li><a href="/admin/reminders.php">📝 Reminders</a></li>
                    <li><a href="/admin/widgets.php">🧩 Widgets</a></li>
                    <li><a href="/admin/analytics.php">📈 Analytics</a></li>
                    <li><a href="/admin/users.php">👥 Users</a></li>
                    <li><a href="/admin/settings.php">⚙️ Settings</a></li>
                    <li><a href="/" target="_blank">🌐 View Site</a></li>
                    <li><a href="/admin/logout.php" style="color: #ff4444;">🚪 Logout</a></li>
                </ul>
            </nav>

            <div class="user-info">
                <strong>Captain:</strong> <?php echo e($user['full_name'] ?? $user['username']); ?><br>
                <strong>Role:</strong> <?php echo e(ucfirst($user['role'])); ?><br>
                <strong>Stardate:</strong> <?php echo date('Y.m.d H:i'); ?>
            </div>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            <div class="header">
                <h2>Command Bridge</h2>
                <div class="status-badge">● ALL SYSTEMS OPERATIONAL</div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="label">Total Posts</div>
                    <div class="value"><?php echo $stats['total_posts']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="label">Published</div>
                    <div class="value"><?php echo $stats['published_posts']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="label">Drafts</div>
                    <div class="value"><?php echo $stats['draft_posts']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="label">Total Views</div>
                    <div class="value"><?php echo number_format($stats['total_views']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="label">Media Files</div>
                    <div class="value"><?php echo $stats['total_media']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="label">Tags</div>
                    <div class="value"><?php echo $stats['total_tags']; ?></div>
                </div>
            </div>

            <!-- Recent posts -->
            <div class="section">
                <h3>📡 Recent Transmissions</h3>

                <?php if (empty($recent_posts)): ?>
                    <div class="empty-state">
                        <p>No posts yet. Start your first transmission!</p>
                        <br>
                        <a href="/admin/editor.php" class="btn">► Create First Post</a>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_posts as $post): ?>
                                <tr>
                                    <td><?php echo e($post['title']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $post['status']; ?>">
                                            <?php echo e(ucfirst($post['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($post['views']); ?></td>
                                    <td><?php echo timeAgo($post['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Quick actions -->
            <div class="section">
                <h3>⚡ Quick Actions</h3>
                <div class="actions-grid">
                    <a href="/admin/editor.php" class="action-button">
                        <div class="action-circle">
                            <div class="action-icon">✍️</div>
                            <div class="action-name">NEW POST</div>
                        </div>
                        <div class="action-label">Create</div>
                    </a>

                    <a href="/admin/media.php" class="action-button">
                        <div class="action-circle">
                            <div class="action-icon">📤</div>
                            <div class="action-name">MEDIA</div>
                        </div>
                        <div class="action-label">Upload</div>
                    </a>

                    <a href="/admin/posts.php" class="action-button">
                        <div class="action-circle">
                            <div class="action-icon">📝</div>
                            <div class="action-name">POSTS</div>
                        </div>
                        <div class="action-label">Manage</div>
                    </a>

                    <a href="/admin/reminders.php" class="action-button">
                        <div class="action-circle">
                            <div class="action-icon">📋</div>
                            <div class="action-name">REMINDERS</div>
                        </div>
                        <div class="action-label">Personal</div>
                    </a>

                    <a href="http://localhost:8081" target="_blank" class="action-button">
                        <div class="action-circle database">
                            <div class="action-icon">🗄️</div>
                            <div class="action-name">DATABASE</div>
                        </div>
                        <div class="action-label">phpMyAdmin</div>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        console.log('%c🚀 COMMAND BRIDGE ONLINE', 'color: #00d9ff; font-size: 16px; font-weight: bold;');
        console.log('%cWelcome, Captain <?php echo e($user['username']); ?>', 'color: #00ff88;');
    </script>
</body>
</html>
