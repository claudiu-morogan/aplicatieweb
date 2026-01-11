<?php
/**
 * Public Homepage - Blog Listing
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/widgets/seasonal-countdown.php';
require_once __DIR__ . '/../includes/widgets/category-filter.php';
require_once __DIR__ . '/../includes/widgets/blog-posts.php';
require_once __DIR__ . '/../includes/widgets/personal-reminders.php';

// Start session to check for logged in user
Auth::startSession();

// Get enabled widgets
$enabled_widgets = [];
$blog_widget_enabled = false;
$seasonal_widgets = [];
try {
    $enabled_widgets = getEnabledWidgets();

    // Check if blog posts widget is enabled and collect seasonal widgets
    foreach ($enabled_widgets as $widget) {
        if ($widget['widget_key'] === 'blog_posts') {
            $blog_widget_enabled = true;
        }
        if ($widget['widget_type'] === 'seasonal') {
            $seasonal_widgets[] = $widget;
        }
    }
} catch (PDOException $e) {
    error_log("Widgets error: " . $e->getMessage());
}

// Determine layout: if blog posts disabled and seasonal widgets enabled, show seasonal as full-width
$show_seasonal_fullwidth = !$blog_widget_enabled && !empty($seasonal_widgets);

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = POSTS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Tag filtering
$tag_filter = $_GET['tag'] ?? null;
$current_tag_name = null;

// Category filtering
$category_filter = $_GET['category'] ?? null;
$current_category = null;

if ($category_filter) {
    try {
        $current_category = getCategoryBySlug($category_filter);
    } catch (PDOException $e) {
        error_log("Category load error: " . $e->getMessage());
    }
}
// Get total count
try {
    if ($tag_filter) {
        // Get tag name
        $tag_stmt = db()->prepare("SELECT name FROM tags WHERE slug = ?");
        $tag_stmt->execute([$tag_filter]);
        $current_tag_name = $tag_stmt->fetchColumn();

        // Count posts with this tag
        $count_stmt = db()->prepare("
            SELECT COUNT(DISTINCT p.id)
            FROM posts p
            JOIN post_tags pt ON p.id = pt.post_id
            JOIN tags t ON pt.tag_id = t.id
            WHERE p.status = 'published' AND t.slug = ?
            " . ($category_filter ? " AND p.category_id = ?" : "") . "
        ");
        $params = [$tag_filter];
        if ($category_filter && $current_category) {
            $params[] = $current_category['id'];
        }
        $count_stmt->execute($params);
    } elseif ($category_filter && $current_category) {
        // Count posts in category
        $count_stmt = db()->prepare("SELECT COUNT(*) FROM posts WHERE status = 'published' AND category_id = ?");
        $count_stmt->execute([$current_category['id']]);
    } else {
        $count_stmt = db()->prepare("SELECT COUNT(*) FROM posts WHERE status = 'published'");
        $count_stmt->execute();
    }

    $total_posts = $count_stmt->fetchColumn();
    $total_pages = ceil($total_posts / $per_page);

    // Get posts
    if ($tag_filter) {
        $stmt = db()->prepare("
            SELECT DISTINCT p.*, u.username, u.full_name
            FROM posts p
            JOIN users u ON p.user_id = u.id
            JOIN post_tags pt ON p.id = pt.post_id
            JOIN tags t ON pt.tag_id = t.id
            WHERE p.status = 'published' AND t.slug = ?
            " . ($category_filter && $current_category ? " AND p.category_id = ?" : "") . "
            ORDER BY p.published_at DESC, p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params = [$tag_filter];
        if ($category_filter && $current_category) {
            $params[] = $current_category['id'];
        }
        $params[] = $per_page;
        $params[] = $offset;
        $stmt->execute($params);
    } elseif ($category_filter && $current_category) {
        $stmt = db()->prepare("
            SELECT p.*, u.username, u.full_name
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.status = 'published' AND p.category_id = ?
            ORDER BY p.published_at DESC, p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$current_category['id'], $per_page, $offset]);
    } else {
        $stmt = db()->prepare("
            SELECT p.*, u.username, u.full_name
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.status = 'published'
            ORDER BY p.published_at DESC, p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$per_page, $offset]);
    }
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
    <title><?php echo e(getSetting('site_name', 'AplicatieWeb')); ?> - <?php echo e(getSetting('site_tagline', 'Developer & Povestitor')); ?></title>
    <meta name="description" content="<?php echo e(getSetting('site_description', 'Blog personal și gânduri')); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(baseUrl('/')); ?>">
    <meta property="og:title" content="<?php echo e(getSetting('site_name', 'AplicatieWeb')); ?>">
    <meta property="og:description" content="<?php echo e(getSetting('site_description', 'Blog personal și gânduri')); ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo e(baseUrl('/')); ?>">
    <meta property="twitter:title" content="<?php echo e(getSetting('site_name', 'AplicatieWeb')); ?>">
    <meta property="twitter:description" content="<?php echo e(getSetting('site_description', 'Blog personal și gânduri')); ?>">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo e(baseUrl('/')); ?>">
    <link rel="stylesheet" href="/assets/css/public-news.css">
    <style>
        /* Remove all inline styles - using external CSS */
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
                <a href="/" class="active">Acasă</a>
                <a href="/admin/" class="admin-link">Admin</a>
            </nav>
        </div>
    </div>

    <div class="container">
        <?php if ($current_tag_name || $current_category): ?>
            <div class="filter-info">
                <?php if ($current_category): ?>
                    Afișare postări din categoria: <strong style="color: <?php echo e($current_category['color']); ?>;"><?php echo e($current_category['name']); ?></strong>
                    <a href="/">← Înapoi la toate postările</a>
                <?php elseif ($current_tag_name): ?>
                    Afișare postări cu eticheta: <strong>#<?php echo e($current_tag_name); ?></strong>
                    <a href="/">← Înapoi la toate postările</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($show_seasonal_fullwidth): ?>
            <!-- Full-width seasonal countdown layout -->
            <div class="fullwidth-seasonal">
                <?php
                foreach ($seasonal_widgets as $widget) {
                    $settings = json_decode($widget['settings'], true);
                    $season = $settings['season'] ?? 'autumn';
                    $icon = $season === 'christmas' ? '🎄' : '🍂';
                    echo '<div class="section-header">' . $icon . ' Countdown ' . ucfirst($season) . '</div>';
                    renderSeasonalCountdown($widget);
                }
                ?>
            </div>
        <?php else: ?>
            <!-- Standard sidebar layout -->
            <div class="main-layout">
                <div class="main-content">
                    <?php
                    // Render blog posts widget
                    if (!empty($enabled_widgets)) {
                        foreach ($enabled_widgets as $widget) {
                            if ($widget['widget_type'] === 'content' && $widget['widget_key'] === 'blog_posts') {
                                echo '<div class="section-header">📰 Ultimele Știri</div>';
                                renderBlogPosts($widget, $posts, $page, $total_pages, $tag_filter);
                            }
                        }
                    }
                    ?>
                </div>

                <div class="sidebar">
                    <?php
                    // Render sidebar widgets
                    if (!empty($enabled_widgets)) {
                        foreach ($enabled_widgets as $widget) {
                            if ($widget['widget_type'] === 'personal' && $widget['widget_key'] === 'personal_reminders') {
                                echo '<div class="section-header">📝 Memento-uri</div>';
                                renderPersonalReminders($widget);
                            } elseif ($widget['widget_type'] === 'filter' && $widget['widget_key'] === 'category_filter') {
                                echo '<div class="section-header">📂 Categorii</div>';
                                renderCategoryFilter($widget, $current_category);
                            } elseif ($widget['widget_type'] === 'seasonal') {
                                $settings = json_decode($widget['settings'], true);
                                $season = $settings['season'] ?? 'autumn';
                                $icon = $season === 'christmas' ? '🎄' : '🍂';
                                echo '<div class="section-header">' . $icon . ' Countdown</div>';
                                renderSeasonalCountdown($widget);
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>© <?php echo date('Y'); ?> <?php echo e(getSetting('site_name', 'AplicatieWeb')); ?> | Toate drepturile rezervate</p>
        </div>
    </div>

    <?php if (!empty($enabled_widgets)): ?>
        <script src="/js/countdown-widget.js?v=2"></script>
    <?php endif; ?>

    <script>
        // Theme toggle functionality
        (function() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.querySelector('.theme-icon');
            const htmlElement = document.documentElement;

            console.log('Theme script loaded');
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
