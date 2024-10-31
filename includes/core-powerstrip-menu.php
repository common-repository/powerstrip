<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

ob_start();
class PowerstripHideMenus {
   
    public function __construct() {
        add_action('admin_init',  array($this, 'process_powerstrip_forms') );
        add_action('admin_init', array($this, 'powerstrip_register_settings'));
        add_action('admin_menu', array($this, 'organize_ps_menus'), 99999);
    }

    private $menu_exceptions = array(
        'woocommerce' => 'admin.php?page=wc-admin',
        'woocommerce-marketing' => 'admin.php?page=wc-admin&path=%2Fmarketing'
    );

    public function powerstrip_register_settings() {
        // Register array to store menu options with a sanitization callback
        register_setting('powerstrip_settings', 'powerstrip_menus', array(
            'sanitize_callback' => array($this, 'sanitize_powerstrip_menus')
        ));
    
        // Register setting for showing only original menus with a sanitization callback
        register_setting('powerstrip_general_settings', 'powerstrip_show_original', array(
            'sanitize_callback' => 'intval' // This will sanitize it as an integer (0 or 1)
        ));
    }

    // Define a sanitization callback function for powerstrip_menus
    public function sanitize_powerstrip_menus($input) {
        if (is_array($input)) {
            // Sanitize each item in the array
            return array_map('sanitize_text_field', wp_unslash($input));
        }
        return array();
    }


    // Process Forms
    public function process_powerstrip_forms() {
    
        if (isset($_POST['powerstrip_general_nonce']) && check_admin_referer('powerstrip_general_options_update', 'powerstrip_general_nonce')) {
            // Process the general settings form
            $show_original = isset($_POST['powerstrip_show_original']) ? 1 : 0;
            update_option('powerstrip_show_original', $show_original);
        }
        if (isset($_POST['powerstrip_menus_nonce']) && check_admin_referer('powerstrip_menus_update', 'powerstrip_menus_nonce')) {
            $selected_menus = isset($_POST['powerstrip_menus']) ? array_map('sanitize_text_field', wp_unslash($_POST['powerstrip_menus'])) : array();
            update_option('powerstrip_menus', $selected_menus);
        }
    }
    
    

