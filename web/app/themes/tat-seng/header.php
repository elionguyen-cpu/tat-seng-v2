<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if (!theme_render_theme_header()) : ?>
    <header class="site-header site-header--fallback">
        <div class="site-header__inner">
            <a class="site-branding" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    echo '<span class="site-title">' . esc_html(get_bloginfo('name')) . '</span>';
                }
?>
            </a>
            <nav class="site-navigation" aria-label="<?php echo esc_attr__('Primary Menu', 'tat-seng'); ?>">
                <?php
wp_nav_menu([
    'theme_location' => 'primary',
    'container' => false,
    'fallback_cb' => false,
    'menu_class' => 'site-navigation__menu',
    'depth' => 2,
]);
?>
            </nav>
        </div>
    </header>
<?php endif; ?>
<main id="primary" class="site-main">
