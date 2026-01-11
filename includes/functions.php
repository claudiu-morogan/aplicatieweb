<?php
/**
 * Helper Functions
 */

/**
 * Sanitize and escape output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL-friendly slug
 */
function slugify($text) {
    // Transliterate Romanian characters
    $text = str_replace(
        ['ă', 'â', 'î', 'ș', 'ț', 'Ă', 'Â', 'Î', 'Ș', 'Ț'],
        ['a', 'a', 'i', 's', 't', 'A', 'A', 'I', 'S', 'T'],
        $text
    );

    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return empty($text) ? 'n-a' : $text;
}

/**
 * Calculate reading time in minutes
 */
function calculateReadingTime($content) {
    $wordCount = str_word_count(strip_tags($content));
    $minutes = ceil($wordCount / 200); // Average reading speed: 200 words/min
    return max(1, $minutes);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'j F Y') {
    if (!$date) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);

    // Romanian month names
    $months = [
        'January' => 'Ianuarie', 'February' => 'Februarie', 'March' => 'Martie',
        'April' => 'Aprilie', 'May' => 'Mai', 'June' => 'Iunie',
        'July' => 'Iulie', 'August' => 'August', 'September' => 'Septembrie',
        'October' => 'Octombrie', 'November' => 'Noiembrie', 'December' => 'Decembrie'
    ];

    $formatted = date($format, $timestamp);
    return str_replace(array_keys($months), array_values($months), $formatted);
}

/**
 * Time ago format
 */
function timeAgo($datetime) {
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return 'acum ' . $diff . ' secunde';
    if ($diff < 3600) return 'acum ' . floor($diff / 60) . ' minute';
    if ($diff < 86400) return 'acum ' . floor($diff / 3600) . ' ore';
    if ($diff < 604800) return 'acum ' . floor($diff / 86400) . ' zile';

    return formatDate($datetime);
}

/**
 * Truncate text
 */
function truncate($text, $length = 150, $suffix = '...') {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Get setting value
 */
function getSetting($key, $default = null) {
    static $cache = [];

    if (isset($cache[$key])) {
        return $cache[$key];
    }

    try {
        $stmt = db()->prepare("SELECT setting_value, setting_type FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();

        if (!$result) {
            return $default;
        }

        $value = $result['setting_value'];

        // Cast based on type
        switch ($result['setting_type']) {
            case 'number':
                $value = (int)$value;
                break;
            case 'boolean':
                $value = (bool)$value;
                break;
            case 'json':
                $value = json_decode($value, true);
                break;
        }

        $cache[$key] = $value;
        return $value;
    } catch (PDOException $e) {
        error_log("getSetting error: " . $e->getMessage());
        return $default;
    }
}

/**
 * Update setting value
 */
function updateSetting($key, $value, $type = 'string') {
    try {
        if ($type === 'json') {
            $value = json_encode($value);
        } elseif ($type === 'boolean') {
            $value = $value ? '1' : '0';
        }

        $stmt = db()->prepare("
            INSERT INTO settings (setting_key, setting_value, setting_type)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = ?, setting_type = ?
        ");

        return $stmt->execute([$key, $value, $type, $value, $type]);
    } catch (PDOException $e) {
        error_log("updateSetting error: " . $e->getMessage());
        return false;
    }
}

/**
 * Redirect helper
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Get uploaded file extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Generate random filename
 */
function generateFilename($originalFilename) {
    $ext = getFileExtension($originalFilename);
    return bin2hex(random_bytes(16)) . '.' . $ext;
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Check if string is valid JSON
 */
function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Simple Markdown parser (basic support)
 */
function parseMarkdown($text) {
    // Headers
    $text = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $text);

    // Bold
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/__(.*?)__/', '<strong>$1</strong>', $text);

    // Italic
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);

    // Links
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $text);

    // Images
    $text = preg_replace('/!\[([^\]]*)\]\(([^\)]+)\)/', '<img src="$2" alt="$1">', $text);

    // Code blocks
    $text = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $text);

    // Inline code
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);

    // Lists (basic)
    $text = preg_replace('/^\* (.*?)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);

    // Paragraphs
    $text = preg_replace('/\n\n/', '</p><p>', $text);
    $text = '<p>' . $text . '</p>';

    return $text;
}

/**
 * Get base URL
 */
