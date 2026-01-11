<?php
/**
 * Widgets Management - Control Homepage Widgets
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
Auth::requireLogin('/admin/login.php');

$user = Auth::user();
$error = '';
$success = '';

// Handle widget toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrfToken($csrf_token)) {
        $error = 'Security validation failed.';
    } else {
        $action = $_POST['action'];

        if ($action === 'toggle' && isset($_POST['widget_id'])) {
            $widget_id = (int)$_POST['widget_id'];
            $is_enabled = isset($_POST['is_enabled']) ? 1 : 0;

            try {
                updateWidgetStatus($widget_id, $is_enabled);
                $success = 'Widget status updated successfully!';
            } catch (PDOException $e) {
                error_log("Widget toggle error: " . $e->getMessage());
                $error = 'Error updating widget status.';
            }
        }
    }
}

// Get all widgets
try {
    $widgets = getAllWidgets();
} catch (PDOException $e) {
    error_log("Widgets list error: " . $e->getMessage());
    $widgets = [];
    $error = 'Error loading widgets.';
}

$csrf_token = Auth::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widgets | Admin</title>
    <link rel="stylesheet" href="/assets/css/admin-starship.css">
    <style>

        .intro {
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(0, 217, 255, 0.05);
            border-left: 4px solid #00d9ff;
            border-radius: 8px;
        }

        .intro p {
            color: #a0aec0;
            line-height: 1.6;
        }

        .widgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            background:
                linear-gradient(0deg, rgba(0, 217, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 217, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            border-radius: 12px;
            position: relative;
        }

        .widgets-grid::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(0, 217, 255, 0.05), transparent 70%);
            pointer-events: none;
        }

        /* Circular Widget Button */
        .widget-button {
            position: relative;
            text-align: center;
        }

        .widget-circle {
            width: 180px;
            height: 180px;
            margin: 0 auto 15px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
        }

        /* Enabled state - Green */
        .widget-circle.enabled {
            background: radial-gradient(circle at 30% 30%, rgba(0, 255, 136, 0.3), rgba(0, 255, 136, 0.1));
            border: 3px solid #00ff88;
            box-shadow: 0 0 30px rgba(0, 255, 136, 0.6), inset 0 0 30px rgba(0, 255, 136, 0.2);
        }

        .widget-circle.enabled::before {
            content: '';
            position: absolute;
            top: 10%;
            left: 10%;
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background: radial-gradient(circle at 50% 50%, rgba(0, 255, 136, 0.3), transparent);
            animation: pulse-green 2s ease-in-out infinite;
        }

        @keyframes pulse-green {
            0%, 100% {
                transform: scale(1);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        /* Disabled state - Red */
        .widget-circle.disabled {
            background: radial-gradient(circle at 30% 30%, rgba(255, 68, 68, 0.3), rgba(255, 68, 68, 0.1));
            border: 3px solid #ff4444;
            box-shadow: 0 0 30px rgba(255, 68, 68, 0.6), inset 0 0 30px rgba(255, 68, 68, 0.2);
        }

        .widget-circle.disabled::before {
            content: '';
            position: absolute;
            top: 10%;
            left: 10%;
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background: radial-gradient(circle at 50% 50%, rgba(255, 68, 68, 0.3), transparent);
            animation: pulse-red 2s ease-in-out infinite;
        }

        @keyframes pulse-red {
            0%, 100% {
                transform: scale(1);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .widget-circle:hover {
            transform: scale(1.08) rotate(2deg);
        }

        .widget-circle:active {
            transform: scale(0.95);
        }

        .widget-circle.enabled:hover {
            box-shadow: 0 0 50px rgba(0, 255, 136, 0.8), inset 0 0 40px rgba(0, 255, 136, 0.3);
        }

        .widget-circle.disabled:hover {
            box-shadow: 0 0 50px rgba(255, 68, 68, 0.8), inset 0 0 40px rgba(255, 68, 68, 0.3);
        }

        /* Add glow effect on icon */
        .widget-circle:hover > div:first-child {
            filter: drop-shadow(0 0 10px currentColor);
        }

        .widget-name {
            font-size: 1rem;
            font-weight: bold;
            color: #00d9ff;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
            text-shadow: 0 0 10px rgba(0, 217, 255, 0.8);
        }

        .widget-status {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            z-index: 1;
        }

        .widget-circle.enabled .widget-status {
            color: #00ff88;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.8);
        }

        .widget-circle.disabled .widget-status {
            color: #ff6b6b;
            text-shadow: 0 0 10px rgba(255, 68, 68, 0.8);
        }

        .widget-type-label {
            font-size: 0.7rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Hidden checkbox */
        .widget-button input[type="checkbox"] {
            display: none;
        }

        /* Scan line animation on circle */
        .widget-circle::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            animation: circle-scan 3s linear infinite;
            opacity: 0.5;
        }

        .widget-circle.enabled::after {
            color: #00ff88;
        }

        .widget-circle.disabled::after {
            color: #ff4444;
        }

        @keyframes circle-scan {
            0% {
                transform: translateY(0) scaleX(0.5);
                opacity: 0;
            }
            50% {
                opacity: 0.8;
                transform: translateY(90px) scaleX(1);
            }
            100% {
                transform: translateY(180px) scaleX(0.5);
                opacity: 0;
            }
        }

        .widget-settings {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 217, 255, 0.2);
        }

        .setting-item {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 5px 0;
        }

        .setting-label {
            color: #00d9ff;
            font-weight: 600;
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

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .status-enabled {
            background: rgba(0, 255, 136, 0.2);
            color: #00ff88;
            border: 1px solid #00ff88;
        }

        .status-disabled {
            background: rgba(255, 68, 68, 0.2);
            color: #ff6b6b;
            border: 1px solid #ff4444;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚡ WIDGET CONTROL MATRIX</h1>
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
                <strong>⚡ WIDGET CONTROL PANEL</strong> - Activate or deactivate homepage modules from the command interface.
                Click any circular module to toggle its operational status. Green indicates ONLINE, red indicates OFFLINE.
            </p>
        </div>

        <div class="widgets-grid">
            <?php foreach ($widgets as $widget): ?>
                <?php
                $settings = json_decode($widget['settings'], true);

                // Generate short names and icons for circle display
                $widgetData = [
                    'Autumn Countdown' => ['name' => 'AUTUMN', 'icon' => '🍂'],
                    'Christmas Countdown' => ['name' => 'CHRISTMAS', 'icon' => '🎄'],
                    'Blog Posts' => ['name' => 'BLOG', 'icon' => '📝'],
                    'Blog Posts Listing' => ['name' => 'BLOG', 'icon' => '📝'],
                    'Category Filter' => ['name' => 'FILTERS', 'icon' => '📂'],
                    'Personal Reminders' => ['name' => 'REMINDERS', 'icon' => '📝']
                ];
                $data = $widgetData[$widget['widget_name']] ?? ['name' => strtoupper(substr($widget['widget_name'], 0, 8)), 'icon' => '🧩'];
                $shortName = $data['name'];
                $icon = $data['icon'];
                ?>
                <div class="widget-button">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="widget_id" value="<?php echo $widget['id']; ?>">
                        <input
                            type="checkbox"
                            name="is_enabled"
                            id="widget_<?php echo $widget['id']; ?>"
                            <?php echo $widget['is_enabled'] ? 'checked' : ''; ?>
                            onchange="this.form.submit()"
                        >

                        <label for="widget_<?php echo $widget['id']; ?>" class="widget-circle <?php echo $widget['is_enabled'] ? 'enabled' : 'disabled'; ?>">
                            <div style="font-size: 2.5rem; margin-bottom: 10px;"><?php echo $icon; ?></div>
                            <div class="widget-name"><?php echo e($shortName); ?></div>
                            <div class="widget-status"><?php echo $widget['is_enabled'] ? '● ONLINE' : '● OFFLINE'; ?></div>
                        </label>
                    </form>

                    <div class="widget-type-label"><?php echo e(ucfirst($widget['widget_type'])); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($widgets)): ?>
            <div class="alert alert-error">
                <strong>No widgets found.</strong> The widgets table may be empty.
            </div>
        <?php endif; ?>
    </div>

    <script>
        console.log('%c⚡ WIDGET CONTROL MATRIX ONLINE', 'color: #00d9ff; font-size: 14px; font-weight: bold;');
        console.log('%cClick any circular module to toggle status', 'color: #00ff88; font-size: 12px;');
    </script>
</body>
</html>
