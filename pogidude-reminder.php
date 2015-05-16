<?php
/*
Plugin Name: Email Reminder
Plugin URI: http://pogidude.com/email-reminder/
Description: Schedules email reminders. Enter your reminder, where you'd like to email the reminder to, and when you'd like the reminder to be sent.
Version: 1.2
License: GPLv2
Author: Ryann Micua
Author URI: http://pogidude.com/about/

TODO:
1. Clean up database on uninstall *
2. Add hour option ***
3. Add option to set FROM email **
5. Create description **
6. Add dashboard widget to add reminder from dashboard
7. Add dashboard notifications
9. Add option for recurring reminders
*/

/* Constants */
define( 'PDER_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PDER_URI', trailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'PDER_ASSETS', PDER_URI . 'assets' );
define( 'PDER_CSS', PDER_ASSETS . '/css' );
define( 'PDER_JS', PDER_ASSETS . '/js' );
define( 'PDER_INC_DIR', trailingslashit( PDER_DIR ) . 'includes' );
define( 'PDER_CLASSES', trailingslashit( PDER_INC_DIR ). 'classes' );
define( 'PDER_VIEWS', PDER_DIR . 'views' );
define( 'PDER_POSTTYPE', 'ereminder' );
define( 'PDER_DOMAIN', 'pd-ereminder' );

/* Load Base class */
require_once( trailingslashit( PDER_CLASSES ) . 'PDER_Base.php' );

/* View Cron Events Page */
//require_once( trailingslashit( PDER_INC_DIR ) . 'admin-cron-events.php' );

/* Load language internationalizing */
load_plugin_textdomain('email-reminder', false, basename( dirname( __FILE__ ) ) . '/languages' );

/* activation/deactivation stuff */
register_activation_hook( __FILE__, array('PDER_Base','on_activate' ) );
register_deactivation_hook( __FILE__, array('PDER_Base','on_deactivate' ) );
