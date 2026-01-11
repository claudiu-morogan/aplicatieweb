<?php
/**
 * Personal Reminders Widget - Display user's reminders on public pages
 * Only visible when user is logged in
 */

function renderPersonalReminders($widget) {
    // Check if user is logged in (admin panel session)
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in'])) {
        return; // Don't display widget if not logged in
    }

    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    $settings = json_decode($widget['settings'], true);
    $max_display = $settings['max_display'] ?? 5;
    $show_completed = $settings['show_completed'] ?? false;

    // Get reminders
    $reminders = getUserReminders($user_id, $show_completed);

    // Limit to max display
    if (count($reminders) > $max_display) {
        $reminders = array_slice($reminders, 0, $max_display);
    }

    $total_count = getReminderCount($user_id, false);
    $overdue_count = getOverdueRemindersCount($user_id);
    ?>

    <div class="reminders-widget" data-widget-id="<?php echo $widget['id']; ?>">
        <div class="widget-header">
            <h2>📝 Memento-uri Personale</h2>
            <a href="/admin/reminders.php" class="manage-link">Gestionează →</a>
        </div>

        <?php if ($total_count === 0): ?>
            <div class="empty-reminders">
                <p>Nu ai memento-uri active.</p>
                <a href="/admin/reminders.php" class="add-reminder-btn">+ Adaugă primul memento</a>
            </div>
        <?php else: ?>
            <div class="reminders-summary">
                <div class="summary-item">
                    <span class="count"><?php echo $total_count; ?></span>
                    <span class="label">Active</span>
                </div>
                <?php if ($overdue_count > 0): ?>
                    <div class="summary-item overdue">
                        <span class="count"><?php echo $overdue_count; ?></span>
                        <span class="label">Întârziate</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="reminders-list">
                <?php foreach ($reminders as $reminder): ?>
                    <?php
                    $is_overdue = !$reminder['is_completed'] && $reminder['due_date'] && strtotime($reminder['due_date']) < strtotime('today');
                    $item_class = 'reminder-item';
                    if ($reminder['is_completed']) $item_class .= ' completed';
                    elseif ($is_overdue) $item_class .= ' overdue';
                    ?>
                    <div class="<?php echo $item_class; ?>">
                        <div class="reminder-content">
                            <div class="reminder-title-row">
                                <span class="priority-dot priority-<?php echo $reminder['priority']; ?>"></span>
                                <div class="reminder-title"><?php echo e($reminder['title']); ?></div>
                            </div>
                            <?php if ($reminder['description']): ?>
                                <div class="reminder-desc"><?php echo e(truncate($reminder['description'], 80)); ?></div>
                            <?php endif; ?>
                            <?php if ($reminder['due_date']): ?>
                                <div class="reminder-due">
                                    📅 <?php echo formatDate($reminder['due_date'], 'd M'); ?>
                                    <?php if ($is_overdue): ?>
                                        <span class="overdue-label">ÎNTÂRZIAT</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($reminders) < $total_count): ?>
                <div class="view-all">
                    <a href="/admin/reminders.php">Vezi toate (<?php echo $total_count; ?>)</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <style>
        .reminders-widget {
            background: var(--bg-secondary);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .reminders-widget:hover {
            box-shadow: 0 2px 8px var(--shadow-hover);
        }

        .widget-header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .widget-header h2 {
            display: none; /* Section header is shown above */
        }

        .manage-link {
            color: var(--accent-red);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s;
            padding: 4px 10px;
            border-radius: 3px;
        }

        .manage-link:hover {
            color: white;
            background: var(--accent-red);
        }

        .empty-reminders {
            text-align: center;
            padding: 30px 15px;
            color: var(--text-secondary);
        }

        .add-reminder-btn {
            display: inline-block;
            margin-top: 12px;
            padding: 8px 16px;
            background: var(--accent-red);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: all 0.2s;
            font-size: 0.85rem;
        }

        .add-reminder-btn:hover {
            background: var(--accent-red-dark);
        }

        .reminders-summary {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .summary-item {
            flex: 1;
            background: var(--bg-primary);
            border-radius: 4px;
            padding: 12px;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .summary-item.overdue {
            background: rgba(239, 68, 68, 0.1);
            border-color: #fecaca;
        }

        .summary-item .count {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-red);
            margin-bottom: 3px;
        }

        .summary-item.overdue .count {
            color: #ef4444;
        }

        .summary-item .label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .reminders-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .reminder-item {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 12px;
            transition: all 0.2s;
        }

        .reminder-item:hover {
            background: var(--card-hover-bg);
            border-color: var(--border-light);
            transform: translateX(3px);
            box-shadow: 0 1px 4px var(--shadow);
        }

        .reminder-item.completed {
            opacity: 0.6;
        }

        .reminder-item.completed .reminder-title {
            text-decoration: line-through;
            color: var(--text-secondary);
        }

        .reminder-item.overdue {
            border-left: 3px solid #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        .reminder-content {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .reminder-title-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .priority-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .priority-dot.priority-low {
            background: #10b981;
        }

        .priority-dot.priority-medium {
            background: #fbbf24;
        }

        .priority-dot.priority-high {
            background: #ef4444;
        }

        .reminder-title {
            font-size: 0.9rem;
            color: var(--text-primary);
            font-weight: 600;
        }

        .reminder-desc {
            font-size: 0.8rem;
            color: var(--text-secondary);
            padding-left: 14px;
            line-height: 1.5;
        }

        .reminder-due {
            font-size: 0.75rem;
            color: var(--text-secondary);
            padding-left: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .overdue-label {
            background: #fecaca;
            color: #dc2626;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .view-all {
            margin-top: 15px;
            text-align: center;
            padding-top: 12px;
            border-top: 1px solid var(--border-color);
        }

        .view-all a {
            color: var(--accent-red);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }

        .view-all a:hover {
            color: white;
            background: var(--accent-red);
        }

        @media (max-width: 768px) {
            .reminders-summary {
                flex-direction: column;
            }

            .widget-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
    <?php
}
