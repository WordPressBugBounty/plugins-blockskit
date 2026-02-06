<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;
/**
 * The Blockskit Import hooks callback functionality of the plugin.
 *
 */
class Bk_Base_Install_Hooks
{

    /**
     * Initialize the class and set its properties.
     *
     */
    public function __construct()
    {
        add_action('wp_ajax_install_base_theme', array($this, 'install_base_theme'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'), 10, 1);
    }

    /**
     * Enqueue styles.
     *
     */
    public function enqueue_styles()
    {

        wp_enqueue_style('bk-base-install', plugin_dir_url(__FILE__) . 'assets/base-install.css', array('wp-admin'), '1.0.0', 'all');
    }

    /**
     * Enqueue scripts.
     *
     */
    public function enqueue_scripts()
    {

        wp_enqueue_script('bk-base-install', plugin_dir_url(__FILE__) . 'assets/base-install.js', array('jquery'), '1.0.0', true);

        // Get all installed theme slugs
        $all_themes = wp_get_themes();
        $installed_themes_slugs = array_keys($all_themes);

        wp_localize_script(
            'bk-base-install',
            'direct_install',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('direct_theme_install'),
                'installed_themes' => $installed_themes_slugs,
                'active_theme_slug' => wp_get_theme()->get_stylesheet(),
                'popup_template' =>
                    '<div class="base-install-notice-outer">
                        <div class="base-install-notice-inner">
                            <div class="base-install-prompt">
                                <div class="base-install-content"><h2 class="base-install-title">{{name}}</h2><p>We recommend to Install and Activate {{name}} theme as all our demo works perfectly with this theme. You can still try our demo on any block theme but it might not look as you see on our demo.</p></div>
                                <div class="base-install-btn">
                                    <a class="install-base-theme button button-primary" data-slug="{{slug}}">Install and Activate {{name}}</a>
                                    <br>
                                    <a class="close-base-notice close-base-button">Skip</a>
                                </div>
                            </div>
                            <div class="base-install-success">
                                <div class="base-install-content"><h3>Thank you for installing {{name}}. Click on Next to proceed to demo importer.</h3></div>
                                <div class="base-install-btn">
                                    <a class="close-base-notice button button-primary">Next</a>
                                </div>
                            </div>
                            <div class="base-go-pro-blockskit-prompt">
                                <div class="go-pro-description">
                                <h2 class="blockskit-notice-title"> Upgrade to <a href="https://blockskit.com/pro/" target="_blank" class="blockskit-title">Blockskit Pro Plugin</a></h2>
                                <P>Access the full Starter Site Library and build faster than ever.</p>
                                </div>
                                <a href="https://blockskit.com/pro/" class="btn-primary" target="_blank">Buy Now</a>
                            </div>
                        </div>
                    </div>',
            )
        );
    }

    /**
     * Install base theme.
     */
    public function install_base_theme()
    {
        check_ajax_referer('direct_theme_install', 'security');

        if (!current_user_can('manage_options')) {
            $error = __('Sorry, you are not allowed to install themes on this site.', 'blockskit');
            wp_send_json_error($error);
        }

        $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
        if (empty($slug)) {
            $base_theme = bk_import_get_base_theme();
            $slug = $base_theme['slug']; // Fallback
        }

        // Check if already installed
        $theme = wp_get_theme($slug);
        if ($theme->exists()) {
            switch_theme($slug);
            wp_send_json_success();
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/theme.php';

        $api = themes_api(
            'theme_information',
            array(
                'slug' => $slug,
                'fields' => array('sections' => false),
            )
        );

        if (is_wp_error($api)) {
            $status['errorMessage'] = $api->get_error_message();
            wp_send_json_error($status['errorMessage']);
        }

        $skin = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Theme_Upgrader($skin);
        $result = $upgrader->install($api->download_link);

        if (is_wp_error($result)) {
            wp_send_json_error($result->errors);
        }

        switch_theme($slug);
        wp_send_json_success();
        die();
    }

}

/**
 * Checks if base theme installed.
 */
function bk_import_base_theme_installed()
{
    $base_theme = bk_import_get_base_theme();
    $all_themes = wp_get_themes();
    $installed_themes = array();
    foreach ($all_themes as $theme) {
        $theme_text_domain = esc_attr($theme->get('TextDomain'));
        $installed_themes[] = $theme_text_domain;
    }
    if (in_array($base_theme['slug'], $installed_themes, true)) {
        return true;
    }
    return false;

}

/**
 * Returns base theme.
 */
function bk_import_get_base_theme()
{
    $theme = bk_import_get_theme_slug();
    $base_theme = array(
        'name' => '',
        'slug' => '',
    );
    if (strpos($theme, 'blockskit') !== false) {
        $base_theme['name'] = 'Blockskit Base';
        $base_theme['slug'] = 'blockskit-base';
    }
    return $base_theme;

}

return new Bk_Base_Install_Hooks();