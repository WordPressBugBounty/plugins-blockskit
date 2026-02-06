<?php
/**
 * Class to override Advanced Import Admin functionality.
 *
 * @since 1.2.2
 */
class Bk_Advanced_Import_Override
{

    /**
     * Instance of the original admin class.
     *
     * @var Advanced_Import_Admin
     */
    private $original;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // We hook late to ensure the original menus are registered.
        add_action('admin_menu', array($this, 'override_admin_menu'), 100);
    }

    /**
     * Override the admin menu pages.
     */
    public function override_admin_menu()
    {
        if (!class_exists('Advanced_Import_Admin')) {
            return;
        }

        $this->original = Advanced_Import_Admin::instance();

        global $submenu;

        // Manually remove theme submenu item to ensure it is gone
        if (isset($submenu['themes.php'])) {
            foreach ($submenu['themes.php'] as $idx => $item) {
                if ($item[2] === 'advanced-import') {
                    unset($submenu['themes.php'][$idx]);
                }
            }
        }

        // Manually remove tools submenu item
        if (isset($submenu['tools.php'])) {
            foreach ($submenu['tools.php'] as $idx => $item) {
                if ($item[2] === 'advanced-import-tool') {
                    unset($submenu['tools.php'][$idx]);
                }
            }
        }

        // Hook is 'appearance_page_advanced-import' because themes.php parent.
        remove_action('appearance_page_advanced-import', array($this->original, 'demo_import_screen'));

        // Hook is 'tools_page_advanced-import-tool' because tools.php parent.
        remove_action('tools_page_advanced-import-tool', array($this->original, 'demo_import_screen'));
        remove_action('management_page_advanced-import-tool', array($this->original, 'demo_import_screen')); // Check both just in case

        // Add our custom pages with the same slugs.
        add_theme_page(
            esc_html__('Demo Import', 'advanced-import'),
            esc_html__('Demo Import'),
            'manage_options',
            'advanced-import',
            array($this, 'custom_demo_import_screen')
        );

        add_management_page(
            esc_html__('Advanced Import', 'advanced-import'),
            esc_html__('Advanced Import', 'advanced-import'),
            'manage_options',
            'advanced-import-tool',
            array($this, 'custom_demo_import_screen')
        );
    }

    /**
     * Custom Demo Import Screen Callback.
     *
     * Replicates Advanced_Import_Admin::demo_import_screen but calls our custom init.
     */
    public function custom_demo_import_screen()
    {
        do_action('advanced_import_before_demo_import_screen');


        echo '<div class="ai-body bkit-override">'; // Added class for verification

        if (method_exists($this->original, 'get_header')) {
            $this->original->get_header();
        }

        echo '<div class="ai-content">';
        echo '<div class="ai-content-blocker hidden">';
        echo '<div class="ai-notification-title"><p>' . esc_html__('Processing... Please do not refresh this page or do not go to other url!', 'advanced-import') . '</p></div>';
        echo '<div id="ai-demo-popup"></div>';
        echo '</div>';

        // Call our custom init function
        $this->custom_init_demo_import();

        echo '</div>';
        echo '</div>';/*ai-body*/
        do_action('advanced_import_after_demo_import_screen');
    }

    /**
     * Custom Init Demo Import.
     *
     * Replicates Advanced_Import_Admin::init_demo_import but calls our custom demo_list.
     */
    public function custom_init_demo_import()
    {
        global $pagenow;
        $total_demo = 0;
        if ($pagenow != 'tools.php') {
            // We can't access protected $this->original->demo_lists easily, 
            // so we re-run the filter or fetch it.
            $demo_lists = apply_filters('advanced_import_demo_lists', array());

            // Same for is_pro_active
            $is_pro_active = false;
            $is_pro_active = apply_filters('advanced_import_is_pro_active', $is_pro_active);


            $total_demo = is_array($demo_lists) ? count($demo_lists) : 0;
            if ($total_demo >= 1) {
                // Call OUR custom demo_list
                $this->custom_demo_list($demo_lists, $total_demo, $is_pro_active);
            }
        }

        // Call original form
        if (method_exists($this->original, 'demo_import_form')) {
            $this->original->demo_import_form($total_demo);
        }
    }

    /**
     * Custom Demo List.
     *
     * This is the modified function.
     *
     * @param array $demo_lists List of demos.
     * @param int   $total_demo Total count.
     * @param bool  $is_pro_active Whether pro is active.
     */
    public function custom_demo_list($demo_lists, $total_demo, $is_pro_active = false)
    {
        ?>
        <div class="ai-filter-header">
            <div class="ai-filter-tabs">
                <ul class="ai-types ai-filter-group" data-filter-group="secondary">
                    <li class="ai-filter-btn-active ai-filter-btn ai-type-filter" data-filter="*">
                        <?php esc_html_e('All', 'advanced-import'); ?>
                        <span class="ai-count"></span>
                    </li>
                    <?php
                    $types = array_column($demo_lists, 'type');
                    $unique_types = array_unique($types);
                    foreach ($unique_types as $cat_index => $single_type) {
                        ?>
                        <li class="ai-filter-btn ai-type-filter" data-filter=".<?php echo strtolower(esc_attr($single_type)); ?>">
                            <?php echo ucfirst(esc_html($single_type)); ?>
                            <span class="ai-count"></span>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <div class="ai-search-control">
                    <input id="ai-filter-search-input" class="ai-search-filter" type="text"
                        placeholder="<?php esc_attr_e('Search...', 'advanced-import'); ?>">
                </div>
                <ul class="ai-form-type">
                    <li class="ai-form-file-import">
                        <?php esc_html_e('Upload zip', 'advanced-import'); ?>
                    </li>
                </ul>
            </div>
        </div>
        <div class="ai-filter-content" id="ai-filter-content">


            <div class="ai-actions ai-sidebar">
                <div class="ai-import-available-categories">
                    <h3><?php esc_html_e('Categories', 'advanced-import'); ?></h3>
                    <div class="ai-import-fp-wrap">
                        <ul class="ai-import-fp-lists ai-filter-group" data-filter-group="pricing">
                            <li class="ai-fp-filter ai-filter-btn ai-filter-btn-active" data-filter="*">All</li>
                            <li class="ai-fp-filter ai-filter-btn" data-filter=".ai-fp-filter-free">Free</li>
                            <li class="ai-fp-filter ai-filter-btn" data-filter=".ai-fp-filter-pro">Pro</li>
                        </ul>
                    </div>
                    <ul class="ai-import-available-categories-lists ai-filter-group" data-filter-group="primary">
                        <li class="ai-filter-btn-active ai-filter-btn" data-filter="*">
                            <?php esc_html_e('All Categories', 'advanced-import'); ?>
                            <span class="ai-count"></span>
                        </li>
                        <?php
                        $categories = array_column($demo_lists, 'categories');
                        $unique_categories = array();
                        if (is_array($categories) && !empty($categories)) {
                            foreach ($categories as $demo_index => $demo_cats) {
                                foreach ($demo_cats as $cat_index => $single_cat) {
                                    if (in_array($single_cat, $unique_categories)) {
                                        continue;
                                    }
                                    $unique_categories[] = $single_cat;
                                    ?>
                                    <li class="ai-filter-btn" data-filter=".<?php echo strtolower(esc_attr($single_cat)); ?>">
                                        <?php echo ucfirst(esc_html($single_cat)); ?>
                                        <span class="ai-count"></span>
                                    </li>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="ai-filter-content-wrapper nweone">
                <?php
                foreach ($demo_lists as $key => $demo_list) {
                    /*Check for required fields*/
                    if (!isset($demo_list['title']) || !isset($demo_list['screenshot_url']) || !isset($demo_list['demo_url'])) {
                        continue;
                    }

                    $template_url = isset($demo_list['template_url']) ? $demo_list['template_url'] : '';
                    if (is_array($template_url)) {
                        $data_template = 'data-template_url="' . esc_attr(wp_json_encode($template_url)) . '"';
                        $data_template_type = 'data-template_type="array"';
                    } elseif ($template_url) {
                        $data_template = 'data-template_url="' . esc_attr($template_url) . '"';
                        if (is_file($template_url) && filesize($template_url) > 0) {
                            $data_template_type = 'data-template_type="file"';
                        } else {
                            $data_template_type = 'data-template_type="url"';
                        }
                    } else {
                        $data_template = 'data-template_url="' . esc_attr(wp_json_encode($template_url)) . '"';
                        $data_template_type = 'data-template_type="array"';
                    }
                    ?>
                    <div data-slug="<?php echo esc_attr($key); ?>" aria-label="<?php echo esc_attr($demo_list['title']); ?>" class="ai-item
                    <?php
                    echo isset($demo_list['categories']) ? esc_attr(implode(' ', $demo_list['categories'])) : '';
                    echo isset($demo_list['type']) ? ' ' . esc_attr($demo_list['type']) : '';
                    // We don't have direct access to is_pro method, so we replicate check
                    $is_item_pro = isset($demo_list['is_pro']) && $demo_list['is_pro'];
                    echo $is_item_pro ? ' ai-fp-filter-pro' : ' ai-fp-filter-free';

                    // Replicate is_template_available check
                    $is_available = false;
                    if ($is_pro_active) {
                        $is_available = true;
                    } elseif (!isset($demo_list['is_pro'])) {
                        $is_available = true;
                    } elseif (isset($demo_list['is_pro']) && !$demo_list['is_pro']) {
                        $is_available = true;
                    }
        
                    echo $is_available ? '' : ' ai-pro-item'
                        ?>
                    " <?php echo $is_available ? $data_template . ' ' . $data_template_type : ''; ?>>
                        <?php
                        wp_nonce_field('advanced-import');
                        ?>
                        <div class="ai-item-preview">
                            <div class="ai-item-screenshot">
                                <img src="<?php echo esc_url($demo_list['screenshot_url']); ?>">

                            </div>
                            <h4 class="ai-author-info">
                                <?php esc_html_e('Author: ', 'advanced-import'); ?>
                                <?php echo esc_html(isset($demo_list['author']) ? $demo_list['author'] : wp_get_theme()->get('Author')); ?>
                            </h4>
                            <div class="ai-details"><?php esc_html_e('Details', 'advanced-import'); ?></div>
                            <?php
                            if ($is_item_pro) {
                                ?>
                                <span class="ai-premium-label"><?php esc_html_e('Premium', 'advanced-import'); ?></span>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="ai-item-footer">
                            <div class="ai-item-footer_meta">
                                <h3 class="theme-name"><?php echo esc_html($demo_list['title']); ?></h3>
                                <div class="ai-item-footer-actions">
                                    <a class="button ai-item-demo-link" href="<?php echo esc_url($demo_list['demo_url']); ?>"
                                        target="_blank">
                                        <span
                                            class="dashicons dashicons-visibility"></span><?php esc_html_e('Preview', 'advanced-import'); ?>
                                    </a>
                                    <?php
                                    echo $this->local_template_button($demo_list, $is_available);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Local implementation of template_button since we can't easily use the original's
     * due to protected property dependencies.
     *
     * @param array $item Item data.
     * @param bool  $is_available Whether it is available.
     */
    private function local_template_button($item, $is_available)
    {
        ob_start();

        if ($is_available) {
            $plugins = isset($item['plugins']) && is_array($item['plugins']) ? ' data-plugins="' . esc_attr(wp_json_encode($item['plugins'])) . '"' : '';
            ?>
            <a class="button ai-demo-import ai-item-import is-button is-default is-primary is-large button-primary" href="#"
                aria-label="<?php esc_attr_e('Import', 'advanced-import'); ?>" <?php echo $plugins; ?>>
                <span class="dashicons dashicons-download"></span><?php esc_html_e('Import', 'advanced-import'); ?>
            </a>
            <?php
        } else {
            ?>
            <a class="button is-button is-default is-primary is-large button-primary"
                href="<?php echo esc_url(isset($item['pro_url']) ? $item['pro_url'] : '#'); ?>" target="_blank"
                aria-label="<?php esc_attr_e('View Pro', 'advanced-import'); ?>">
                <span class="dashicons dashicons-awards"></span><?php esc_html_e('View Pro', 'advanced-import'); ?>
            </a>
            <?php
        }

        $render_button = ob_get_clean();
        // We skip the filter for now or apply it with unique hook if needed, 
        // but keeping it simple is safer.
        return $render_button;
    }
}
