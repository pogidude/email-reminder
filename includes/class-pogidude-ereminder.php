<?php

//register Ereminder Custom Post Type
add_action('init', array( 'Pogidude_Ereminder','register_ereminder_post_type') );

//specify our own cron interval
add_filter('cron_schedules', array( 'Pogidude_Ereminder', 'add_cron_intervals' ) );

//register our event
add_action('pogidude_send_reminders', array('Pogidude_Ereminder', 'send_ereminders') );

Class Pogidude_Ereminder {
	
	public function __construct(){
		$this->init();
	}
	
	public function init(){

	}
	
	/**
	 * Stuff that needs to be done once. This function gets fired on plugin activation
	 */
	static function on_activate(){
		//verify event has not been scheduled
		if( !wp_next_scheduled( 'pogidude_send_reminders' ) ){
			//schedule our custom event
			wp_schedule_event( time(), 'pogidude_5minutes', 'pogidude_send_reminders' );
		}
	}
	
	/**
	 * Stuff that needs to be done on deactivation.
	 */
	static function on_deactivate(){
		//clear scheduled events
		while( wp_next_scheduled( 'pogidude_send_reminders' ) ){
			$timestamp = wp_next_scheduled( 'pogidude_send_reminders' );
			wp_unschedule_event( $timestamp,'pogidude_send_reminders' );
		}
	}
	
	/**
	 * Add our own Cron Intervals
	 */
	public function add_cron_intervals( $schedules ){
		//create a 'minute' recurrent schedule option
		$schedules['pogidude_minute'] = array(
			'interval' => 60,
			'display' => 'Every Minute'
		);
		
		$schedules['pogidude_twicehourly'] = array(
			'interval' => 60*30,
			'display' => 'Twice Hourly (30 min)'
		);
		
		$schedules['pogidude_5minutes'] = array(
			'interval' => 60*5,
			'display' => 'Every 5 minutes'
		);
		return $schedules;
	}
	
	/**
	 * Send Ereminders
	 */
	public function send_ereminders(){
	
		//credits
		$credits = 'This reminder was sent using <a href="http://pogidude.com/email-reminder/">Email Reminder plugin</a> by <a href="http://pogidude.com/about/">Ryann Micua</a>';
		
		//get ereminders
		$pd = new Pogidude_Ereminder;
		$ereminders = $pd->get_ereminders();
		
		foreach( $ereminders as $ereminder ){
		
			$subject = '[Reminder] ' . $ereminder->post_title;
			$to = $ereminder->post_excerpt;
			
			//use the email of the user who scheduled the reminder
			$author = get_userdata( $ereminder->post_author );
			$author_email = $author->user_email;
			$headers = 	"From: Email Reminder <{$author_email}>\r\n" .
						"Content-Type: text/html;\r\n";
			
			$creation_date = date( 'l, F j, Y', strtotime( $ereminder->post_date ) );
			$message = "<p>This message is a reminder created on {$creation_date}</p>\n";
			$message .= "<p><strong>REMINDER:</strong><br />\n";
			$message .= $ereminder->post_content . "</p><br />\n";
			$message .= "<p>{$credits}</p>";
			
			$email_result = wp_mail( $to, $subject, $message, $headers );
			//$email_result = wp_mail( 'ryannmicua@gmail.com', 'Test Reminder', 'message', 'From: Email Reminder <ryannmicua@gmail.com>' );
			
			
			if( $email_result ){//wp_mail() processed the request successfully
				//set post to 'publish' or delete the post
				$args = array( 'ID' => $ereminder->ID, 'post_status' => 'publish', 'post_date' => $ereminder->post_date, 'post_date_gmt' => $ereminder->post_date_gmt, 'post_modified' => current_time('mysql',0), 'post_modified_gmt' => current_time('mysql',1) );
				
				//wp_update_post( $args );
				wp_delete_post( $ereminder->ID );
			}
			
		}
	}
	
	/**
	 * Get Ereminders
	 *
	 * Selects all 'ereminders' custom post types from the 'posts' table whose 'post_date' is less than $date and 'post_status' = draft returns rows as a numerically indexed array of objects. Uses $wpdb->get_results() function to fetch the results from the database.
	 *
	 * @param string $date date in YYYY-MM-DD H:i:s format. Defaults to current local time
	 * @return array numerically indexed array of row objects
	 */
	public function get_ereminders( $date = '' ) {
		global $wpdb;
		
		if( $date == '' ){
			$date = current_time( 'mysql',0 );
		}
		
		$ereminders = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM {$wpdb->posts}
			WHERE post_date < '{$date}'
				AND post_type = 'ereminder'
				AND post_status = 'draft'
			ORDER BY post_date ASC
		") );
		
		return $ereminders;
	}//get_ereminders

	/**
	 * Register 'ereminder' Custom Post Type
	 */
	public function register_ereminder_post_type(){
		$labels = array(
			'name' => __('E-Reminders'),
			'singular_name' => __('E-Reminder'),
			'add_new' => _x('Create New', 'entry'),
			'add_new_item' => __('Create E-Reminder' ),
			'edit_item' => __( 'Edit E-Reminder' ),
			'new_item' => __( 'New E-Reminder' ),
			'view_item' => __( 'View E-Reminder' ),
			'search_items' => __( 'Search E-Reminders' ),
			'not_found' => __('No E-Reminders found' ),
			'not_found_in_trash' => __('No E-Reminders found in Trash' ),
			'parent_item_colon' => ''
		);
		
		$args = array(
			'labels' => $labels,
			'public' => false,
			'show_in_nav_menus' => false,
			'exclude_from_search' => true,
			'show_ui' => false,
			'show_in_menu' => false,
			'publicly_queryable' => false,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array(''),
			'description' => 'Stores reminders'
		);
		
		register_post_type( 'ereminder', $args );
	}
	
} //Pogidude_Email_Reminder

//boot strap
//new Pogidude_Ereminder;
