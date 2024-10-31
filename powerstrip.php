<?php
/*
* Plugin Name: PowerStrip
* Plugin URI: https://powerstrip.io/
* Description: Take charge of your dashboard. Display any admin menu from one location.
* Requires at least: 6.3
* Requires PHP: 7.4
* Version: 1.0
* Author: Agile League
* Author URI: https://agileleague.com
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: powerstrip
*/


// No Direct Access
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !function_exists( 'add_action' ) ) {
	echo "Hey, we're cleaning up over here!";
	exit;
}

define( 'POWERSTRIP_DIR', plugin_dir_path( __FILE__ ) );
define( 'POWERSTRIP_URL', plugin_dir_url( __FILE__ ) );
define( 'POWERSTRIP_TEXTDOMAIN', 'powerstrip' );

// Only show core menus on activation
function powerstrip_activate() {
	update_option('powerstrip_show_original', true);
}
register_activation_hook(__FILE__, 'powerstrip_activate');

// Core Classes
require_once( POWERSTRIP_DIR . '/includes/capture-original-menus.php' );
require_once( POWERSTRIP_DIR . '/includes/core-powerstrip-menu.php' );
require_once( POWERSTRIP_DIR . '/includes/powerstrip-scripts.php' );