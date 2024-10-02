<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

define( 'BK_TEMPLATE_URL', plugin_dir_url( __FILE__ ) );
define( 'BK_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Returns the currently active theme's name.
 *
 */
function bk_import_get_theme_slug(){
    $demo_theme = wp_get_theme();
   	return $demo_theme->get( 'TextDomain' );
}

/**
 * Returns the currently active theme's screenshot.
 *
 */
function bk_import_get_theme_screenshot(){
	$demo_theme = wp_get_theme();
    return $demo_theme->get_screenshot();
}

/**
 * The core plugin class that is used to define internationalization,admin-specific hooks, 
 * and public-facing site hooks..
 *
 */   
require BK_PATH . 'demo/functions.php';

if( get_stylesheet() !== 'blockskit-education' && get_stylesheet() !== 'blockskit-agency' && get_stylesheet() !== 'blockskit-base' ){
    require BK_PATH . 'base-install/base-install.php';  
}

/**
 * Register all of the hooks related to the admin area functionality
 * of the plugin.
 *
 */
$plugin_admin = bk_import_hooks();
add_filter( 'advanced_import_demo_lists', array( $plugin_admin,'bk_import_demo_import_lists'), 20, 1 );
add_filter( 'admin_menu', array( $plugin_admin, 'import_menu' ), 10, 1 );
add_filter( 'wp_ajax_bk_import_getting_started', array( $plugin_admin, 'install_advanced_import' ), 10, 1 );
add_filter( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ), 10, 1 );
add_filter( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ), 10, 1 );
add_filter( 'advanced_export_include_options', array( $plugin_admin, 'bk_import_include_options' ), 10, 1 );
add_action( 'advanced_import_replace_post_ids', array( $plugin_admin, 'bk_import_replace_attachment_ids' ), 30 );