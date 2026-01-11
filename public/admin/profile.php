<?php
/**
 * User Profile - Edit own profile
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
Auth::requireLogin('/admin/login.php');

$user = Auth::user();
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrfToken($csrf_token)) {
        $error = 'Security validation failed.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($email)) {
            $error = 'Email is required.';
        } elseif (!empty($new_password) && $new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (!empty($new_password) && strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            try {
                // If changing password, verify current password
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $error = 'Current password is required to change password.';
                    } else {
                        $stmt = db()->prepare("SELECT password_hash FROM users WHERE id = ?");
                        $stmt->execute([$user['id']]);
                        $stored_hash = $stmt->fetchColumn();

                        if (!password_verify($current_password, $stored_hash)) {
                            $error = 'Current password is incorrect.';
                        } else {
                            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
                            $stmt = db()->prepare("UPDATE users SET email = ?, full_name = ?, password_hash = ? WHERE id = ?");
                            $stmt->execute([$email, $full_name, $new_hash, $user['id']]);
                            $success = 'Profile updated successfully! Password changed.';

                            // Update session
                            $_SESSION['user']['email'] = $email;
                            $_SESSION['user']['full_name'] = $full_name;
                            $user = $_SESSION['user'];
                        }
                    }
                } else {
                    $stmt = db()->prepare("UPDATE users SET email = ?, full_name = ? WHERE id = ?");
                    $stmt->execute([$email, $full_name, $user['id']]);
                    $success = 'Profile updated successfully!';

                    // Update session
                    $_SESSION['user']['email'] = $email;
                    $_SESSION['user']['full_name'] = $full_name;
                    $user = $_SESSION['user'];
                }
            } catch (PDOException $e) {
                error_log("Profile update error: " . $e->getMessage());
                $error = 'Database error updating profile.';
            }
        }
    }
}

// Get user stats
try {
    $stmt = db()->prepare("
        SELECT
            COUNT(*) as total_posts,
            SUM(views) as total_views,
            MAX(created_at) as last_post_date
        FROM posts
        WHERE user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $stats = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Stats error: " . $e->getMessage());
    $stats = ['total_posts' => 0, 'total_views' => 0, 'last_post_date' => null];
}

$csrf_token = Auth::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Admin</title>
    <link rel="stylesheet" href="/assets/css/admin-starship.css">
    <style>
        .container {
            max-width: 900px;
        }

        .profile-header {
            text-align: center;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 40px;
            margin-bottom: 30px;
        }

        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 0 30px rgba(102, 126, 234, 0.6);
            animation: avatarglow 2s ease-in-out infinite;
        }

        @keyframes avatarglow {
            0%, 100% { box-shadow: 0 0 30px rgba(102, 126, 234, 0.6); }
            50% { box-shadow: 0 0 50px rgba(102, 126, 234, 0.9); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            color: #00ff88;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .form-section {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
        }

        .form-section h2 {
            color: #00ff88;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
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
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid #00d9ff;
            border-radius: 6px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
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

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            background: rgba(0, 217, 255, 0.2);
            color: #00d9ff;
            border: 1px solid #00d9ff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👤 My Profile</h1>
        <a href="/admin/" class="btn btn-secondary">← Dashboard</a>
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

        <div class="profile-header">
            <div class="avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
            <h2 style="color: #00d9ff; margin-bottom: 10px;"><?php echo e($user['full_name'] ?: $user['username']); ?></h2>
            <p style="color: #6b7280;">@<?php echo e($user['username']); ?></p>
            <p style="margin-top: 10px;"><span class="badge"><?php echo e(ucfirst($user['role'])); ?></span></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_posts']); ?></div>
                <div class="stat-label">Total Posts</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_views']); ?></div>
                <div class="stat-label">Total Views</div>
            </div>

            <div class="stat-card">
                <div class="stat-value">
                    <?php echo $stats['last_post_date'] ? timeAgo($stats['last_post_date']) : 'Never'; ?>
                </div>
                <div class="stat-label">Last Post</div>
            </div>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

            <div class="form-section">
                <h2>📧 Account Information</h2>

                <div class="form-group">
                    <label for="username">Username (cannot be changed)</label>
                    <input type="text" id="username" value="<?php echo e($user['username']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo e($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo e($user['full_name']); ?>">
                </div>
            </div>

            <div class="form-section">
                <h2>🔒 Change Password</h2>
                <p style="color: #6b7280; margin-bottom: 20px; font-size: 0.9rem;">Leave blank to keep current password</p>

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </div>

            <button type="submit" class="btn" style="width: 100%;">💾 Save Changes</button>
        </form>
    </div>

    <script>
        console.log('%c👤 PROFILE PAGE ACTIVE', 'color: #00d9ff; font-size: 14px; font-weight: bold;');
    </script>
</body>
</html>
