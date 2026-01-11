<?php
/**
 * XML Sitemap Generator
 */

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

// Get all published posts
try {
    $stmt = db()->query("
        SELECT slug, published_at, created_at
        FROM posts
        WHERE status = 'published'
        ORDER BY published_at DESC, created_at DESC
    ");
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Sitemap error: " . $e->getMessage());
    $posts = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Homepage -->
    <url>
        <loc><?php echo e(baseUrl('/')); ?></loc>
        <lastmod><?php echo date('c'); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Posts -->
    <?php foreach ($posts as $post): ?>
    <url>
        <loc><?php echo e(baseUrl('/post.php?slug=' . $post['slug'])); ?></loc>
        <lastmod><?php echo date('c', strtotime($post['published_at'] ?? $post['created_at'])); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
</urlset>
