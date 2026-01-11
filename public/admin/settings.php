<?php
/**
 * Settings Management - Site Configuration
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
Auth::requireLogin('/admin/login.php');

$user = Auth::user();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrfToken($csrf_token)) {
        $error = 'Security validation failed.';
    } else {
        $settings = [
            'site_name' => trim($_POST['site_name'] ?? ''),
            'site_tagline' => trim($_POST['site_tagline'] ?? ''),
            'site_description' => trim($_POST['site_description'] ?? ''),
            'posts_per_page' => max(1, (int)($_POST['posts_per_page'] ?? POSTS_PER_PAGE)),
            'admin_posts_per_page' => max(1, (int)($_POST['admin_posts_per_page'] ?? ADMIN_POSTS_PER_PAGE)),
            'analytics_enabled' => isset($_POST['analytics_enabled']) ? '1' : '0',
        ];

        try {
            foreach ($settings as $key => $value) {
                $stmt = db()->prepare("
                    INSERT INTO settings (setting_key, setting_value)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->execute([$key, $value, $value]);
            }
            $success = 'Settings saved successfully!';
        } catch (PDOException $e) {
            error_log("Settings save error: " . $e->getMessage());
            $error = 'Database error saving settings.';
        }
    }
}

// Load current settings
$current_settings = [
    'site_name' => getSetting('site_name', 'AplicatieWeb'),
    'site_tagline' => getSetting('site_tagline', 'Developer & Povestitor'),
    'site_description' => getSetting('site_description', 'Blog personal și gânduri'),
    'posts_per_page' => getSetting('posts_per_page', POSTS_PER_PAGE),
    'admin_posts_per_page' => getSetting('admin_posts_per_page', ADMIN_POSTS_PER_PAGE),
    'analytics_enabled' => getSetting('analytics_enabled', '1'),
];

$csrf_token = Auth::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Admin</title>
    <link rel="stylesheet" href="/assets/css/admin-starship.css">
    <style>
        .container {
            max-width: 900px;
        }

        .intro {
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(0, 217, 255, 0.05);
            border-left: 4px solid #00d9ff;
            border-radius: 8px;
            position: relative;
        }

        .intro::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #00d9ff, #00ff88);
            animation: glow 2s ease-in-out infinite;
        }

        @keyframes glow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; box-shadow: 0 0 20px #00d9ff; }
        }

        .intro p {
            color: #a0aec0;
            line-height: 1.6;
            margin: 0;
        }

        .settings-card {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 217, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .settings-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #00d9ff, #00ff88, #00d9ff);
            background-size: 200% 100%;
            animation: borderflow 3s linear infinite;
        }

        @keyframes borderflow {
            0% { background-position: 0% 0%; }
            100% { background-position: 200% 0%; }
        }

        .settings-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(0, 217, 255, 0.05), transparent);
            transform: rotate(45deg);
            animation: cardshine 5s linear infinite;
            pointer-events: none;
        }

        @keyframes cardshine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        .settings-card h2 {
            color: #00ff88;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 217, 255, 0.2);
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-card h2::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #00ff88;
            box-shadow: 0 0 10px #00ff88;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 10px #00ff88; }
            50% { opacity: 0.5; box-shadow: 0 0 20px #00ff88; }
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        label {
            display: block;
            color: #00d9ff;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        label::before {
            content: '▸';
            color: #00ff88;
            font-size: 0.7rem;
        }

        .hint {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 5px;
            padding-left: 16px;
            font-style: italic;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid #00d9ff;
            border-radius: 6px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            transition: all 0.3s;
            position: relative;
        }

        input[type="number"] {
            background: rgba(0, 217, 255, 0.05)
                        linear-gradient(90deg,
                            rgba(0, 255, 136, 0.1) 0%,
                            transparent 20%);
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            background: rgba(0, 217, 255, 0.08);
            box-shadow: 0 0 20px rgba(0, 217, 255, 0.3), inset 0 0 10px rgba(0, 217, 255, 0.1);
            border-color: #00ff88;
            transform: translateY(-1px);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(0, 217, 255, 0.03);
            border: 1px solid rgba(0, 217, 255, 0.2);
            border-radius: 8px;
            transition: all 0.3s;
        }

        .checkbox-group:hover {
            background: rgba(0, 217, 255, 0.05);
            border-color: rgba(0, 217, 255, 0.4);
        }

        input[type="checkbox"] {
            width: 24px;
            height: 24px;
            cursor: pointer;
            appearance: none;
            background: rgba(0, 217, 255, 0.1);
            border: 2px solid #00d9ff;
            border-radius: 4px;
            position: relative;
            transition: all 0.3s;
        }

        input[type="checkbox"]:checked {
            background: linear-gradient(135deg, #00d9ff, #00ff88);
            border-color: #00ff88;
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.5);
        }

        input[type="checkbox"]:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #0a0e27;
            font-weight: bold;
            font-size: 1rem;
        }

        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            text-transform: none;
            font-size: 0.9rem;
        }

        .checkbox-group label::before {
            display: none;
        }

        .btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #00d9ff, #00ff88);
            border: none;
            border-radius: 6px;
            color: #0a0e27;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 217, 255, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #00d9ff;
            border: 1px solid #00d9ff;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚙️ SYSTEM CONFIGURATION</h1>
        <a href="/admin/" class="btn btn-secondary">← COMMAND BRIDGE</a>
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

        <div class="intro">
            <p>
                <strong>⚙️ CONFIGURATION PANEL</strong> - Modify system parameters and operational settings.
                Changes to these values will affect the entire platform behavior.
            </p>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

            <div class="settings-card">
                <h2>🌐 General Settings</h2>

                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input
                        type="text"
                        id="site_name"
                        name="site_name"
                        value="<?php echo e($current_settings['site_name']); ?>"
                        required
                    >
                    <div class="hint">The name of your blog</div>
                </div>

                <div class="form-group">
                    <label for="site_tagline">Site Tagline</label>
                    <input
                        type="text"
                        id="site_tagline"
                        name="site_tagline"
                        value="<?php echo e($current_settings['site_tagline']); ?>"
                    >
                    <div class="hint">A short description or motto</div>
                </div>

                <div class="form-group">
                    <label for="site_description">Site Description</label>
                    <textarea
                        id="site_description"
                        name="site_description"
                    ><?php echo e($current_settings['site_description']); ?></textarea>
                    <div class="hint">Used for SEO meta description</div>
                </div>
            </div>

            <div class="settings-card">
                <h2>📄 Display Settings</h2>

                <div class="form-group">
                    <label for="posts_per_page">Posts Per Page (Public)</label>
                    <input
                        type="number"
                        id="posts_per_page"
                        name="posts_per_page"
                        value="<?php echo e($current_settings['posts_per_page']); ?>"
                        min="1"
                        max="100"
                        required
                    >
                    <div class="hint">Number of posts to show on homepage</div>
                </div>

                <div class="form-group">
                    <label for="admin_posts_per_page">Posts Per Page (Admin)</label>
                    <input
                        type="number"
                        id="admin_posts_per_page"
                        name="admin_posts_per_page"
                        value="<?php echo e($current_settings['admin_posts_per_page']); ?>"
                        min="1"
                        max="100"
                        required
                    >
                    <div class="hint">Number of posts to show in admin panel</div>
                </div>
            </div>

            <div class="settings-card">
                <h2>📊 Analytics</h2>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input
                            type="checkbox"
                            id="analytics_enabled"
                            name="analytics_enabled"
                            <?php echo $current_settings['analytics_enabled'] === '1' ? 'checked' : ''; ?>
                        >
                        <label for="analytics_enabled">Enable view tracking</label>
                    </div>
                    <div class="hint">Track page views for posts</div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">💾 Save Settings</button>
                <a href="/admin/" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        console.log('%c⚙️ SYSTEM CONFIGURATION PANEL ONLINE', 'color: #00d9ff; font-size: 14px; font-weight: bold;');
        console.log('%cAdjust operational parameters with caution', 'color: #00ff88; font-size: 12px;');
    </script>
</body>
</html>
