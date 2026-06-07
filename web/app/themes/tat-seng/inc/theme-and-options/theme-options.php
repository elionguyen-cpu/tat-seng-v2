<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function theme_builder_post_types(): array
{
    return ['theme_header', 'theme_footer'];
}

function theme_builder_post_type_args(string $singular, string $plural): array
{
    $singular_lower = strtolower($singular);

    return [
        'labels' => [
            'name' => $plural,
            'singular_name' => $singular,
            'menu_name' => $plural,
            'name_admin_bar' => $singular,
            'add_new' => 'Add ' . $singular,
            'add_new_item' => 'Add New ' . $singular,
            'new_item' => 'New ' . $singular,
            'edit_item' => 'Edit ' . $singular,
            'view_item' => 'View ' . $singular,
            'all_items' => $plural,
            'search_items' => 'Search ' . $plural,
            'parent_item_colon' => 'Parent ' . $singular . ':',
            'not_found' => 'No ' . $singular_lower . 's found.',
            'not_found_in_trash' => 'No ' . $singular_lower . 's found in Trash.',
            'archives' => $singular . ' Archives',
            'attributes' => $singular . ' Attributes',
            'insert_into_item' => 'Insert into ' . $singular_lower,
            'uploaded_to_this_item' => 'Uploaded to this ' . $singular_lower,
            'filter_items_list' => 'Filter ' . $singular_lower . 's list',
            'items_list_navigation' => $plural . ' list navigation',
            'items_list' => $plural . ' list',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'tat-seng-theme-builder',
        'show_in_admin_bar' => true,
        'exclude_from_search' => true,
        'publicly_queryable' => true,
        'query_var' => false,
        'rewrite' => false,
        'has_archive' => false,
        'supports' => ['title', 'editor'],
        'capability_type' => 'post',
        'show_in_rest' => true,
    ];
}

function theme_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('menus');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => esc_html__('Primary Menu', 'tat-seng'),
        'footer' => esc_html__('Footer Menu', 'tat-seng'),
    ]);
}
add_action('after_setup_theme', 'theme_setup');

function theme_enqueue_assets(): void
{
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();
    $css_path = $theme_dir . '/assets/css/main.css';
    $js_path = $theme_dir . '/assets/js/main.js';

    if (file_exists($css_path)) {
        wp_enqueue_style('tat-seng-main', $theme_uri . '/assets/css/main.css', [], (string) filemtime($css_path));
    }

    if (file_exists($js_path)) {
        wp_enqueue_script('tat-seng-main', $theme_uri . '/assets/js/main.js', [], (string) filemtime($js_path), true);
    }
}
add_action('wp_enqueue_scripts', 'theme_enqueue_assets');

function theme_register_admin_menus(): void
{
    add_menu_page(
        esc_html__('Theme Builder', 'tat-seng'),
        esc_html__('Theme Builder', 'tat-seng'),
        'edit_pages',
        'tat-seng-theme-builder',
        'theme_render_theme_builder_landing_page',
        'dashicons-layout',
        59,
    );

    add_theme_page(
        esc_html__('TAT SENG Settings', 'tat-seng'),
        esc_html__('TAT SENG Settings', 'tat-seng'),
        'manage_options',
        'tat-seng-settings',
        'theme_render_settings_page',
    );
}
add_action('admin_menu', 'theme_register_admin_menus');

function theme_render_theme_builder_landing_page(): void
{
    if (!current_user_can('edit_pages')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'tat-seng'));
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Theme Builder', 'tat-seng') . '</h1>';
    echo '<p>' . esc_html__('Use the Headers and Footers sections to manage Elementor-powered structural templates.', 'tat-seng') . '</p>';
    echo '</div>';
}

function theme_sanitize_display_overrides_option(mixed $value): int
{
    return empty($value) ? 0 : 1;
}

function theme_register_settings(): void
{
    register_setting('tat_seng_settings', 'theme_enable_display_overrides', [
        'type' => 'boolean',
        'sanitize_callback' => 'theme_sanitize_display_overrides_option',
        'default' => 0,
    ]);
}
add_action('admin_init', 'theme_register_settings');

function theme_display_overrides_enabled(): bool
{
    return (int) get_option('theme_enable_display_overrides', 0) === 1;
}

