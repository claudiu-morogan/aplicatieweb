<?php
/**
 * Media Library - Upload and manage media files
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
Auth::requireLogin('/admin/login.php');

$user = Auth::user();
$error = '';
$success = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrfToken($csrf_token)) {
        $error = 'Security validation failed.';
    } else {
        $uploaded = 0;
        $failed = 0;

        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) {
                $failed++;
                continue;
            }

            $original_filename = $_FILES['files']['name'][$key];
            $file_size = $_FILES['files']['size'][$key];
            $mime_type = $_FILES['files']['type'][$key];

            // Validate file type
            if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
                $failed++;
                continue;
            }

            // Validate file size
            if ($file_size > MAX_UPLOAD_SIZE) {
                $failed++;
                continue;
            }

            // Generate unique filename
            $ext = getFileExtension($original_filename);
            $filename = generateFilename($original_filename);
            $file_path = 'media/' . $filename;
            $full_path = PUBLIC_PATH . '/' . $file_path;

            // Move uploaded file
            if (move_uploaded_file($tmp_name, $full_path)) {
                // Get image dimensions
                list($width, $height) = getimagesize($full_path);

                // Save to database
                try {
                    $stmt = db()->prepare("
                        INSERT INTO media (user_id, filename, original_filename, file_path, file_type, file_size, mime_type, width, height)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $user['id'],
                        $filename,
                        $original_filename,
                        '/' . $file_path,
                        $ext,
                        $file_size,
                        $mime_type,
                        $width,
                        $height
                    ]);
                    $uploaded++;
                } catch (PDOException $e) {
                    error_log("Media save error: " . $e->getMessage());
                    unlink($full_path);
                    $failed++;
                }
            } else {
                $failed++;
            }
        }

        if ($uploaded > 0) {
            $success = "$uploaded file(s) uploaded successfully!";
        }
        if ($failed > 0) {
            $error = "$failed file(s) failed to upload.";
        }
    }
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $media_id = $_GET['id'];
    $csrf_token = $_GET['token'] ?? '';

    if (Auth::verifyCsrfToken($csrf_token)) {
        try {
            // Get file info
            $stmt = db()->prepare("SELECT file_path FROM media WHERE id = ?");
            $stmt->execute([$media_id]);
            $media = $stmt->fetch();

            if ($media) {
                // Delete file
                $full_path = PUBLIC_PATH . $media['file_path'];
                if (file_exists($full_path)) {
                    unlink($full_path);
                }

                // Delete from database
                $delete_stmt = db()->prepare("DELETE FROM media WHERE id = ?");
                $delete_stmt->execute([$media_id]);
                $success = 'File deleted successfully!';
            }
        } catch (PDOException $e) {
            error_log("Media delete error: " . $e->getMessage());
            $error = 'Error deleting file.';
        }
    }
}

// Get all media files
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 24;
$offset = ($page - 1) * $per_page;

try {
    $count_stmt = db()->query("SELECT COUNT(*) FROM media");
    $total_files = $count_stmt->fetchColumn();
    $total_pages = ceil($total_files / $per_page);

    $stmt = db()->prepare("
        SELECT m.*, u.username
        FROM media m
        JOIN users u ON m.user_id = u.id
        ORDER BY m.uploaded_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$per_page, $offset]);
    $files = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Media list error: " . $e->getMessage());
    $files = [];
    $total_files = 0;
    $total_pages = 0;
}

$csrf_token = Auth::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library | Admin</title>
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

        .container {
            max-width: 1400px;
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

        .upload-zone {
            background: rgba(0, 217, 255, 0.05);
            border: 2px dashed #00d9ff;
            border-radius: 12px;
            padding: 60px 40px;
            text-align: center;
            margin-bottom: 40px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-zone:hover,
        .upload-zone.dragover {
            background: rgba(0, 217, 255, 0.1);
            border-color: #00ff88;
            transform: scale(1.02);
        }

        .upload-zone h3 {
            color: #00d9ff;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }

        .upload-zone p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        #fileInput {
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

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .media-item {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .media-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 217, 255, 0.3);
            border-color: #00d9ff;
        }

        .media-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .media-info {
            padding: 15px;
        }

        .media-filename {
            font-size: 0.75rem;
            color: #00ff88;
            margin-bottom: 8px;
            word-break: break-all;
        }

        .media-meta {
            font-size: 0.7rem;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .media-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.7rem;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff4444, #ff6b6b);
            color: white;
        }

        .media-url {
            width: 100%;
            padding: 6px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 4px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
            font-size: 0.65rem;
            margin-top: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #6b7280;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            background: rgba(0, 217, 255, 0.1);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 6px;
            color: #00d9ff;
            text-decoration: none;
        }

        .pagination a:hover {
            background: rgba(0, 217, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🖼️ Media Library</h1>
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

        <form id="uploadForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
            <div class="upload-zone" id="uploadZone">
                <h3>📤 Upload Media Files</h3>
                <p>Drag & drop images here or click to browse</p>
                <p style="font-size: 0.8rem; color: #6b7280;">Supported: JPG, PNG, GIF, WebP (Max: <?php echo formatFileSize(MAX_UPLOAD_SIZE); ?>)</p>
                <input type="file" id="fileInput" name="files[]" multiple accept="image/*">
                <button type="button" onclick="document.getElementById('fileInput').click()" class="btn">
                    Choose Files
                </button>
            </div>
        </form>

        <h2 style="color: #00d9ff; margin-bottom: 20px; font-size: 1.2rem;">
            📁 Media Files (<?php echo number_format($total_files); ?>)
        </h2>

        <?php if (empty($files)): ?>
            <div class="empty-state">
                <h3>No media files yet</h3>
                <p>Upload your first image to get started!</p>
            </div>
        <?php else: ?>
            <div class="media-grid">
                <?php foreach ($files as $file): ?>
                    <div class="media-item">
                        <img src="<?php echo e($file['file_path']); ?>" alt="<?php echo e($file['original_filename']); ?>">
                        <div class="media-info">
                            <div class="media-filename"><?php echo e($file['original_filename']); ?></div>
                            <div class="media-meta">
                                <?php echo $file['width']; ?>x<?php echo $file['height']; ?> •
                                <?php echo formatFileSize($file['file_size']); ?> •
                                <?php echo timeAgo($file['uploaded_at']); ?>
                            </div>
                            <input type="text" class="media-url" readonly value="<?php echo e($file['file_path']); ?>" onclick="this.select(); document.execCommand('copy'); alert('URL copied!');">
                            <div class="media-actions">
                                <a href="<?php echo e($file['file_path']); ?>" target="_blank" class="btn btn-small btn-secondary">View</a>
                                <a href="?action=delete&id=<?php echo $file['id']; ?>&token=<?php echo e($csrf_token); ?>"
                                   class="btn btn-small btn-danger"
                                   onclick="return confirm('Delete this file?');">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">← Prev</a>
                    <?php endif; ?>
                    <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        console.log('%c🖼️ MEDIA LIBRARY ACTIVE', 'color: #00d9ff; font-size: 14px; font-weight: bold;');

        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const uploadForm = document.getElementById('uploadForm');

        // Drag and drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');

            const files = e.dataTransfer.files;
            fileInput.files = files;
            uploadForm.submit();
        });

        // Auto-submit on file selection
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                uploadForm.submit();
            }
        });
    </script>
</body>
</html>
