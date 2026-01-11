<?php
/**
 * Blog Posts Widget - Display blog post listing
 */

function renderBlogPosts($widget, $posts, $page, $total_pages, $tag_filter = null) {
    $settings = json_decode($widget['settings'], true);
    $show_pagination = $settings['show_pagination'] ?? true;
    ?>

    <div class="blog-posts-widget" data-widget-id="<?php echo $widget['id']; ?>">
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <h2>Nicio postare încă</h2>
                <p>Prima postare va apărea în curând!</p>
                <br>
                <a href="/admin/editor.php" class="btn">Creează prima postare →</a>
            </div>
        <?php else: ?>
            <div class="posts-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card" onclick="window.location.href='/post.php?slug=<?php echo e($post['slug']); ?>'">
                        <h2><a href="/post.php?slug=<?php echo e($post['slug']); ?>"><?php echo e($post['title']); ?></a></h2>
                        <div class="post-meta">
                            <span>📅 <?php echo formatDate($post['published_at'] ?? $post['created_at'], 'd M Y'); ?></span>
                            <span>⏱️ <?php echo $post['reading_time']; ?> min</span>
                            <span>👁️ <?php echo number_format($post['views']); ?></span>
                        </div>
                        <?php if ($post['excerpt']): ?>
                            <p class="post-excerpt"><?php echo e(truncate($post['excerpt'], 150)); ?></p>
                        <?php endif; ?>
                        <a href="/post.php?slug=<?php echo e($post['slug']); ?>" class="read-more">Citește mai mult →</a>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($show_pagination && $total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $tag_filter ? '&tag='.urlencode($tag_filter) : ''; ?>">← Anterior</a>
                    <?php endif; ?>

                    <span>Pagina <?php echo $page; ?> din <?php echo $total_pages; ?></span>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $tag_filter ? '&tag='.urlencode($tag_filter) : ''; ?>">Următorul →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <style>
        /* Blog posts widget styles are inherited from main page */
    </style>
    <?php
}
