<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Plugin Name: Blockskit
 * Description: An easy plugin to import starter sites and add different effects to the image.
 * Author: blockskitdev
 * Author URI: https://blockskit.com/
 * Version: 1.0.7
 * Text Domain: blockskit
 * Domain Path:
 * Tested up to: 6.7
 *
 * Blockskit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with Blockskit. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Blockskit
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
 */
function create_block_bk_block_init() {
    register_block_type_from_metadata( __DIR__ );
}
add_action( 'init', 'create_block_bk_block_init' );

/*
 * Demo Import
 */
require plugin_dir_path( __FILE__ ) . 'import/demo-import.php';