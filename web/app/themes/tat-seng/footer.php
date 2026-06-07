<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

?>
</main>
<?php if (!theme_render_theme_footer()) : ?>
    <footer class="site-footer site-footer--fallback">
        <div class="site-footer__inner">
            <div class="site-footer__brand">
                <?php echo esc_html(get_bloginfo('name')); ?>
            </div>
            <nav class="site-footer__navigation" aria-label="<?php echo esc_attr__('Footer Menu', 'tat-seng'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'container' => false,
                    'fallback_cb' => false,
                    'menu_class' => 'site-footer__menu',
                    'depth' => 1,
                ]);
    ?>
            </nav>
        </div>
    </footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
