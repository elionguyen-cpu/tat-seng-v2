<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="content-area">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('content-card'); ?>>
                <?php if (is_singular()) : ?>
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                <?php else : ?>
                    <h2 class="entry-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                <?php endif; ?>
                <div class="entry-content">
                    <?php
                    if (is_singular()) {
                        the_content();
                    } else {
                        the_excerpt();
                    }
            ?>
                </div>
            </article>
        <?php endwhile; ?>

        <?php if (!is_singular()) : ?>
            <?php wp_paginate(); ?>
        <?php endif; ?>
    <?php else : ?>
        <section class="content-card">
            <h1><?php echo esc_html__('Nothing found', 'tat-seng'); ?></h1>
            <p><?php echo esc_html__('No content is available yet.', 'tat-seng'); ?></p>
        </section>
    <?php endif; ?>
</div>

<?php
get_footer();
