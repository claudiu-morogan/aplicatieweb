<?php
/**
 * Blog Homepage - List all published posts
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = POSTS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Get total count
try {
    $count_stmt = db()->prepare("SELECT COUNT(*) FROM posts WHERE status = 'published'");
    $count_stmt->execute();
    $total_posts = $count_stmt->fetchColumn();
    $total_pages = ceil($total_posts / $per_page);

    // Get posts
    $stmt = db()->prepare("
        SELECT p.*, u.username, u.full_name
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.status = 'published'
        ORDER BY p.published_at DESC, p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$per_page, $offset]);
    $posts = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Blog list error: " . $e->getMessage());
    $posts = [];
    $total_posts = 0;
    $total_pages = 0;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(getSetting('site_name', 'AplicatieWeb')); ?> - Blog</title>
    <meta name="description" content="<?php echo e(getSetting('site_tagline', 'Blog personal și gânduri')); ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #ffffff;
            min-height: 100vh;
        }

        .header {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav a {
            color: #a0aec0;
            text-decoration: none;
            margin-left: 25px;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .nav a:hover {
            color: #ffffff;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .hero {
            text-align: center;
            margin-bottom: 60px;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.2rem;
            color: #a0aec0;
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .post-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
            cursor: pointer;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
            border-color: rgba(102, 126, 234, 0.5);
        }

        .post-card h2 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #667eea;
        }

        .post-card h2 a {
            color: inherit;
            text-decoration: none;
        }

        .post-meta {
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .post-excerpt {
            color: #cbd5e0;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .read-more {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .read-more:hover {
            color: #764ba2;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 50px;
        }

        .pagination a,
        .pagination span {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: rgba(102, 126, 234, 0.3);
            border-color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 100px 20px;
        }

        .empty-state h2 {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 20px;
        }

        .footer {
            text-align: center;
            color: #718096;
            margin-top: 80px;
            padding: 40px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .posts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-inner">
            <div class="logo">AplicatieWeb</div>
            <nav class="nav">
                <a href="/">Acasă</a>
                <a href="/blog.php">Blog</a>
                <a href="/admin/">Admin</a>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="hero">
            <h1>Blog</h1>
            <p>Gânduri, idei și povești</p>
        </div>

        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <h2>Nicio postare încă</h2>
                <p>Prima postare va apărea în curând!</p>
            </div>
        <?php else: ?>
            <div class="posts-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card" onclick="window.location.href='/post.php?slug=<?php echo e($post['slug']); ?>'">
                        <h2><a href="/post.php?slug=<?php echo e($post['slug']); ?>"><?php echo e($post['title']); ?></a></h2>
                        <div class="post-meta">
                            <span>📅 <?php echo formatDate($post['published_at'] ?? $post['created_at'], 'd M Y'); ?></span>
                            <span>⏱️ <?php echo $post['reading_time']; ?> min</span>
                            <span>👁️ <?php echo number_format($post['views']); ?></span>
                        </div>
                        <?php if ($post['excerpt']): ?>
                            <p class="post-excerpt"><?php echo e(truncate($post['excerpt'], 150)); ?></p>
                        <?php endif; ?>
                        <a href="/post.php?slug=<?php echo e($post['slug']); ?>" class="read-more">Citește mai mult →</a>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">← Anterior</a>
                    <?php endif; ?>

                    <span>Pagina <?php echo $page; ?> din <?php echo $total_pages; ?></span>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Următorul →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="footer">
            <p>© <?php echo date('Y'); ?> AplicatieWeb | Construit cu pasiune</p>
        </div>
    </div>
</body>
</html>