function theme_render_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'tat-seng'));
    }

    $theme = wp_get_theme();
    $active_header = theme_get_active_builder_post_id('theme_header');
    $active_footer = theme_get_active_builder_post_id('theme_footer');
    $elementor_support = get_option('elementor_cpt_support', []);
    $elementor_support = is_array($elementor_support) ? implode(', ', array_map('sanitize_key', $elementor_support)) : (string) $elementor_support;
    $wp_default_theme = defined('WP_DEFAULT_THEME') ? (string) WP_DEFAULT_THEME : 'Not defined';

    $rows = [
        'Theme' => $theme->get('Name') . ' ' . $theme->get('Version'),
        'Active stylesheet' => get_stylesheet(),
        'WP_DEFAULT_THEME' => $wp_default_theme,
        'Elementor' => theme_is_elementor_loaded() ? 'Loaded' : 'Not loaded',
        'Elementor builder CPT support' => $elementor_support !== '' ? $elementor_support : 'Not configured',
        'Yoast SEO builder exclusion' => defined('WPSEO_VERSION') || class_exists('WPSEO_Options') ? 'Filters active for builder CPTs' : 'Yoast SEO not loaded',
        'Display Overrides' => theme_display_overrides_enabled() ? 'Enabled' : 'Disabled',
        'Active header' => theme_format_builder_status_value($active_header),
        'Active footer' => theme_format_builder_status_value($active_footer),
        'Page override support' => theme_display_overrides_enabled() ? 'Available and applied' : 'Disabled and ignored on frontend',
    ];

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('TAT SENG Settings', 'tat-seng') . '</h1>';
    echo '<p>' . esc_html__('Theme integration status for Bedrock, Elementor, Yoast SEO, TAT SENG Builder content, and optional TAT SENG feature flags.', 'tat-seng') . '</p>';
    echo '<form method="post" action="options.php">';
    settings_fields('tat_seng_settings');
    echo '<h2>' . esc_html__('Feature Flags', 'tat-seng') . '</h2>';
    echo '<table class="form-table" role="presentation"><tbody><tr>';
    echo '<th scope="row">' . esc_html__('Enable Display Overrides', 'tat-seng') . '</th>';
    echo '<td><label class="tat-seng-switch">';
    echo '<input type="checkbox" name="theme_enable_display_overrides" value="1"' . checked(theme_display_overrides_enabled(), true, false) . '>';
    echo '<span>' . esc_html__('Enable page body mode and Header/Footer overrides.', 'tat-seng') . '</span>';
    echo '</label></td>';
    echo '</tr></tbody></table>';
    submit_button();
    echo '</form>';
    echo '<h2>' . esc_html__('Diagnostics', 'tat-seng') . '</h2>';
    echo '<table class="widefat striped"><tbody>';

    foreach ($rows as $label => $value) {
        echo '<tr><th scope="row">' . esc_html($label) . '</th><td>' . wp_kses_post($value) . '</td></tr>';
    }

    echo '</tbody></table>';
    echo '<p>' . esc_html__("Bedrock should define WP_DEFAULT_THEME from the WP_DEFAULT_THEME environment variable with a tat-seng fallback.", 'tat-seng') . '</p>';
    echo '</div>';
}

function theme_format_builder_status_value(int $post_id): string
{
    if ($post_id <= 0) {
        return esc_html__('None', 'tat-seng');
    }

    $title = get_the_title($post_id);
    $value = $title !== '' ? esc_html($title) : esc_html__('Untitled', 'tat-seng');

    if (current_user_can('edit_post', $post_id)) {
        $edit_link = get_edit_post_link($post_id);
        if (is_string($edit_link) && $edit_link !== '') {
            $value .= ' <a href="' . esc_url($edit_link) . '">' . esc_html__('Edit', 'tat-seng') . '</a>';
        }
    }

    return $value;
}

function theme_is_elementor_loaded(): bool
{
    return did_action('elementor/loaded') > 0 && class_exists('\Elementor\Plugin');
}

function theme_get_elementor_edited_post_id(): int
{
    $keys = ['post', 'post_id', 'editor_post_id', 'elementor-preview', 'preview_id', 'p'];

    foreach ($keys as $key) {
        if (!isset($_REQUEST[$key])) {
            continue;
        }

        $value = absint(wp_unslash($_REQUEST[$key]));
        if ($value > 0 && in_array(get_post_type($value), theme_builder_post_types(), true)) {
            return $value;
        }
    }

    return 0;
}

function theme_is_elementor_editing_builder_post(?string $post_type = null): bool
{
    $post_id = theme_get_elementor_edited_post_id();

    if ($post_id <= 0) {
        return false;
    }

    $current_type = get_post_type($post_id);

    if (!in_array($current_type, theme_builder_post_types(), true)) {
        return false;
    }

    return $post_type === null || $current_type === $post_type;
}

function theme_get_active_builder_post_id(string $post_type): int
{
    if (!in_array($post_type, theme_builder_post_types(), true)) {
        return 0;
    }

    $posts = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'numberposts' => 1,
        'fields' => 'ids',
        'meta_key' => 'is_active',
        'meta_value' => '1',
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
        'suppress_filters' => true,
    ]);

    return isset($posts[0]) ? (int) $posts[0] : 0;
}

