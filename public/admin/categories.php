<?php
/**
 * Category Management
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
Auth::requireLogin('/admin/login.php');

$user = Auth::user();
$error = '';
$success = '';

// Handle category creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrfToken($csrf_token)) {
        $error = 'Security validation failed.';
    } else {
        $action = $_POST['action'];

        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $color = trim($_POST['color'] ?? '#667eea');

            if (empty($name)) {
                $error = 'Category name is required.';
            } else {
                $slug = slugify($name);

                try {
                    if ($action === 'create') {
                        $stmt = db()->prepare("INSERT INTO categories (name, slug, description, color) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$name, $slug, $description, $color]);
                        $success = 'Category created successfully!';
                    } else {
                        $category_id = (int)$_POST['category_id'];
                        $stmt = db()->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, color = ? WHERE id = ?");
                        $stmt->execute([$name, $slug, $description, $color, $category_id]);
                        $success = 'Category updated successfully!';
                    }
                } catch (PDOException $e) {
                    error_log("Category save error: " . $e->getMessage());
                    $error = 'Database error saving category.';
                }
            }
        } elseif ($action === 'delete') {
            $category_id = (int)$_POST['category_id'];

            try {
                $stmt = db()->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$category_id]);
                $success = 'Category deleted successfully!';
            } catch (PDOException $e) {
                error_log("Category delete error: " . $e->getMessage());
                $error = 'Error deleting category.';
            }
        }
    }
}

// Get all categories
try {
    $categories = getAllCategories();

    // Update post counts
    foreach ($categories as &$category) {
        updateCategoryPostCount($category['id']);
    }

    // Reload to get updated counts
    $categories = getAllCategories();
} catch (PDOException $e) {
    error_log("Categories list error: " . $e->getMessage());
    $categories = [];
}

$csrf_token = Auth::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Consolas, monospace;
            background: #0a0e27;
            color: #00d9ff;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(180deg, #0a0e27 0%, #1a1e3f 100%);
            border-bottom: 2px solid #00d9ff;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.5rem;
            color: #00d9ff;
            text-shadow: 0 0 20px rgba(0, 217, 255, 0.5);
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid #ff4444;
            color: #ff6b6b;
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid #00ff88;
            color: #00ff88;
        }

        .category-form {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 2fr 3fr 1fr auto;
            gap: 15px;
            align-items: end;
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
        textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid #00d9ff;
            border-radius: 6px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
        }

        input[type="color"] {
            width: 100%;
            height: 45px;
            border: 2px solid #00d9ff;
            border-radius: 6px;
            background: rgba(0, 217, 255, 0.05);
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

        .categories-grid {
            display: grid;
            gap: 15px;
        }

        .category-card {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 217, 255, 0.2);
        }

        .category-info {
            flex: 1;
        }

        .category-name {
            font-size: 1.2rem;
            color: #00ff88;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
        }

        .category-description {
            color: #a0aec0;
            margin-bottom: 8px;
        }

        .category-meta {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .category-actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📂 Categories</h1>
        <div class="header-actions">
            <a href="/admin/" class="btn btn-secondary">← Dashboard</a>
        </div>
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

        <div class="category-form">
            <h2 style="color: #00ff88; margin-bottom: 20px;">➕ Add New Category</h2>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                <input type="hidden" name="action" value="create">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" placeholder="Technology, Personal, etc." required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description" placeholder="Brief description...">
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="color" id="color" name="color" value="#667eea">
                    </div>

                    <button type="submit" class="btn">Create</button>
                </div>
            </form>
        </div>

        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <div class="category-card">
                    <div class="category-info">
                        <div class="category-name">
                            <span class="category-color" style="background-color: <?php echo e($category['color']); ?>"></span>
                            <?php echo e($category['name']); ?>
                        </div>
                        <?php if ($category['description']): ?>
                            <div class="category-description"><?php echo e($category['description']); ?></div>
                        <?php endif; ?>
                        <div class="category-meta">
                            Slug: <strong><?php echo e($category['slug']); ?></strong> •
                            Posts: <strong><?php echo number_format($category['post_count']); ?></strong>
                        </div>
                    </div>

                    <div class="category-actions">
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                            <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Delete this category? Posts will not be deleted.');">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($categories)): ?>
            <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                <p>No categories yet. Create your first category above!</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        console.log('%c📂 CATEGORIES MANAGEMENT ACTIVE', 'color: #00d9ff; font-size: 14px; font-weight: bold;');
    </script>
</body>
</html>