function baseUrl($path = '') {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Get asset URL
 */
function asset($path) {
    return baseUrl('assets/' . ltrim($path, '/'));
}

/**
 * Include view with data
 */
function view($viewPath, $data = []) {
    extract($data);
    include $viewPath;
}

/**
 * Save tags for a post
 */
function saveTags($post_id, $tags_input) {
    // First, delete existing tags for this post
    $delete_stmt = db()->prepare("DELETE FROM post_tags WHERE post_id = ?");
    $delete_stmt->execute([$post_id]);

    // Parse tags from input (comma-separated)
    $tags = array_map('trim', explode(',', $tags_input));
    $tags = array_filter($tags); // Remove empty values

    foreach ($tags as $tag_name) {
        if (empty($tag_name)) continue;

        $tag_slug = slugify($tag_name);

        // Check if tag exists
        $stmt = db()->prepare("SELECT id FROM tags WHERE slug = ?");
        $stmt->execute([$tag_slug]);
        $tag = $stmt->fetch();

        if ($tag) {
            $tag_id = $tag['id'];
        } else {
            // Create new tag
            $insert_stmt = db()->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
            $insert_stmt->execute([$tag_name, $tag_slug]);
            $tag_id = db()->lastInsertId();
        }

        // Link tag to post
        $link_stmt = db()->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
        $link_stmt->execute([$post_id, $tag_id]);
    }
}

/**
 * Get tags for a post
 */
function getTags($post_id) {
    $stmt = db()->prepare("
        SELECT t.name
        FROM tags t
        JOIN post_tags pt ON t.id = pt.tag_id
        WHERE pt.post_id = ?
        ORDER BY t.name ASC
    ");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get all tags with post count
 */
function getAllTags() {
    $stmt = db()->query("
        SELECT t.*, COUNT(pt.post_id) as post_count
        FROM tags t
        LEFT JOIN post_tags pt ON t.id = pt.tag_id
        GROUP BY t.id
        HAVING post_count > 0
        ORDER BY t.name ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Get enabled widgets
 */
function getEnabledWidgets() {
    $stmt = db()->query("
        SELECT * FROM widgets
        WHERE is_enabled = 1
        ORDER BY display_order ASC, id ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Get all widgets
 */
function getAllWidgets() {
    $stmt = db()->query("
        SELECT * FROM widgets
        ORDER BY display_order ASC, id ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Update widget status
 */
function updateWidgetStatus($widget_id, $is_enabled) {
    $stmt = db()->prepare("UPDATE widgets SET is_enabled = ? WHERE id = ?");
    return $stmt->execute([$is_enabled ? 1 : 0, $widget_id]);
}

/**
 * Get widget by key
 */
function getWidget($widget_key) {
    $stmt = db()->prepare("SELECT * FROM widgets WHERE widget_key = ?");
    $stmt->execute([$widget_key]);
    return $stmt->fetch();
}

/**
 * Get all categories
 */
function getAllCategories() {
    $stmt = db()->query("
        SELECT * FROM categories
        ORDER BY name ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Get category by slug
 */
function getCategoryBySlug($slug) {
    $stmt = db()->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Get category by ID
 */
function getCategoryById($id) {
    $stmt = db()->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Update category post count
 */
function updateCategoryPostCount($category_id) {
    $stmt = db()->prepare("
        UPDATE categories
        SET post_count = (
            SELECT COUNT(*) FROM posts WHERE category_id = ? AND status = 'published'
        )
        WHERE id = ?
    ");
    return $stmt->execute([$category_id, $category_id]);
}

// =============================================
// REMINDERS FUNCTIONS
// =============================================

/**
 * Get all reminders for a user
 */
function getUserReminders($user_id, $include_completed = false) {
    $sql = "SELECT * FROM reminders WHERE user_id = ?";
    if (!$include_completed) {
        $sql .= " AND is_completed = 0";
    }
    $sql .= " ORDER BY due_date ASC, priority DESC, created_at DESC";

    $stmt = db()->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Get a single reminder by ID (with user ownership check)
 */
function getReminder($reminder_id, $user_id) {
    $stmt = db()->prepare("SELECT * FROM reminders WHERE id = ? AND user_id = ?");
    $stmt->execute([$reminder_id, $user_id]);
    return $stmt->fetch();
}

/**
 * Create a new reminder
 */
function createReminder($user_id, $title, $description = '', $due_date = null, $priority = 'medium') {
    try {
        $stmt = db()->prepare("
            INSERT INTO reminders (user_id, title, description, due_date, priority)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $title, $description, $due_date, $priority]);
        return db()->lastInsertId();
    } catch (PDOException $e) {
        error_log("createReminder error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a reminder
 */
function updateReminder($reminder_id, $user_id, $title, $description = '', $due_date = null, $priority = 'medium') {
    try {
        $stmt = db()->prepare("
            UPDATE reminders
            SET title = ?, description = ?, due_date = ?, priority = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$title, $description, $due_date, $priority, $reminder_id, $user_id]);
    } catch (PDOException $e) {
        error_log("updateReminder error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a reminder
 */
function deleteReminder($reminder_id, $user_id) {
    try {
        $stmt = db()->prepare("DELETE FROM reminders WHERE id = ? AND user_id = ?");
        return $stmt->execute([$reminder_id, $user_id]);
    } catch (PDOException $e) {
        error_log("deleteReminder error: " . $e->getMessage());
        return false;
    }
}

/**
 * Toggle reminder completion status
 */
function toggleReminderComplete($reminder_id, $user_id) {
    try {
        // Get current status
        $reminder = getReminder($reminder_id, $user_id);
        if (!$reminder) {
            return false;
        }

        $new_status = $reminder['is_completed'] ? 0 : 1;
        $completed_at = $new_status ? date('Y-m-d H:i:s') : null;

        $stmt = db()->prepare("
            UPDATE reminders
            SET is_completed = ?, completed_at = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$new_status, $completed_at, $reminder_id, $user_id]);
    } catch (PDOException $e) {
        error_log("toggleReminderComplete error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get reminder count for user
 */
function getReminderCount($user_id, $include_completed = false) {
    $sql = "SELECT COUNT(*) FROM reminders WHERE user_id = ?";
    if (!$include_completed) {
        $sql .= " AND is_completed = 0";
    }

    $stmt = db()->prepare($sql);
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Get overdue reminders count
 */
function getOverdueRemindersCount($user_id) {
    $stmt = db()->prepare("
        SELECT COUNT(*) FROM reminders
        WHERE user_id = ? AND is_completed = 0 AND due_date < CURDATE()
    ");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Get upcoming reminders (next 7 days)
 */
function getUpcomingReminders($user_id, $days = 7) {
    $stmt = db()->prepare("
        SELECT * FROM reminders
        WHERE user_id = ? AND is_completed = 0
        AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
        ORDER BY due_date ASC, priority DESC
    ");
    $stmt->execute([$user_id, $days]);
    return $stmt->fetchAll();
}