    public function organize_ps_menus() {
        global $menu;
        global $submenu;

        $powerstrip_show_original = get_option('powerstrip_show_original');
        $powerstrip_menus = get_option('powerstrip_menus');

        // Ensure $powerstrip_menus is always an array
        if (!is_array($powerstrip_menus)) {
            $powerstrip_menus = array();
        }

        // List of stock WordPress admin menu slugs
        $stock_menus = [
            'index.php', // Dashboard
            'edit.php', // Posts
            'upload.php', // Media
            'edit.php?post_type=page', // Pages
            'edit-comments.php', // Comments
            'themes.php', // Appearance
            'plugins.php', // Plugins
            'users.php', // Users
            'tools.php', // Tools
            'options-general.php', // Settings
            'powerstrip-menu' // PowerStrip Menu
        ];

        // Create PowerStrip Menu Items
        $powerstrip_icon = 'PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKCQkJdmlld0JveD0iMCAwIDM2IDM0IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAzNiAzNDsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgoJIDxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+CgkJIC5zdDB7ZmlsbDojRUVFRUVFO30KCSA8L3N0eWxlPgoJIDxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0yOCwzLjdDMjYuNSwyLjQsMjUsMiwyNC45LDJsLTAuMSwwaC0wLjFIMTAuMUg5VjN2MjYuNHYyLjdsMS45LTEuOWMwLDAsMCwwLDAsMGwxMC42LTEwLjYKCQkgYzAuMi0wLjMsMC40LTAuNiwwLjMtMWMtMC4xLTAuMy0wLjMtMC42LTAuNi0wLjhsLTMuOS0ybDQuMi00LjJjMC40LTAuNCwwLjQtMS4xLDAtMS42Yy0wLjQtMC40LTEuMS0wLjQtMS42LDBsLTUuMyw1LjMKCQkgYy0wLjIsMC4zLTAuNCwwLjYtMC4zLDFjMC4xLDAuMywwLjMsMC42LDAuNiwwLjhsMy45LDJsLTQuMiw0LjJjMCwwLDAsMCwwLDBsLTMuNCwzLjRWNC4xaDEzLjNjMC43LDAuMiw0LjMsMS42LDQuMyw2LjgKCQkgYzAsMi44LTAuNyw0LjktMi4xLDZjLTEsMC44LTIuMSwwLjgtMi4xLDAuOGMtMC42LDAtMS4xLDAuNS0xLjEsMS4xUzI0LDIwLDI0LjYsMjBjMC4yLDAsMS43LDAsMy4zLTEuMmMyLjUtMS45LDMuMS01LjMsMy4xLTcuOAoJCSBDMzEsNy4xLDI5LjQsNC45LDI4LDMuN3oiLz4KCSA8L3N2Zz4KCSA=';

        add_menu_page(
            'PowerStrip Settings', 
            'PowerStrip', 
            'delete_posts', 
            'powerstrip-menu', 
            array($this, 'powerstrip_settings_page'), 
            'data:image/svg+xml;base64,' . $powerstrip_icon,
            99999
        );      

        // Iterate over the global $menu array (holds all admin menus)
        foreach ($menu as $index => $item) {
            $slug = $item[2];
            $original_slug = null; // Initialize $original_slug otherwise next submenus inherit between iterations


            if (!$powerstrip_show_original) {
               $slug_check = array_key_exists($slug, $powerstrip_menus);
            } else {
                $slug_check = !in_array($slug, $stock_menus);
            }
            
            // If the menu slug is not in the stock_menus array, move it under the powerstrip menu
            if (!str_contains($item[4], 'wp-menu-separator') && $slug_check) {

                //Build icon image
                $icon_html = $this->get_menu_icon_html($item);

                // Add span to title if has submenu
                if (isset($submenu[$slug]) && current_user_can($item[1]) ) {
                    $top_menu_title = "<span class=\"has-ps-sub\" data-id=\"$item[5]\" data-class=\"$item[4]\"></span>$icon_html<div class='wp-menu-name'>$item[0]</div>"; 
                } else {
                    $top_menu_title = "<span data-id=\"$item[5]\" data-class=\"$item[4]\"></span> <div class='wp-menu-name'>$item[0]</div>";
                }

                // Check for exceptions and adjust the slug if necessary
                if (isset($this->menu_exceptions[$slug])) {
                    $original_slug = $slug;
                    $slug = $this->menu_exceptions[$slug];
                }

                add_submenu_page(
                    'powerstrip-menu', // Parent Slug
                    $item[0], // Page Title
                    $top_menu_title, // Menu Title
                    $item[1], // Capability
                    $slug // Menu Slug
                );

                // Use original slug to check for and add submenus
                $slug_to_check = $original_slug ?? $slug;

                // Add Submenus if they exist && user has permissions to access from parent
                if (isset($submenu[$slug_to_check]) && current_user_can($item[1])) {
                    foreach ($submenu[$slug_to_check] as $sub_index => $sub_item) {


                        // Make sure the submenu link is correctly formatted
                        $menu_slug = $sub_item[2];
                        if (!preg_match('/.php/', $menu_slug)) {
                            $menu_slug = 'admin.php?page=' . $menu_slug;
                        }
                        
                        $sub_new_title = "<span class='ps-sub-indent' data-parent-slug='$slug'>   → </span>$sub_item[0]"; 

                        add_submenu_page(
                            'powerstrip-menu', 
                            $sub_item[0], 
                            $sub_new_title, 
                            $sub_item[1], 
                            $menu_slug
                        );  
                    }
                }
                unset($menu[$index]);
            }
        }

        // add classes to top PS submenu items based on spans added to titles
        if (isset($submenu['powerstrip-menu']) && is_array($submenu['powerstrip-menu'])) {
            foreach ($submenu['powerstrip-menu'] as $index => $item) {
                $sub_class = '';
                $sub_id = '';

                if ( str_contains($item[0], 'ps-sub-indent') ) {
                    $sub_class .= "ps-submenu ";
                } else if ( str_contains($item[0], 'has-ps-sub') ) {
                    $sub_class .= "ps-topmenu has-ps-submenu ";
                } else {
                    $sub_class .= "ps-topmenu ";
                }

                $pattern = '/<span class="has-ps-sub" data-id="([^"]+)" data-class="([^"]+)"/';

                if (preg_match($pattern, $item[0], $matches)) {
                    $sub_id .= $matches[1]; 
                    $sub_class .= $matches[2]; 
                }

                $submenu['powerstrip-menu'][$index][4] = $sub_class;
                $submenu['powerstrip-menu'][$index][5] = $sub_id;
            }
        }
    }

    // Top level Icons
    public function get_menu_icon_html($item) {
        $img_build = '';
        if ( ! empty( $item[6] ) ) {
            $img = '<img src="' . $item[6] . '" alt="" />';
            $img_style = $img_class = '';
            if ( 'none' === $item[6] || 'div' === $item[6] ) {
                $img = '<br />';
            } elseif ( 0 === strpos( $item[6], 'data:image/svg+xml;base64,' ) ) {
                $img       = '<br />';
                $img_style = ' style="background-image:url(\'' . esc_attr( $item[6] ) . '\')"';
                $img_class = ' svg';
            } elseif ( 0 === strpos( $item[6], 'dashicons-' ) ) {
                $img       = '<br />';
                $img_class = ' dashicons-before ' . sanitize_html_class( $item[6] );
            }
            $img_build = "<div class='wp-menu-image$img_class'$img_style aria-hidden='true'>$img</div>";
        }
        return $img_build;
    }

