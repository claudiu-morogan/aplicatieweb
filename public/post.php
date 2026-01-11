<?php
/**
 * Single Post View - Public
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Start session to check for logged in user
Auth::startSession();
$is_logged_in = Auth::check();

$slug = $_GET['slug'] ?? '';
$post = null;
$error = '';

if (empty($slug)) {
    $error = 'Post not found.';
} else {
    try {
        // Allow viewing draft/scheduled posts if logged in
        if ($is_logged_in) {
            $stmt = db()->prepare("
                SELECT p.*, u.username, u.full_name
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.slug = ?
            ");
        } else {
            $stmt = db()->prepare("
                SELECT p.*, u.username, u.full_name
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.slug = ? AND p.status = 'published'
            ");
        }
        $stmt->execute([$slug]);
        $post = $stmt->fetch();

        if (!$post) {
            $error = 'Post not found or not published yet.';
        } else {
            // Increment view count
            if (ANALYTICS_ENABLED) {
                $update_stmt = db()->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
                $update_stmt->execute([$post['id']]);
            }

            // Get tags for this post
            $post_tags = getTags($post['id']);
        }
    } catch (PDOException $e) {
        error_log("Post view error: " . $e->getMessage());
        $error = 'Error loading post.';
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? e($post['title']) . ' | ' : ''; ?>AplicatieWeb</title>
    <?php if ($post): ?>
        <meta name="description" content="<?php echo e($post['excerpt']); ?>">
        <meta name="author" content="<?php echo e($post['full_name'] ?? $post['username']); ?>">
        <meta name="keywords" content="<?php echo e(implode(', ', $post_tags)); ?>">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="article">
        <meta property="og:url" content="<?php echo e(baseUrl('/post.php?slug=' . $post['slug'])); ?>">
        <meta property="og:title" content="<?php echo e($post['title']); ?>">
        <meta property="og:description" content="<?php echo e($post['excerpt']); ?>">
        <?php if ($post['featured_image']): ?>
        <meta property="og:image" content="<?php echo e(baseUrl($post['featured_image'])); ?>">
        <?php endif; ?>
        <meta property="article:published_time" content="<?php echo date('c', strtotime($post['published_at'] ?? $post['created_at'])); ?>">
        <meta property="article:author" content="<?php echo e($post['full_name'] ?? $post['username']); ?>">
        <?php foreach ($post_tags as $tag): ?>
        <meta property="article:tag" content="<?php echo e($tag); ?>">
        <?php endforeach; ?>

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="<?php echo e(baseUrl('/post.php?slug=' . $post['slug'])); ?>">
        <meta property="twitter:title" content="<?php echo e($post['title']); ?>">
        <meta property="twitter:description" content="<?php echo e($post['excerpt']); ?>">
        <?php if ($post['featured_image']): ?>
        <meta property="twitter:image" content="<?php echo e(baseUrl($post['featured_image'])); ?>">
        <?php endif; ?>

        <!-- Canonical URL -->
        <link rel="canonical" href="<?php echo e(baseUrl('/post.php?slug=' . $post['slug'])); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/public-news.css">
    <!-- Quill Editor CSS for proper content rendering -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        /* Post-specific styles */
        .post-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .article-wrapper {
            background: var(--bg-secondary);
            border-radius: 2px;
            padding: 0;
            box-shadow: 0 1px 3px var(--shadow);
            margin-bottom: 30px;
            transition: background-color 0.3s ease;
        }

        .post-header {
            padding: 40px 40px 30px;
            border-bottom: 3px solid var(--accent-red);
            transition: border-color 0.3s ease;
            position: relative;
        }

        .post-status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            background: var(--bg-tertiary);
            border: 2px solid var(--border-color);
            color: var(--text-secondary);
        }

        .post-status-badge.status-draft {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        [data-theme="dark"] .post-status-badge.status-draft {
            background: #4a3f1a;
            border-color: #ffc107;
            color: #ffc107;
        }

        .post-status-badge.status-scheduled {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }

        [data-theme="dark"] .post-status-badge.status-scheduled {
            background: #1a3d44;
            border-color: #17a2b8;
            color: #17a2b8;
        }

        .post-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--text-primary);
            line-height: 1.2;
            font-weight: 800;
        }

        .post-meta {
            color: var(--text-secondary);
            font-size: 0.9rem;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .post-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .featured-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
            display: block;
        }

        .post-content {
            padding: 40px;
            color: var(--text-primary);
            font-size: 1.05rem;
            line-height: 1.8;
        }

        /* Override Quill editor styles for public display */
        .post-content .ql-editor {
            padding: 0;
            background: transparent;
            color: var(--text-tertiary);
            font-size: 1.05rem;
            line-height: 1.8;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        .post-content .ql-editor * {
            color: inherit;
        }

        .post-content h1,
        .post-content h2,
        .post-content h3 {
            color: var(--text-primary);
            margin-top: 30px;
            margin-bottom: 15px;
            line-height: 1.3;
            font-weight: 700;
        }

        .post-content h1 { font-size: 2rem; }
        .post-content h2 {
            font-size: 1.6rem;
            border-left: 4px solid var(--accent-red);
            padding-left: 15px;
        }
        .post-content h3 { font-size: 1.3rem; }

        .post-content p {
            margin-bottom: 1.2em;
            margin-top: 0;
            color: var(--text-tertiary);
            line-height: 1.8;
            min-height: 1.2em;
        }

        .post-content p + p {
            margin-top: 1em;
        }

        .post-content p br {
            line-height: 1.8;
        }

        .post-content a {
            color: var(--accent-red);
            text-decoration: underline;
        }

        .post-content a:hover {
            color: var(--accent-red-dark);
        }

        .post-content ul,
        .post-content ol {
            margin-bottom: 20px;
            padding-left: 30px;
            color: var(--text-tertiary);
        }

        .post-content li {
            margin-bottom: 10px;
        }

        .post-content pre {
            background: var(--bg-primary);
            padding: 20px;
            border-radius: 4px;
            overflow-x: auto;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent-red);
            transition: background-color 0.3s ease;
        }

        .post-content code {
            font-family: 'Courier New', monospace;
            color: #c7254e;
            background: var(--bg-tertiary);
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.9em;
        }

        .post-content pre code {
            color: var(--text-primary);
            background: transparent;
            padding: 0;
        }

        .post-content img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin: 20px 0;
            box-shadow: 0 2px 8px var(--shadow);
        }

        .post-content strong {
            color: var(--text-primary);
            font-weight: 700;
        }

        .post-content em {
            color: var(--text-tertiary);
        }

        .post-content blockquote {
            border-left: 4px solid var(--accent-red);
            padding-left: 20px;
            margin: 20px 0;
            font-style: italic;
            color: var(--text-secondary);
        }

        /* Quill Editor Output Styles */
        .post-content .ql-align-center {
            text-align: center;
        }

        .post-content .ql-align-right {
            text-align: right;
        }

        .post-content .ql-align-justify {
            text-align: justify;
        }

        .post-content .ql-indent-1 { padding-left: 3em; }
        .post-content .ql-indent-2 { padding-left: 6em; }
        .post-content .ql-indent-3 { padding-left: 9em; }
        .post-content .ql-indent-4 { padding-left: 12em; }
        .post-content .ql-indent-5 { padding-left: 15em; }
        .post-content .ql-indent-6 { padding-left: 18em; }
        .post-content .ql-indent-7 { padding-left: 21em; }
        .post-content .ql-indent-8 { padding-left: 24em; }

        .post-content .ql-size-small {
            font-size: 0.75em;
        }

        .post-content .ql-size-large {
            font-size: 1.5em;
        }

        .post-content .ql-size-huge {
            font-size: 2.5em;
        }

        .post-content .ql-font-serif {
            font-family: Georgia, Times New Roman, serif;
        }

        .post-content .ql-font-monospace {
            font-family: Monaco, Courier New, monospace;
        }

        .post-content s {
            text-decoration: line-through;
        }

        .post-content u {
            text-decoration: underline;
        }

        .post-content sub {
            vertical-align: sub;
            font-size: smaller;
        }

        .post-content sup {
            vertical-align: super;
            font-size: smaller;
        }

        .post-content .ql-video {
            width: 100%;
            height: 450px;
            margin: 20px 0;
        }

        /* Code block from Quill */
        .post-content .ql-code-block-container {
            background: var(--bg-primary);
            padding: 20px;
            border-radius: 4px;
            overflow-x: auto;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent-red);
        }

        .post-content pre.ql-syntax {
            background: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            border-radius: 4px;
            overflow-x: auto;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent-red);
        }

        .post-tags {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .post-tags-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .tag {
            display: inline-block;
            padding: 5px 12px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
            border-radius: 3px;
        }

        .tag:hover {
            background: var(--accent-red);
            border-color: var(--accent-red);
            color: white;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background: var(--accent-red);
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border-radius: 3px;
            font-size: 0.9rem;
        }

        .back-link:hover {
            background: var(--accent-red-dark);
        }

        .error {
            text-align: center;
            padding: 100px 20px;
            background: var(--bg-secondary);
            border-radius: 2px;
            box-shadow: 0 1px 3px var(--shadow);
            transition: background-color 0.3s ease;
        }

        .error h2 {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--accent-red);
        }

        .error p {
            color: var(--text-secondary);
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .post-title {
                font-size: 1.8rem;
            }

            .post-header,
            .post-content {
                padding: 25px 20px;
            }

            .featured-image {
                height: 250px;
            }

            .post-container {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="top-bar-inner">
            <div class="logo-section">
                <div class="logo"><?php echo e(getSetting('site_name', 'AplicatieWeb')); ?><span class="logo-accent">.ro</span></div>
            </div>
            <div class="top-info">
                <span><?php echo date('l, d F Y'); ?></span>
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
                    <span class="theme-icon">🌙</span>
                </button>
            </div>
        </div>
    </div>

    <div class="header">
        <div class="header-inner">
            <nav class="nav">
                <a href="/">Acasă</a>
                <a href="/admin/" class="admin-link">Admin</a>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="post-container">
            <?php if ($error): ?>
                <div class="error">
                    <h2>404</h2>
                    <p><?php echo e($error); ?></p>
                    <a href="/" class="back-link">← Înapoi la pagina principală</a>
                </div>
            <?php else: ?>
                <article class="article-wrapper">
                    <?php if ($post['featured_image']): ?>
                        <img src="<?php echo e($post['featured_image']); ?>" alt="<?php echo e($post['title']); ?>" class="featured-image">
                    <?php endif; ?>

                    <header class="post-header">
                        <?php if ($is_logged_in && $post['status'] !== 'published'): ?>
                            <div class="post-status-badge status-<?php echo e($post['status']); ?>">
                                <?php if ($post['status'] === 'draft'): ?>
                                    📝 DRAFT
                                <?php elseif ($post['status'] === 'scheduled'): ?>
                                    ⏰ SCHEDULED
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <h1 class="post-title"><?php echo e($post['title']); ?></h1>
                        <div class="post-meta">
                            <span>📅 <?php echo formatDate($post['published_at'] ?? $post['created_at']); ?></span>
                            <span>👤 <?php echo e($post['full_name'] ?? $post['username']); ?></span>
                            <span>⏱️ <?php echo $post['reading_time']; ?> min lectură</span>
                            <span>👁️ <?php echo number_format($post['views']); ?> vizualizări</span>
                        </div>
                    </header>

                    <div class="post-content">
                        <div class="ql-editor">
                            <?php echo $post['content']; ?>
                        </div>

                        <?php if (!empty($post_tags)): ?>
                            <div class="post-tags" style="margin-left: 0; padding: 0;">
                                <span class="post-tags-label">Etichete:</span>
                                <?php foreach ($post_tags as $tag): ?>
                                    <a href="/?tag=<?php echo urlencode(slugify($tag)); ?>" class="tag">#<?php echo e($tag); ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>

                <a href="/" class="back-link">← Înapoi la toate postările</a>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>© <?php echo date('Y'); ?> <?php echo e(getSetting('site_name', 'AplicatieWeb')); ?> | Toate drepturile rezervate</p>
        </div>
    </div>

    <script>
        console.log('📖 Post: <?php echo $post ? e($post['title']) : 'Not found'; ?>');

        // Theme toggle functionality
        (function() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.querySelector('.theme-icon');
            const htmlElement = document.documentElement;

            console.log('Theme script loaded (post page)');
            console.log('themeToggle:', themeToggle);
            console.log('themeIcon:', themeIcon);

            // Get saved theme or use system preference
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            console.log('savedTheme:', savedTheme);
            console.log('systemPrefersDark:', systemPrefersDark);

            // Set initial theme
            if (savedTheme) {
                htmlElement.setAttribute('data-theme', savedTheme);
                updateIcon(savedTheme);
            } else if (systemPrefersDark) {
                htmlElement.setAttribute('data-theme', 'dark');
                updateIcon('dark');
            } else {
                // Default to light theme
                htmlElement.setAttribute('data-theme', 'light');
                updateIcon('light');
            }

            // Toggle theme on button click
            themeToggle.addEventListener('click', () => {
                console.log('Theme toggle clicked!');
                const currentTheme = htmlElement.getAttribute('data-theme');
                console.log('Current theme:', currentTheme);
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                console.log('New theme:', newTheme);

                htmlElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateIcon(newTheme);
            });

            // Update icon based on theme
            function updateIcon(theme) {
                console.log('Updating icon for theme:', theme);
                themeIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
            }

            // Listen for system theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    const newTheme = e.matches ? 'dark' : 'light';
                    htmlElement.setAttribute('data-theme', newTheme);
                    updateIcon(newTheme);
                }
            });
        })();
    </script>
</body>
</html>