function theme_get_current_content_post_id(): int
{
    if (is_singular()) {
        return (int) get_queried_object_id();
    }

    return 0;
}

function theme_resolve_body_mode(): string
{
    if (!theme_display_overrides_enabled()) {
        return 'light';
    }

    $post_id = theme_get_current_content_post_id();
    $mode = $post_id > 0 && get_post_type($post_id) === 'page' ? (string) get_post_meta($post_id, 'theme_body_mode', true) : 'inherit';

    return in_array($mode, ['light', 'dark'], true) ? $mode : 'light';
}

function theme_body_classes(array $classes): array
{
    $classes = array_values(array_filter($classes, static fn($class): bool => !in_array($class, ['light', 'dark'], true)));
    $classes[] = theme_resolve_body_mode();

    return $classes;
}
add_filter('body_class', 'theme_body_classes');

function theme_get_builder_candidates(string $post_type): array
{
    if (!in_array($post_type, theme_builder_post_types(), true)) {
        return [];
    }

    $candidates = [];

    if (theme_display_overrides_enabled() && is_page()) {
        $meta_key = $post_type === 'theme_header' ? 'theme_header_override_id' : 'theme_footer_override_id';
        $override_id = absint(get_post_meta((int) get_queried_object_id(), $meta_key, true));

        if ($override_id > 0 && get_post_type($override_id) === $post_type && get_post_status($override_id) === 'publish') {
            $candidates[] = $override_id;
        }
    }

    $active_id = theme_get_active_builder_post_id($post_type);
    if ($active_id > 0 && !in_array($active_id, $candidates, true)) {
        $candidates[] = $active_id;
    }

    return $candidates;
}

function theme_resolve_header_id(): int
{
    return theme_resolve_builder_id('theme_header');
}

function theme_resolve_footer_id(): int
{
    return theme_resolve_builder_id('theme_footer');
}

function theme_resolve_builder_id(string $post_type): int
{
    foreach (theme_get_builder_candidates($post_type) as $post_id) {
        if (get_post_status($post_id) !== 'publish') {
            continue;
        }

        if (!theme_is_elementor_loaded()) {
            return $post_id;
        }

        if (theme_has_non_empty_rendered_content(theme_get_elementor_rendered_content($post_id))) {
            return $post_id;
        }
    }

    return 0;
}

function theme_get_elementor_rendered_content(int $post_id): string
{
    static $rendering = false;
    static $cache = [];

    if ($post_id <= 0 || get_post_status($post_id) !== 'publish' || !theme_is_elementor_loaded() || $rendering) {
        return '';
    }

    if (isset($cache[$post_id])) {
        return $cache[$post_id];
    }

    try {
        $rendering = true;
        $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($post_id);
        $rendering = false;
    } catch (\Throwable) {
        $rendering = false;
        return '';
    }

    $cache[$post_id] = is_string($content) && theme_has_non_empty_rendered_content($content) ? $content : '';

    return $cache[$post_id];
}

function theme_render_elementor_content(int $post_id): bool
{
    $content = theme_get_elementor_rendered_content($post_id);

    if ($content === '') {
        return false;
    }

    echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    return true;
}

function theme_has_non_empty_rendered_content(string $html): bool
{
    return trim($html) !== '';
}

function theme_register_page_display_meta_box(): void
{
    add_meta_box(
        'theme-page-display-options',
        esc_html__('TAT SENG Display Options', 'tat-seng'),
        'theme_render_page_display_meta_box',
        'page',
        'side',
        'default',
    );
}
add_action('add_meta_boxes_page', 'theme_register_page_display_meta_box');

