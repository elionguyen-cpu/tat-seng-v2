<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function theme_register_header_post_type(): void
{
    register_post_type('theme_header', theme_builder_post_type_args('Header', 'Headers'));
}
add_action('init', 'theme_register_header_post_type');

function theme_register_header_meta_box(): void
{
    add_meta_box(
        'theme-header-active',
        esc_html__('Header Settings', 'tat-seng'),
        'theme_render_header_meta_box',
        'theme_header',
        'side',
        'high',
    );
}
add_action('add_meta_boxes_theme_header', 'theme_register_header_meta_box');

function theme_render_header_meta_box(\WP_Post $post): void
{
    wp_nonce_field('theme_save_header_settings', 'theme_header_settings_nonce');
    $is_active = get_post_meta($post->ID, 'is_active', true) === '1';

    echo '<label>';
    echo '<input type="checkbox" name="theme_header_is_active" value="1"' . checked($is_active, true, false) . '> ';
    echo esc_html__('Set as active Header', 'tat-seng');
    echo '</label>';
}

function theme_save_header_settings(int $post_id): void
{
    if (!isset($_POST['theme_header_settings_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['theme_header_settings_nonce'])), 'theme_save_header_settings')) {
        return;
    }

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $is_active = isset($_POST['theme_header_is_active']) ? 1 : 0;
    update_post_meta($post_id, 'is_active', (string) $is_active);

    if ($is_active === 1) {
        $headers = get_posts([
            'post_type' => 'theme_header',
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids',
            'exclude' => [$post_id],
            'suppress_filters' => true,
        ]);

        foreach ($headers as $header_id) {
            update_post_meta((int) $header_id, 'is_active', '0');
        }
    }
}
add_action('save_post_theme_header', 'theme_save_header_settings');

function theme_add_header_active_column(array $columns): array
{
    $columns['theme_active'] = esc_html__('Active', 'tat-seng');

    return $columns;
}
add_filter('manage_theme_header_posts_columns', 'theme_add_header_active_column');

function theme_render_header_active_column(string $column, int $post_id): void
{
    if ($column === 'theme_active') {
        echo get_post_meta($post_id, 'is_active', true) === '1' ? esc_html__('Yes', 'tat-seng') : esc_html__('No', 'tat-seng');
    }
}
add_action('manage_theme_header_posts_custom_column', 'theme_render_header_active_column', 10, 2);

function theme_render_theme_header(): bool
{
    static $rendering = false;

    if ($rendering || theme_is_elementor_editing_builder_post()) {
        return true;
    }

    $post_id = theme_resolve_header_id();

    if ($post_id <= 0) {
        return false;
    }

    $content = theme_get_elementor_rendered_content($post_id);

    if ($content === '') {
        return false;
    }

    $rendering = true;
    echo '<header class="site-header site-header--elementor">';
    echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '</header>';
    $rendering = false;

    return true;
}
