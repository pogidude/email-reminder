<?php
/*
Plugin Name: Email Reminder
Description: Schedules email reminders. Enter your reminder, where you'd like to email the reminder to, and when you'd like the reminder to be sent.
Version: 0.1
License: GPL
Author: Ryann Micua
Author URI: http://pogidude.com/

TODO:
1. Clean up database on uninstall *
2. Add hour option ***
3. Add option to set FROM email **
4. Run cron every hour
5. Create description **
6. Add dashboard widget to add reminder from dashboard
7. Add dashboard notifications
8. Do validation/sanitation ***
9. Add option for recurring reminders
*/

/* Constants */
define( 'PD_EREMINDER_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PD_EREMINDER_URI', trailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'PD_EREMINDER_CSS', trailingslashit( PD_EREMINDER_URI ) . 'css' );
define( 'PD_EREMINDER_JS', trailingslashit( PD_EREMINDER_URI ) . 'js' );
define( 'PD_EREMINDER_INC_DIR', trailingslashit( PD_EREMINDER_DIR ) . 'includes' );

/* Ereminder Class */
require_once( trailingslashit( PD_EREMINDER_INC_DIR ) . 'class-pogidude-ereminder.php' );

/* Admin Page */
require_once( trailingslashit( PD_EREMINDER_INC_DIR ) . 'admin.php' );

/* View Cron Events Page */
//require_once( trailingslashit( PD_EREMINDER_INC_DIR ) . 'admin-cron-events.php' );

/* activation/deactivation stuff */
register_activation_hook( __FILE__, array('Pogidude_Ereminder','on_activate' ) );
register_deactivation_hook( __FILE__, array('Pogidude_Ereminder','on_deactivate' ) );