    // Build Settings Page
    public function powerstrip_settings_page() {
        // Retrieve the original menu structure
        $og_menu = get_transient('powerstrip_original_wpmenu');
        $options = get_option('powerstrip_menus', array());
        $original_option = get_option('powerstrip_show_original');

        $allowed_tags = array(
            'span' => array(
                'class' => array(),
            ),
            'i' => array(),
            'b' => array(),
            'strong' => array(),
            'em' => array(),
        );
        

        ?>
        <div id="powerstrip-wrap" class="wrap">
            <div class="ps-container">
                <img src="<?php echo esc_url(POWERSTRIP_URL) ?>/assets/powerstrip.svg" width="52" height="52" style="display:inline-block;float:left; margin-top:11px;margin-right:12px" />
                <h1>PowerStrip</h1>
                <p style="margin-top:0"><strong>Take charge of your WordPress dashboard</strong></p>
                <br />
                <div class="ps-card">
                    <div class="ps-grid">
                        <div class="ps-cell medium-six">
                            <form method="post" action="options.php">
                                <?php 
                                    settings_fields('powerstrip_general_settings'); 
                                    do_settings_sections('powerstrip_general_settings'); 
                                    wp_nonce_field('powerstrip_general_options_update', 'powerstrip_general_nonce');
                                ?>
                                <table class="form-table">
                                    <tr valign="top">
                                    <th scope="row">
                                        <label for="powerstrip_show_original">Show Only Core Menus</label>
                                        <small style="font-weight:normal">Tuck any non-WordPress menu under PowerStrip. Toggle off to activate customization.</small>
                                    </th>
                                    <td style="vertical-align:top;padding-top:20px;"><input type="checkbox" name="powerstrip_show_original" id="powerstrip_show_original" value="1" <?php checked(1, $original_option, true); ?> /></td>
                                    </tr>
                                </table>
                                
                                <?php submit_button(); ?>
                            </form>
                        </div>

                        <div class="ps-cell medium-six">
                            <form method="post" action="options.php" <?php if ($original_option) echo 'class="disabled"'; ?>>
                                <p style="padding-top:14px">Select any top menu you'd like to include under the PowerStrip menu.</p>
                                <?php 
                                    settings_fields('powerstrip_settings');
                                    do_settings_sections('powerstrip_settings'); 
                                    wp_nonce_field('powerstrip_menus_update', 'powerstrip_menus_nonce');
                                ?>
                                <table class="form-table powerstrip-options-table">
                                    <?php foreach ($og_menu as $item) :
                                        if (!isset($item[2]) || str_contains($item[4], 'wp-menu-separator') || !current_user_can($item[1])) continue; // Skip if no valid menu slug

                                        $menu_slug = esc_attr($item[2]);
                                        $checked = isset($options[$menu_slug]) && $options[$menu_slug] ? 'checked' : '';
                                    ?>
                                        <tr valign="top">
                                            <th scope="row">
                                                <label for="powerstrip_menus[<?php echo esc_attr($menu_slug); ?>]">
                                                    <?php
                                                    // Rebuild image icons outside of function for WP sanitize output requirements
                                                    if ( ! empty( $item[6] ) ) { ?>
                                                        <div class='wp-menu-image
                                                            <?php if ( 'none' === $item[6] || 'div' === $item[6] ) { ?>
                                                                ' aria-hidden='true'>
                                                                <br />
                                                            <?php } elseif ( 0 === strpos( $item[6], 'data:image/svg+xml;base64,' ) ) { ?>
                                                                  svg' 
                                                                 style="background-image:url('<?php echo esc_attr( $item[6] ) ?> ')"
                                                                 aria-hidden='true' >
                                                                <br />
                                                            <?php } elseif ( 0 === strpos( $item[6], 'dashicons-' ) ) { ?>
                                                                 dashicons-before <?php echo sanitize_html_class( $item[6] ) ?>'
                                                                aria-hidden='true'>
                                                                <br />
                                                            <?php } else { ?>
                                                                <img src="<?php echo esc_attr($item[6]) ?>" alt="" />
                                                            <?php } ?> 
                                                        </div>
                                                        <?php
                                                    }
                                                    echo wp_kses($item[0], $allowed_tags);
                                                    ?>
                                                </label>
                                            </th>
                                            <td>
                                                <input type="checkbox" name="powerstrip_menus[<?php echo esc_attr($menu_slug); ?>]"
                                                    id="powerstrip_menus[<?php echo esc_attr($menu_slug); ?>]"
                                                    value="1" <?php echo esc_attr($checked); ?> 
                                                    <?php if ($original_option) echo 'disabled="true"'; ?>
                                                    />
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                                <p>
                                <?php
                                $button_attributes = array();
                                if ($original_option) {
                                    $button_attributes = array('disabled' => 'true');
                                }
                                submit_button(__('Update Menu Settings', 'powerstrip'), 'primary', 'submit', false, $button_attributes); 
                                ?>
                                </p>
                            </form>
                        </div>
                    </div><!--end grid -->
                </div><!--end card -->
            </div><!-- end ps-container -->
        </div>
        <?php
    }  

}

// Instantiate Class
new PowerstripHideMenus();
