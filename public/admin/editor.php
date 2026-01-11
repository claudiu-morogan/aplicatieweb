<?php
/**
 * Post Editor - Create/Edit Posts
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
Auth::requireLogin('/admin/login.php');

$user = Auth::user();
$error = '';
$success = '';
$post_id = $_GET['id'] ?? null;
$post = null;
$current_tags = [];

// Load existing post if editing
if ($post_id) {
    try {
        $stmt = db()->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();

        if (!$post) {
            $error = 'Post not found.';
        } else {
            // Load tags for this post
            $current_tags = getTags($post_id);
        }
    } catch (PDOException $e) {
        error_log("Editor load error: " . $e->getMessage());
        $error = 'Database error loading post.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrfToken($csrf_token)) {
        $error = 'Security validation failed.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content_markdown = $_POST['content_markdown'] ?? ''; // TinyMCE sends HTML
        $excerpt = trim($_POST['excerpt'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $featured_image = trim($_POST['featured_image'] ?? '');
        $tags_input = trim($_POST['tags'] ?? '');
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

        if (empty($title) || empty($content_markdown)) {
            $error = 'Title and content are required.';
        } else {
            $slug = slugify($title);
            // TinyMCE already provides HTML, no need to parse markdown
            $content_html = $content_markdown;
            $reading_time = calculateReadingTime($content_html);

            // Auto-generate excerpt if empty
            if (empty($excerpt)) {
                $excerpt = truncate($content_html, 200);
            }

            try {
                if ($post_id) {
                    // Update existing post
                    $stmt = db()->prepare("
                        UPDATE posts
                        SET title = ?, slug = ?, content = ?, content_markdown = ?, excerpt = ?,
                            featured_image = ?, status = ?, reading_time = ?, category_id = ?,
                            published_at = IF(status = 'published' AND published_at IS NULL, NOW(), published_at)
                        WHERE id = ?
                    ");
                    $stmt->execute([$title, $slug, $content_html, $content_markdown, $excerpt, $featured_image, $status, $reading_time, $category_id, $post_id]);

                    // Update tags
                    saveTags($post_id, $tags_input);

                    $success = 'Post updated successfully!';
                } else {
                    // Create new post
                    $stmt = db()->prepare("
                        INSERT INTO posts (user_id, category_id, title, slug, content, content_markdown, excerpt, featured_image, status, reading_time, published_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, IF(? = 'published', NOW(), NULL))
                    ");
                    $stmt->execute([$user['id'], $category_id, $title, $slug, $content_html, $content_markdown, $excerpt, $featured_image, $status, $reading_time, $status]);
                    $post_id = db()->lastInsertId();

                    // Save tags
                    saveTags($post_id, $tags_input);

                    $success = 'Post created successfully!';

                    // Reload post data
                    $stmt = db()->prepare("SELECT * FROM posts WHERE id = ?");
                    $stmt->execute([$post_id]);
                    $post = $stmt->fetch();
                }
            } catch (PDOException $e) {
                error_log("Editor save error: " . $e->getMessage());
                $error = 'Database error saving post.';
            }
        }
    }
}

$csrf_token = Auth::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post_id ? 'Edit' : 'New'; ?> Post | Admin</title>
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
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

        .editor-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        .editor-main {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 25px;
        }

        .form-group {
            margin-bottom: 25px;
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
            transition: all 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            background: rgba(0, 217, 255, 0.1);
            box-shadow: 0 0 20px rgba(0, 217, 255, 0.3);
            border-color: #00ff88;
        }

        textarea {
            min-height: 400px;
            resize: vertical;
            line-height: 1.6;
        }

        .editor-sidebar {
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 12px;
            padding: 25px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .sidebar-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(0, 217, 255, 0.2);
        }

        .sidebar-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .sidebar-section h3 {
            color: #00ff88;
            font-size: 0.9rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        select {
            width: 100%;
            padding: 10px;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid #00d9ff;
            border-radius: 6px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
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
            letter-spacing: 1px;
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

        .btn-danger {
            background: linear-gradient(135deg, #ff4444, #ff6b6b);
            color: white;
        }

        .markdown-hint {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 5px;
        }

        .preview-toggle {
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .info-box {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            padding: 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #00ff88;
        }

        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }

        /* Quill Editor Styles */
        #editor-container {
            min-height: 500px;
            background: #1a1e3f;
            color: #00d9ff;
            border: 2px solid #00d9ff;
            border-radius: 4px;
        }

        .ql-toolbar.ql-snow {
            background: #0a0e27;
            border: 2px solid #00d9ff;
            border-bottom: 1px solid #00d9ff;
            border-radius: 4px 4px 0 0;
            padding: 10px;
        }

        .ql-container.ql-snow {
            background: #1a1e3f;
            border: 2px solid #00d9ff;
            border-top: none;
            border-radius: 0 0 4px 4px;
            font-size: 16px;
            min-height: 500px;
        }

        .ql-editor {
            color: #e0e0e0;
            min-height: 500px;
            font-size: 16px;
            line-height: 1.8;
        }

        .ql-editor p {
            margin-bottom: 1.2em;
            margin-top: 0;
            line-height: 1.8;
            min-height: 1.2em;
        }

        .ql-editor p + p {
            margin-top: 1em;
        }

        .ql-editor.ql-blank::before {
            color: #00d9ff;
            opacity: 0.6;
            font-style: italic;
        }

        /* Toolbar button styles */
        .ql-snow .ql-stroke {
            stroke: #00d9ff;
        }

        .ql-snow .ql-fill {
            fill: #00d9ff;
        }

        .ql-snow .ql-picker-label {
            color: #00d9ff;
        }

        .ql-snow .ql-picker-options {
            background: #0a0e27;
            border: 1px solid #00d9ff;
        }

        .ql-snow .ql-picker-item:hover {
            background: #1a1e3f;
            color: #00d9ff;
        }

        .ql-snow.ql-toolbar button:hover,
        .ql-snow .ql-toolbar button:hover,
        .ql-snow.ql-toolbar button.ql-active,
        .ql-snow .ql-toolbar button.ql-active {
            color: #00ff88;
        }

        .ql-snow.ql-toolbar button:hover .ql-stroke,
        .ql-snow .ql-toolbar button:hover .ql-stroke,
        .ql-snow.ql-toolbar button.ql-active .ql-stroke,
        .ql-snow .ql-toolbar button.ql-active .ql-stroke {
            stroke: #00ff88;
        }

        .ql-snow.ql-toolbar button:hover .ql-fill,
        .ql-snow .ql-toolbar button:hover .ql-fill,
        .ql-snow.ql-toolbar button.ql-active .ql-fill,
        .ql-snow .ql-toolbar button.ql-active .ql-fill {
            fill: #00ff88;
        }

        /* Hide the original textarea */
        #content_markdown {
            display: none;
        }
    </style>
    <!-- Quill Editor CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- Quill Editor JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</head>