function theme_render_page_display_meta_box(\WP_Post $post): void
{
    wp_nonce_field('theme_save_page_display_options', 'theme_page_display_options_nonce');

    if (!theme_display_overrides_enabled()) {
        echo '<p>' . esc_html__('Display Overrides are disabled in TAT SENG Settings. Stored page choices are ignored on the frontend.', 'tat-seng') . '</p>';
        return;
    }

    $body_mode = (string) get_post_meta($post->ID, 'theme_body_mode', true);
    $body_mode = in_array($body_mode, ['inherit', 'light', 'dark'], true) ? $body_mode : 'inherit';
    $header_id = absint(get_post_meta($post->ID, 'theme_header_override_id', true));
    $footer_id = absint(get_post_meta($post->ID, 'theme_footer_override_id', true));

    echo '<p><label for="theme_body_mode"><strong>' . esc_html__('Body mode', 'tat-seng') . '</strong></label></p>';
    echo '<select id="theme_body_mode" name="theme_body_mode" class="widefat">';
    foreach (['inherit' => 'Inherit', 'light' => 'Light', 'dark' => 'Dark'] as $value => $label) {
        echo '<option value="' . esc_attr($value) . '"' . selected($body_mode, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';

    theme_render_builder_select('theme_header_override_id', esc_html__('Header override', 'tat-seng'), 'theme_header', $header_id);
    theme_render_builder_select('theme_footer_override_id', esc_html__('Footer override', 'tat-seng'), 'theme_footer', $footer_id);
}

function theme_render_builder_select(string $field_name, string $label, string $post_type, int $selected_id): void
{
    $posts = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'suppress_filters' => true,
    ]);

    echo '<p><label for="' . esc_attr($field_name) . '"><strong>' . esc_html($label) . '</strong></label></p>';
    echo '<select id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" class="widefat">';
    echo '<option value="0">' . esc_html__('Inherit', 'tat-seng') . '</option>';

    foreach ($posts as $post) {
        echo '<option value="' . esc_attr((string) $post->ID) . '"' . selected($selected_id, $post->ID, false) . '>' . esc_html(get_the_title($post)) . '</option>';
    }

    echo '</select>';
}

function theme_save_page_display_options(int $post_id): void
{
    if (!isset($_POST['theme_page_display_options_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['theme_page_display_options_nonce'])), 'theme_save_page_display_options')) {
        return;
    }

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!theme_display_overrides_enabled()) {
        return;
    }

    $body_mode = isset($_POST['theme_body_mode']) ? sanitize_key(wp_unslash($_POST['theme_body_mode'])) : 'inherit';
    $body_mode = in_array($body_mode, ['inherit', 'light', 'dark'], true) ? $body_mode : 'inherit';
    update_post_meta($post_id, 'theme_body_mode', $body_mode);

    foreach (['theme_header_override_id' => 'theme_header', 'theme_footer_override_id' => 'theme_footer'] as $meta_key => $post_type) {
        $value = isset($_POST[$meta_key]) ? absint(wp_unslash($_POST[$meta_key])) : 0;

        if ($value > 0 && (get_post_type($value) !== $post_type || get_post_status($value) !== 'publish')) {
            $value = 0;
        }

        update_post_meta($post_id, $meta_key, $value);
    }
}
add_action('save_post_page', 'theme_save_page_display_options');

function theme_ensure_elementor_cpt_support(): void
{
    if (!is_admin()) {
        return;
    }

    $support = get_option('elementor_cpt_support', []);
    $support = is_array($support) ? array_map('sanitize_key', $support) : [];
    $updated = array_values(array_unique(array_merge($support, theme_builder_post_types())));

    if ($updated !== $support) {
        update_option('elementor_cpt_support', $updated);
    }
}
add_action('admin_init', 'theme_ensure_elementor_cpt_support');

function theme_remove_yoast_builder_metaboxes(): void
{
    foreach (theme_builder_post_types() as $post_type) {
        remove_meta_box('wpseo_meta', $post_type, 'normal');
    }
}
add_action('add_meta_boxes', 'theme_remove_yoast_builder_metaboxes', 99);

function theme_yoast_exclude_builder_post_type(bool $excluded, string $post_type): bool
{
    return in_array($post_type, theme_builder_post_types(), true) ? true : $excluded;
}
add_filter('wpseo_sitemap_exclude_post_type', 'theme_yoast_exclude_builder_post_type', 10, 2);

function theme_yoast_exclude_builder_posts(bool $excluded, string $post_type): bool
{
    return in_array($post_type, theme_builder_post_types(), true) ? true : $excluded;
}
add_filter('wpseo_should_index_post_type', 'theme_yoast_exclude_builder_posts', 10, 2);

function theme_yoast_builder_robots_array(array $robots): array
{
    if (is_singular(theme_builder_post_types())) {
        $robots['index'] = 'noindex';
        $robots['follow'] = 'nofollow';
    }

    return $robots;
}
add_filter('wpseo_robots_array', 'theme_yoast_builder_robots_array');

function theme_yoast_builder_robots(string $robots): string
{
    if (is_singular(theme_builder_post_types())) {
        return 'noindex,nofollow';
    }

    return $robots;
}
add_filter('wpseo_robots', 'theme_yoast_builder_robots');

function theme_yoast_suppress_builder_schema(mixed $data): mixed
{
    if (is_singular(theme_builder_post_types())) {
        return false;
    }

    return $data;
}
add_filter('wpseo_json_ld_output', 'theme_yoast_suppress_builder_schema');
