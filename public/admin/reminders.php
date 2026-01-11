<?php
/**
 * Reminders Management - Personal Reminders
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
Auth::requireLogin('/admin/login.php');

$user = Auth::user();
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrfToken($csrf_token)) {
        $error = 'Security validation failed.';
    } else {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $due_date = trim($_POST['due_date'] ?? '') ?: null;
                $priority = $_POST['priority'] ?? 'medium';

                if (empty($title)) {
                    $error = 'Title is required.';
                } else {
                    if (createReminder($user['id'], $title, $description, $due_date, $priority)) {
                        $success = 'Reminder created successfully!';
                    } else {
                        $error = 'Failed to create reminder.';
                    }
                }
                break;

            case 'update':
                $reminder_id = (int)($_POST['reminder_id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $due_date = trim($_POST['due_date'] ?? '') ?: null;
                $priority = $_POST['priority'] ?? 'medium';

                if (empty($title)) {
                    $error = 'Title is required.';
                } else {
                    if (updateReminder($reminder_id, $user['id'], $title, $description, $due_date, $priority)) {
                        $success = 'Reminder updated successfully!';
                    } else {
                        $error = 'Failed to update reminder.';
                    }
                }
                break;

            case 'delete':
                $reminder_id = (int)($_POST['reminder_id'] ?? 0);
                if (deleteReminder($reminder_id, $user['id'])) {
                    $success = 'Reminder deleted successfully!';
                } else {
                    $error = 'Failed to delete reminder.';
                }
                break;

            case 'toggle':
                $reminder_id = (int)($_POST['reminder_id'] ?? 0);
                if (toggleReminderComplete($reminder_id, $user['id'])) {
                    $success = 'Reminder status updated!';
                } else {
                    $error = 'Failed to update reminder status.';
                }
                break;
        }
    }
}

// Get all reminders (including completed)
$reminders = getUserReminders($user['id'], true);
$active_count = getReminderCount($user['id'], false);
$overdue_count = getOverdueRemindersCount($user['id']);

$csrf_token = Auth::generateCsrfToken();

// Editing mode
$editing_id = $_GET['edit'] ?? null;
$editing_reminder = null;
if ($editing_id) {
    $editing_reminder = getReminder($editing_id, $user['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders | Admin</title>
    <link rel="stylesheet" href="/assets/css/admin-starship.css">
    <style>
        .container {
            max-width: 1000px;
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #00d9ff, #00ff88);
            animation: borderflow 3s linear infinite;
        }

        .stat-number {
            font-size: 2.5rem;
            color: #00ff88;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .stat-box.overdue .stat-number {
            color: #ff4444;
            text-shadow: 0 0 10px rgba(255, 68, 68, 0.5);
        }

        .reminder-form {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .reminder-form h2 {
            color: #00ff88;
            margin-bottom: 20px;
            font-size: 1rem;
            text-transform: uppercase;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            color: #00d9ff;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid #00d9ff;
            border-radius: 6px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            transition: all 0.3s;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #00ff88;
            box-shadow: 0 0 20px rgba(0, 217, 255, 0.3);
        }

        .priority-select {
            position: relative;
        }

        .reminders-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .reminder-card {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 20px;
            position: relative;
            transition: all 0.3s;
        }

        .reminder-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 217, 255, 0.2);
        }

        .reminder-card.completed {
            opacity: 0.6;
            border-color: rgba(107, 114, 128, 0.3);
        }

        .reminder-card.completed .reminder-title {
            text-decoration: line-through;
            color: #6b7280;
        }

        .reminder-card.overdue {
            border-color: rgba(255, 68, 68, 0.5);
            background: rgba(255, 68, 68, 0.05);
        }

        .reminder-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .reminder-title {
            font-size: 1.2rem;
            color: #00ff88;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .reminder-description {
            color: #a0aec0;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .reminder-meta {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .priority-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .priority-low {
            background: rgba(0, 217, 255, 0.2);
            color: #00d9ff;
            border: 1px solid #00d9ff;
        }

        .priority-medium {
            background: rgba(255, 165, 0, 0.2);
            color: #ffa500;
            border: 1px solid #ffa500;
        }

        .priority-high {
            background: rgba(255, 68, 68, 0.2);
            color: #ff6b6b;
            border: 1px solid #ff4444;
        }

        .reminder-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.8rem;
        }

        .btn-complete {
            background: linear-gradient(135deg, #00ff88, #00d9ff);
        }

        .btn-edit {
            background: linear-gradient(135deg, #ffa500, #ff8c00);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        @media (max-width: 768px) {
            .stats-bar {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .reminder-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📝 PERSONAL REMINDERS</h1>
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

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-box">
                <div class="stat-number"><?php echo $active_count; ?></div>
                <div class="stat-label">Active Reminders</div>
            </div>

            <div class="stat-box overdue">
                <div class="stat-number"><?php echo $overdue_count; ?></div>
                <div class="stat-label">Overdue</div>
            </div>

            <div class="stat-box">
                <div class="stat-number"><?php echo count($reminders); ?></div>
                <div class="stat-label">Total Reminders</div>
            </div>
        </div>

        <!-- Reminder Form -->
        <div class="reminder-form">
            <h2><?php echo $editing_reminder ? '✏️ Edit Reminder' : '➕ New Reminder'; ?></h2>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                <input type="hidden" name="action" value="<?php echo $editing_reminder ? 'update' : 'create'; ?>">
                <?php if ($editing_reminder): ?>
                    <input type="hidden" name="reminder_id" value="<?php echo $editing_reminder['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Title *</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?php echo e($editing_reminder['title'] ?? ''); ?>"
                        required
                        placeholder="What do you need to remember?"
                    >
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Additional details..."
                    ><?php echo e($editing_reminder['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input
                            type="date"
                            id="due_date"
                            name="due_date"
                            value="<?php echo e($editing_reminder['due_date'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <option value="low" <?php echo ($editing_reminder['priority'] ?? 'medium') === 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo ($editing_reminder['priority'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo ($editing_reminder['priority'] ?? 'medium') === 'high' ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn">
                        <?php echo $editing_reminder ? '💾 Update Reminder' : '➕ Create Reminder'; ?>
                    </button>
                    <?php if ($editing_reminder): ?>
                        <a href="/admin/reminders.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Reminders List -->
        <h2 style="color: #00ff88; margin-bottom: 20px; font-size: 1rem; text-transform: uppercase;">📋 Your Reminders</h2>

        <?php if (empty($reminders)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <p>No reminders yet. Create your first reminder above!</p>
            </div>
        <?php else: ?>
            <div class="reminders-list">
                <?php foreach ($reminders as $reminder): ?>
                    <?php
                    $is_overdue = !$reminder['is_completed'] && $reminder['due_date'] && strtotime($reminder['due_date']) < strtotime('today');
                    $card_class = 'reminder-card';
                    if ($reminder['is_completed']) $card_class .= ' completed';
                    elseif ($is_overdue) $card_class .= ' overdue';
                    ?>
                    <div class="<?php echo $card_class; ?>">
                        <div class="reminder-header">
                            <div style="flex: 1;">
                                <div class="reminder-title">
                                    <?php echo $reminder['is_completed'] ? '✓ ' : ''; ?>
                                    <?php echo e($reminder['title']); ?>
                                </div>
                                <?php if ($reminder['description']): ?>
                                    <div class="reminder-description"><?php echo nl2br(e($reminder['description'])); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="reminder-meta">
                            <?php if ($reminder['due_date']): ?>
                                <div class="meta-item">
                                    📅 <?php echo formatDate($reminder['due_date']); ?>
                                    <?php if ($is_overdue): ?>
                                        <span style="color: #ff4444; font-weight: bold;">(OVERDUE)</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="meta-item">
                                <span class="priority-badge priority-<?php echo $reminder['priority']; ?>">
                                    <?php echo strtoupper($reminder['priority']); ?>
                                </span>
                            </div>

                            <div class="meta-item">
                                🕒 Created <?php echo timeAgo($reminder['created_at']); ?>
                            </div>
                        </div>

                        <div class="reminder-actions">
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="reminder_id" value="<?php echo $reminder['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-complete">
                                    <?php echo $reminder['is_completed'] ? '↩️ Reopen' : '✓ Complete'; ?>
                                </button>
                            </form>

                            <a href="/admin/reminders.php?edit=<?php echo $reminder['id']; ?>" class="btn btn-sm btn-edit">✏️ Edit</a>

                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this reminder?');">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="reminder_id" value="<?php echo $reminder['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️ Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        console.log('%c📝 REMINDERS SYSTEM ONLINE', 'color: #00d9ff; font-size: 14px; font-weight: bold;');
        console.log('%cManage your personal reminders', 'color: #00ff88; font-size: 12px;');
    </script>
</body>
</html>
