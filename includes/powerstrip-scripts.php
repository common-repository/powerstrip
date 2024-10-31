<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class PowerstripAddAssets {

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_ps_styles'));
        wp_enqueue_style( 'powerstrip_css', POWERSTRIP_URL . 'assets/powerstrip.css', array(), '1.0' );
        wp_enqueue_script( 'powerstrip_js', POWERSTRIP_URL . 'assets/powerstrip-organize-menus.js?v=1.001', array( 'jquery' ), '1', true );
    }

    public function enqueue_ps_styles() {
        // Inline core menu styles
        $css = '
        
            /* Stop PS first levell Submenu from having odd spacing */
            #toplevel_page_powerstrip-menu .wp-submenu {
                border-left:none!important;
            }
            #toplevel_page_powerstrip-menu a.ps-topmenu:not(.wp-first-item) {
                padding-left:0!important;
                padding-right:0!important;
            }
            #adminmenu #toplevel_page_powerstrip-menu.wp-has-current-submenu ul > li.ps-topmenu > a {
                padding-top:0;
                padding-bottom:0;
            }

            #toplevel_page_powerstrip-menu.wp-menu-open .ps-submenu-container {
                position: absolute;
                top:0;
                left: 160px;
                z-index: 9999;
                background-color: #2c3338;
                box-shadow: 0 3px 5px rgba(0,0,0,.2);
            }

            /* Hide sub indent */
            #toplevel_page_powerstrip-menu .ps-sub-indent {
                display:none;
            }
            #adminmenu #toplevel_page_powerstrip-menu.wp-has-current-submenu .wp-submenu .wp-submenu-head {
                background-color:transparent!important;
            }

            /* Ensures that sub-submenus are not displayed by default */
            .ps-submenu-container {
                display: none;
                position: absolute;
                left: 100%;
                top: 0; 
                z-index: 999999; 
            }
            #adminmenu .ps-topmenu.wp-has-current-submenu .wp-submenu {
                position:absolute;
            }

            /* Styling for the list items within the submenu container */
            .ps-submenu-container li {
                display: block;
            }

            /* Display the submenu when the parent item is hovered */
            .has-ps-submenu:hover .ps-submenu-container {
                display: block;
            }

            /* Style adjustments for the top-level menu items with submenus */
            .ps-topmenu.has-ps-submenu {
                position: relative; 
                cursor: pointer;
            }

            @media screen and (min-width: 782px) {
                /* IF menu is folded */
                .folded #toplevel_page_powerstrip-menu .wp-submenu:not(.ps-submenu-container) {
                    width:36px;
                    min-width:36px;
                }
                .folded #toplevel_page_powerstrip-menu .wp-submenu:not(.ps-submenu-container) > .wp-submenu-head, .folded #toplevel_page_powerstrip-menu .wp-submenu:not(.ps-submenu-container) > .wp-first-item {
                    display:none
                }
                .folded #adminmenu #toplevel_page_powerstrip-menu .wp-submenu:not(.ps-submenu-container) > li > a {
                    padding:0;
                } 
                .ps-submenu-container {
                    margin-top:0!important;
                }
            }

        ';
        
        $minified_css = preg_replace('/\s+/', ' ', trim($css));
        wp_add_inline_style('admin-menu', esc_html($minified_css));
    }
}

new PowerstripAddAssets();