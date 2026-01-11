<?php
/**
 * User Management - Manage blog users
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
Auth::requireLogin('/admin/login.php');

$user = Auth::user();
$error = '';
$success = '';

// Only admin can manage users
if ($user['role'] !== 'admin') {
    redirect('/admin/');
}

// Handle user creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrfToken($csrf_token)) {
        $error = 'Security validation failed.';
    } else {
        $action = $_POST['action'];

        if ($action === 'create') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $full_name = trim($_POST['full_name'] ?? '');
            $role = $_POST['role'] ?? 'author';

            if (empty($username) || empty($email) || empty($password)) {
                $error = 'Username, email, and password are required.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } else {
                try {
                    // Check if username/email already exists
                    $check_stmt = db()->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                    $check_stmt->execute([$username, $email]);
                    if ($check_stmt->fetch()) {
                        $error = 'Username or email already exists.';
                    } else {
                        $password_hash = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = db()->prepare("INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$username, $email, $password_hash, $full_name, $role]);
                        $success = 'User created successfully!';
                    }
                } catch (PDOException $e) {
                    error_log("User create error: " . $e->getMessage());
                    $error = 'Database error creating user.';
                }
            }
        } elseif ($action === 'update') {
            $user_id = (int)$_POST['user_id'];
            $email = trim($_POST['email'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $role = $_POST['role'] ?? 'author';
            $new_password = $_POST['new_password'] ?? '';

            if (empty($email)) {
                $error = 'Email is required.';
            } else {
                try {
                    if (!empty($new_password)) {
                        if (strlen($new_password) < 6) {
                            $error = 'Password must be at least 6 characters.';
                        } else {
                            $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                            $stmt = db()->prepare("UPDATE users SET email = ?, full_name = ?, role = ?, password_hash = ? WHERE id = ?");
                            $stmt->execute([$email, $full_name, $role, $password_hash, $user_id]);
                            $success = 'User updated successfully! Password changed.';
                        }
                    } else {
                        $stmt = db()->prepare("UPDATE users SET email = ?, full_name = ?, role = ? WHERE id = ?");
                        $stmt->execute([$email, $full_name, $role, $user_id]);
                        $success = 'User updated successfully!';
                    }
                } catch (PDOException $e) {
                    error_log("User update error: " . $e->getMessage());
                    $error = 'Database error updating user.';
                }
            }
        } elseif ($action === 'delete') {
            $user_id = (int)$_POST['user_id'];

            // Don't allow deleting yourself
            if ($user_id === $user['id']) {
                $error = 'You cannot delete your own account.';
            } else {
                try {
                    $stmt = db()->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $success = 'User deleted successfully!';
                } catch (PDOException $e) {
                    error_log("User delete error: " . $e->getMessage());
                    $error = 'Error deleting user. User may have posts.';
                }
            }
        }
    }
}

// Get all users with post counts
try {
    $stmt = db()->query("
        SELECT u.*,
               COUNT(DISTINCT p.id) as post_count,
               MAX(p.created_at) as last_post_date
        FROM users u
        LEFT JOIN posts p ON u.id = p.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Users list error: " . $e->getMessage());
    $users = [];
}

$csrf_token = Auth::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users | Admin</title>
    <link rel="stylesheet" href="/assets/css/admin-starship.css">
    <style>
        .container {
            max-width: 1400px;
        }

        .create-user-section {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 0;
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
        input[type="password"],
        select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid #00d9ff;
            border-radius: 6px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
        }

        select {
            cursor: pointer;
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

        .btn-small {
            padding: 8px 16px;
            font-size: 0.75rem;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff4444, #ff6b6b);
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            overflow: hidden;
        }

        thead {
            background: rgba(0, 217, 255, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 217, 255, 0.2);
        }

        th {
            color: #00ff88;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:hover {
            background: rgba(0, 217, 255, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .badge-admin {
            background: rgba(0, 217, 255, 0.2);
            color: #00d9ff;
            border: 1px solid #00d9ff;
        }

        .badge-author {
            background: rgba(0, 255, 136, 0.2);
            color: #00ff88;
            border: 1px solid #00ff88;
        }

        .badge-editor {
            background: rgba(255, 165, 0, 0.2);
            color: #ffa500;
            border: 1px solid #ffa500;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            margin-right: 10px;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
            animation: avatarglow 2s ease-in-out infinite;
        }

        @keyframes avatarglow {
            0%, 100% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.5); }
            50% { box-shadow: 0 0 30px rgba(102, 126, 234, 0.8); }
        }

        .edit-form {
            display: none;
            margin-top: 10px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
        }

        .edit-form.active {
            display: block;
        }

        .form-inline {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👥 User Management</h1>
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

        <div class="create-user-section">
            <h2 style="color: #00ff88; margin-bottom: 20px;">➕ Create New User</h2>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                <input type="hidden" name="action" value="create">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <option value="author">Author</option>
                            <option value="editor">Editor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn" style="width: 100%;">Create User</button>
                    </div>
                </div>
            </form>
        </div>

        <h2 style="color: #00d9ff; margin-bottom: 20px;">All Users (<?php echo count($users); ?>)</h2>

        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Posts</th>
                    <th>Last Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $usr): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <div class="user-avatar"><?php echo strtoupper(substr($usr['username'], 0, 1)); ?></div>
                                <div>
                                    <strong><?php echo e($usr['full_name'] ?: $usr['username']); ?></strong><br>
                                    <small style="color: #6b7280;">@<?php echo e($usr['username']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo e($usr['email']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo e($usr['role']); ?>">
                                <?php echo e(ucfirst($usr['role'])); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($usr['post_count']); ?></td>
                        <td>
                            <?php if ($usr['last_post_date']): ?>
                                <?php echo timeAgo($usr['last_post_date']); ?>
                            <?php else: ?>
                                <span style="color: #6b7280;">No posts yet</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button onclick="toggleEdit(<?php echo $usr['id']; ?>)" class="btn btn-small">Edit</button>
                                <?php if ($usr['id'] !== $user['id']): ?>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $usr['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Delete this user? Their posts will remain.');">
                                            Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" style="padding: 0; border: none;">
                            <div id="edit-<?php echo $usr['id']; ?>" class="edit-form">
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="user_id" value="<?php echo $usr['id']; ?>">

                                    <div class="form-inline">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" value="<?php echo e($usr['email']); ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input type="text" name="full_name" value="<?php echo e($usr['full_name']); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Role</label>
                                            <select name="role">
                                                <option value="author" <?php echo $usr['role'] === 'author' ? 'selected' : ''; ?>>Author</option>
                                                <option value="editor" <?php echo $usr['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                                <option value="admin" <?php echo $usr['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>New Password (optional)</label>
                                            <input type="password" name="new_password" placeholder="Leave blank to keep">
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 10px;">
                                        <button type="submit" class="btn btn-small">Save Changes</button>
                                        <button type="button" onclick="toggleEdit(<?php echo $usr['id']; ?>)" class="btn btn-small btn-secondary">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        console.log('%c👥 USER MANAGEMENT ACTIVE', 'color: #00d9ff; font-size: 14px; font-weight: bold;');

        function toggleEdit(userId) {
            const form = document.getElementById('edit-' + userId);
            form.classList.toggle('active');
        }
    </script>
</body>
</html>
