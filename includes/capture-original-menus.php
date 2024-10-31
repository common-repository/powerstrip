<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function powerstrip_capture_menus() {
    global $menu, $submenu;
    // Capture the original states early in the load process
    set_transient('powerstrip_original_wpmenu', $menu, DAY_IN_SECONDS); // Store for longer if needed
    set_transient('powerstrip_original_wpsubmenu', $submenu, DAY_IN_SECONDS);
    
}
add_action('admin_menu', 'powerstrip_capture_menus', 9999); 