<body>
    <div class="header">
        <h1>✍️ <?php echo $post_id ? 'Edit Post' : 'New Transmission'; ?></h1>
        <div class="header-actions">
            <a href="/admin/" class="btn btn-secondary">← Back to Dashboard</a>
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
                <?php if ($post_id && isset($post['slug'])): ?>
                    <br><a href="/post.php?slug=<?php echo e($post['slug']); ?>" target="_blank" style="color: #00d9ff;">View Post →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

            <div class="editor-grid">
                <div class="editor-main">
                    <div class="form-group">
                        <label for="title">Post Title</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="<?php echo e($post['title'] ?? ''); ?>"
                            placeholder="Enter an engaging title..."
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="content_markdown">Content</label>
                        <!-- Quill Editor Container -->
                        <div id="editor-container"></div>
                        <!-- Hidden textarea to store HTML content -->
                        <textarea
                            id="content_markdown"
                            name="content_markdown"
                            required
                        ><?php echo ($post['content_markdown'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Excerpt (Optional)</label>
                        <textarea
                            id="excerpt"
                            name="excerpt"
                            placeholder="Brief summary (auto-generated if empty)..."
                            style="min-height: 100px;"
                        ><?php echo e($post['excerpt'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="editor-sidebar">
                    <div class="sidebar-section">
                        <h3>📡 Publish</h3>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="draft" <?php echo ($post['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-top: 15px;">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="">No Category</option>
                                <?php
                                $all_categories = getAllCategories();
                                foreach ($all_categories as $cat):
                                ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($post['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">
                            <?php echo $post_id ? '💾 Update' : '🚀 Publish'; ?>
                        </button>
                    </div>

                    <div class="sidebar-section">
                        <h3>🖼️ Featured Image</h3>
                        <div class="form-group">
                            <label for="featured_image">Image URL</label>
                            <input
                                type="text"
                                id="featured_image"
                                name="featured_image"
                                value="<?php echo e($post['featured_image'] ?? ''); ?>"
                                placeholder="/media/image.jpg"
                            >
                        </div>
                        <a href="/admin/media.php" target="_blank" style="color: #00ff88; font-size: 0.75rem; text-decoration: underline;">Browse Media Library →</a>
                    </div>

                    <div class="sidebar-section">
                        <h3>🏷️ Tags</h3>
                        <div class="form-group">
                            <label for="tags">Tags (comma-separated)</label>
                            <input
                                type="text"
                                id="tags"
                                name="tags"
                                value="<?php echo e(implode(', ', $current_tags)); ?>"
                                placeholder="php, web dev, tutorial"
                            >
                            <div class="markdown-hint">Separate tags with commas</div>
                        </div>
                    </div>

                    <?php if ($post): ?>
                    <div class="sidebar-section">
                        <h3>📊 Stats</h3>
                        <div class="info-box">
                            <strong>Views:</strong> <?php echo number_format($post['views']); ?><br>
                            <strong>Reading Time:</strong> <?php echo $post['reading_time']; ?> min<br>
                            <strong>Created:</strong> <?php echo formatDate($post['created_at'], 'd M Y'); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <script>
        console.log('%c✍️ POST EDITOR ACTIVE', 'color: #00d9ff; font-size: 14px; font-weight: bold;');

        // Initialize Quill WYSIWYG Editor
        var toolbarOptions = [
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'font': [] }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'script': 'sub'}, { 'script': 'super' }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            [{ 'align': [] }],
            ['blockquote', 'code-block'],
            ['link', 'image', 'video'],
            ['clean']
        ];

        var quill = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Write your post content...'
        });

        // Load existing content if editing
        var existingContent = document.getElementById('content_markdown').value;
        if (existingContent) {
            quill.root.innerHTML = existingContent;
        }

        // Function to clean up Quill HTML output
        function getCleanHTML() {
            var html = quill.root.innerHTML;

            // Replace empty paragraphs with just <br> to proper spacing
            html = html.replace(/<p><br><\/p>/g, '<p>&nbsp;</p>');

            // Clean up extra whitespace
            html = html.trim();

            return html;
        }

        // Sync Quill content to hidden textarea on form submit
        var form = document.querySelector('form');
        form.onsubmit = function() {
            var html = getCleanHTML();
            document.getElementById('content_markdown').value = html;
            console.log('Saving content:', html.substring(0, 100) + '...');
        };

        // Auto-sync content every 2 seconds (optional, for auto-save in future)
        setInterval(function() {
            var html = getCleanHTML();
            document.getElementById('content_markdown').value = html;
        }, 2000);

        console.log('Quill editor initialized successfully');

        // Handle image uploads
        quill.getModule('toolbar').addHandler('image', function() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async function() {
                const file = input.files[0];
                if (file) {
                    const formData = new FormData();
                    formData.append('file', file);

                    try {
                        // You can implement image upload endpoint here
                        // For now, we'll use a local URL
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const range = quill.getSelection();
                            quill.insertEmbed(range.index, 'image', e.target.result);
                        };
                        reader.readAsDataURL(file);
                    } catch (error) {
                        console.error('Error uploading image:', error);
                        alert('Error uploading image. Please try again.');
                    }
                }
            };
        });
    </script>
</body>
</html>
