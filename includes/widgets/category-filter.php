<?php
/**
 * Category Filter Widget - Display category filter for public zone
 */

function renderCategoryFilter($widget, $current_category = null) {
    $settings = json_decode($widget['settings'], true);
    $categories = getAllCategories();

    if (empty($categories)) {
        return; // Don't show widget if no categories exist
    }
    ?>

    <div class="category-filter-widget" data-widget-id="<?php echo $widget['id']; ?>">
        <div class="category-filter-header">
            <h3>📂 Categorii</h3>
        </div>

        <div class="category-pills">
            <a href="/" class="category-pill <?php echo !$current_category ? 'active' : ''; ?>">
                <span class="pill-name">Toate</span>
            </a>

            <?php foreach ($categories as $category): ?>
                <?php if ($category['post_count'] > 0): ?>
                    <a href="/?category=<?php echo e($category['slug']); ?>"
                       class="category-pill <?php echo $current_category && $current_category['slug'] === $category['slug'] ? 'active' : ''; ?>"
                       style="border-color: <?php echo e($category['color']); ?>;">
                        <span class="pill-color" style="background-color: <?php echo e($category['color']); ?>;"></span>
                        <span class="pill-name"><?php echo e($category['name']); ?></span>
                        <span class="pill-count"><?php echo $category['post_count']; ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <style>
        .category-filter-widget {
            background: var(--bg-secondary);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px var(--shadow);
            transition: background-color 0.3s ease;
        }

        .category-filter-header h3 {
            display: none; /* Section header is shown above */
        }

        .category-pills {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .category-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-left: 3px solid var(--border-light);
            color: var(--text-primary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
            font-weight: 500;
        }

        .category-pill:hover {
            background: var(--card-hover-bg);
            border-left-color: var(--accent-red);
            transform: translateX(3px);
        }

        .category-pill.active {
            background: rgba(211, 47, 47, 0.1);
            border-left-color: var(--accent-red);
            color: var(--accent-red);
            font-weight: 600;
        }

        .pill-color {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        .pill-name {
            flex: 1;
        }

        .pill-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            padding: 0 8px;
            background: var(--border-color);
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .category-pill.active .pill-count {
            background: var(--accent-red);
            color: white;
        }

        @media (max-width: 768px) {
            .category-pills {
                gap: 6px;
            }

            .category-pill {
                padding: 10px 12px;
                font-size: 0.85rem;
            }
        }
    </style>
    <?php
}
