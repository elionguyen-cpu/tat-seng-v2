<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="content-area content-area--front-page">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('content-card'); ?>>
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <section class="content-card">
            <h1><?php echo esc_html__('Welcome to TAT SENG', 'tat-seng'); ?></h1>
            <p><?php echo esc_html__('Add front page content in WordPress to replace this fallback.', 'tat-seng'); ?></p>
        </section>
    <?php endif; ?>
</div>

<?php
get_footer();
