<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Blockskit_Admin_Notice')) {
    class Blockskit_Admin_Notice {
        private $current_date;

        public function __construct() {
            $this->current_date = strtotime( 'now' );
            add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
            add_action( 'admin_init', [$this, 'check_pro_install'] );
            add_action( 'wp_ajax_remind_me_later_blockskit_pro', [$this, 'remind_me_later_blockskit_pro'] );
            add_action( 'wp_ajax_upgrade_blockskit_pro_notice_dismiss', [$this, 'upgrade_dismiss'] );
        }

        public function admin_scripts() {
            wp_enqueue_style( 'blockskit-admin-notice-style', BLOCKSKIT_PLUGIN_URL . 'assets/blockskit-admin-notice.css' );
            wp_enqueue_script( 'blockskit-admin-notice', BLOCKSKIT_PLUGIN_URL . 'assets/blockskit-admin-notice.js', array( 'jquery' ), '1.0.0', true );
            wp_localize_script( 'blockskit-admin-notice', 'BLOCKSKIT_PRO_UPGRADE', 
                array( 
                    'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                    'nonce'     => wp_create_nonce( 'blockskit_pro_upgrade_nonce' ),
                    'dismiss_nonce'     => wp_create_nonce( 'blockskit_pro_upgrade_dismiss_nonce' ),
                ) 
            );
        }

        public function check_pro_install() { 

            if ( $this->current_date >= (int)get_option('remind_me_later_blockskit_pro_time') ) {
                if ( !get_option('upgrade_blockskit_pro_notice_dismiss_' . BLOCKSKIT_PLUGIN_VERSION) ) {
                    add_action( 'admin_notices', [$this, 'admin_notice_blockskit_pro' ]);
                }
            }
        }

        public function remind_me_later_blockskit_pro() {
            $nonce = $_POST['nonce'];

            if ( !wp_verify_nonce( $nonce, 'blockskit_pro_upgrade_nonce')  || !current_user_can( 'manage_options' ) ) {
              exit; // Get out of here, the nonce is rotten!
            }

            update_option( 'remind_me_later_blockskit_pro_time', strtotime('7 days') );
        }

        public function upgrade_dismiss() {
            $nonce = $_POST['nonce'];

            if ( !wp_verify_nonce( $nonce, 'blockskit_pro_upgrade_dismiss_nonce')  || !current_user_can( 'manage_options' ) ) {
              exit; // Get out of here, the nonce is rotten!
            }

            add_option( 'upgrade_blockskit_pro_notice_dismiss_' . BLOCKSKIT_PLUGIN_VERSION, true );
        }

        /**
         * To Check Plugin is installed or not
         * @since Blockskit 1.2.0
         */
        function _is_plugin_installed($plugin_path ) {
            $installed_plugins = get_plugins();
            return isset( $installed_plugins[ $plugin_path ] );
        }

        public function admin_notice_blockskit_pro() {
            $pro_img_url = BLOCKSKIT_PLUGIN_URL . 'assets/img/pro-notice.png';
            $plugin = 'blockskit-pro/blockskit-pro.php';
            if ( !$this->_is_plugin_installed( $plugin ) ) {
                echo '<div class="blockskit-go-pro-notice notice is-dismissible">';
                    echo '<div class="getting-img">';
                        echo '<img id="" src="'.esc_url( $pro_img_url ).'" />';
                    echo '</div>';
                    echo '<div class="getting-content ">';
                        echo '<h2 class="blockskit-notice-title"> Upgrade to <a href="https://blockskit.com/pro/#pricing-table" target="_blank" class="blockskit-title">Blockskit Pro</a> Plugin for Full Starter Sites Library</h2>';
                        echo '<ul class="blockskit-demo-info-list">';
                        echo '<li><div><strong>Launch with Premium Starter Sites</strong> – Access all pre-built premium libraries, that come ready to use for different kinds of websites.</div></li>';
                        echo '<li> <div><strong>One-Click Demo Installer</strong> – Instantly import pre-built demo to get started in minutes.</div></li>';                         
                        echo '</ul>';
                        echo '<div class="button-wrapper">';
                        echo '<a href="https://blockskit.com/pro/#pricing-table" class="btn-primary" target="_blank">Buy Now</a>';
                        echo '<a href="https://blockskit.com/pro/" class="btn-primary btn-theme-detail" target="_blank">Plugin Details</a>';
                        echo'<button class=" btn-primary blockskit-pro-remind-me-later">Remind Me Later</button>';
                        echo '</div>';
                    echo '</div>';
                    echo '<a href="javascript:void(0)" id="blockskit-pro-dismiss" class="admin-notice-dismiss">
                            <span class="blockskit-pro-top-dissmiss-btn">Dismiss</span>
                        </a>';
                echo '</div>';
            }
        }
    }
}
return new Blockskit_Admin_Notice();