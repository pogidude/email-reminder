<?php

Class PDER_Base {
	
	public function __construct(){
		$this->init();
	}
	
	public function init(){
		//Load files
		$this->loadFiles();
		
		//register Ereminder Custom Post Type
		add_action('init', array( $this,'register_post_type') );
		
		//specify our own cron interval
		add_filter('cron_schedules', array( $this, 'add_cron_intervals' ) );
		
		//register our event
		add_action('PDER_cron_send_reminders', array( 'PDER', 'send_ereminders') );
	}
	
	function loadFiles(){
		/* Ereminder Class */
		require_once( PDER_CLASSES . '/PDER.php' );
		/* Admin */
		require_once( PDER_CLASSES . '/PDER_Admin.php' );
		/* Utilities */
		require_once( PDER_CLASSES . '/PDER_Utils.php' );
	}
	
	/**
	 * Stuff that needs to be done once. This function gets fired on plugin activation
	 */
	static function on_activate(){
		//verify event has not been scheduled
		if( !wp_next_scheduled( 'PDER_cron_send_reminders' ) ){
			//schedule our custom event
			wp_schedule_event( time(), 'PDER_5minutes', 'PDER_cron_send_reminders' );
		}
	}
	
	/**
	 * Stuff that needs to be done on deactivation.
	 */
	static function on_deactivate(){
		//clear scheduled events
		while( wp_next_scheduled( 'PDER_cron_send_reminders' ) ){
			$timestamp = wp_next_scheduled( 'PDER_cron_send_reminders' );
			wp_unschedule_event( $timestamp,'PDER_cron_send_reminders' );
		}
	}
	
	/**
	 * Add our own Cron Intervals
	 */
	public function add_cron_intervals( $schedules ){
		//create a 'minute' recurrent schedule option
		$schedules['PDER_minute'] = array(
			'interval' => 60,
			'display' => 'Every Minute'
		);
		
		$schedules['PDER_twicehourly'] = array(
			'interval' => 60*30,
			'display' => 'Twice Hourly (30 min)'
		);
		
		$schedules['PDER_5minutes'] = array(
			'interval' => 60*5,
			'display' => 'Every 5 minutes'
		);
		return $schedules;
	}

	/**
	 * Register 'ereminder' Custom Post Type
	 */
	public function register_post_type(){
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
new PDER_Base();
