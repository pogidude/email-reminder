<?php
/**
 * Admin Pages
 * dashboard_page_pogidude-create-email-reminder 
 */
 
class PDER_Admin{
	
	private $_field_data = null;
	private $_messages = array( 'error' => array(), 'success' => array() );
	
	function init(){
		add_action('admin_menu', array( &$this, 'create_menu' ) );
		add_action('init', array( &$this, 'process_submissions' ) );
	}

	/** Add the admin menu page */
	function create_menu(){
		$hooks = array();
		$hooks[] = add_dashboard_page( 'Create Email Reminder', 'Email Reminder', 'manage_options', 'pogidude-ereminder', array( &$this, 'ereminder_page' ) );
		
		$hooks[] = add_menu_page('Create Email Reminder', 'Email Reminder', 'manage_options', 'pogidude-ereminder', array( &$this, 'ereminder_page' ), PDER_ASSETS . '/images/icon.png' );
		
		foreach( $hooks as $hook ){
			add_action( 'admin_print_scripts-' . $hook, array( &$this, 'load_assets' ) );
		}
	}
	
	function ereminder_page(){
		$data = array();

		$timenow = strtotime( current_time('mysql',0) );//local time
		$timenow_gmt = time();//utc time
		$timedelta = $timenow_gmt - $timenow;//if positive, local time is -gmt. else +gmt
		$error = array();
		
		$empty_fields = array(
			'reminder' => '',
			'email' => '',
			'time' => date( 'h:00 a', $timenow + 60*60 ),
			'date' => date( 'Y-m-d', $timenow )
		);

		$data['fields'] = !empty( $this->_field_data ) ? $this->_field_data : $empty_fields;
		
		$data['messages'] = $this->_messages;
		
		$file = 'ereminder_page.php';
		echo PDER_Utils::get_view( $file, $data );
	}
	
	function process_submissions(){
		if( isset( $_POST['pder-action'] ) && $_POST['pder-action'] == 'submit' && check_admin_referer( 'pder-submit-reminder', 'pder-submit-reminder-nonce' ) ){
			//A reminder was submitted for scheduling
			$this->schedule_reminder( $_POST );
		}
	}
	
	function schedule_reminder( $data ){
		$clean = array();
		$error = array();
		error_log( print_r( $data, true ) );
		if( empty( $data['pder'] ) || !is_array( $data['pder'] ) ) return;
		error_log('processing');
		$pder = $data['pder'];
		
		/** Validate/Sanitize **/
		//Reminder
		if( '' === $pder['reminder'] ){
			$error['reminder'] = 'Please enter a reminder.';
			$clean['reminder'] = '';
		} else {
			$clean['reminder'] = $pder['reminder'];
		}
		//create shortened version of reminder to use as title
		$title = substr( $clean['reminder'], 0, 30 );
		//add elipses to title if needed
		if( strlen( $clean['reminder'] ) > 30 ){
			$title = $title . '...';
		}
		
		//Email
		if( '' === $pder['email'] || !is_email( $pder['email'] ) ){
			$error['email'] = 'Please enter a valid e-mail address.';
			$clean['email'] = '';
		} else {
			$clean['email'] = $pder['email'];
		}
		
		//Dates
		$timenow = strtotime( current_time('mysql',0) );//local time
		$timenow_gmt = time();//utc time
		$timedelta = $timenow_gmt - $timenow;//if positive, local time is -gmt. else +gmt
		
		//validate dates and specify default ones if needed
		if( '' === $pder['date'] ){
			$error['date'] = 'Please enter date in the correct format (YYYY-MM-DD).';
		}
		if( '' === $pder['time'] ){
			$error['time'] = 'Please enter time in the correct format (HH:MM:S).';
		}
		$date_unformatted = empty( $pder['date'] )? $timenow : strtotime( $pder['date'] );
		$time_unformatted = empty( $pder['time'] ) ? $timenow + 60*60 : strtotime( $pder['time'] );
		
		//convert date and time into required format for database entry (YYYY-MM-DD HH:MM:SS)
		$clean['date'] = date( 'Y-m-d', $date_unformatted );
		$clean['time'] = date( 'H:i:s', $time_unformatted );
		$date_all = "{$clean['date']} {$clean['time']}";
		
		//determine gmt time for schedule
		$date_all_gmt = date( 'Y-m-d H:i:s', strtotime( $date_all ) + $timedelta );
		
		//Setup for writing to database
		$reminder = array(
			'post_title' => $title,
			'post_content' => $clean['reminder'],
			'post_type' => PDER_POSTTYPE,
			'post_date' => $date_all,
			'post_date_gmt' => $date_all_gmt,
			'post_excerpt' => $clean['email'],
			'post_status' => 'draft'
		);
		
		if( empty( $error ) ){
			//create new post
			$insert_post_success = wp_insert_post( $reminder );
			
			if( empty( $insert_post_success ) ){
				$this->_messages['error'][] = 'There was an error scheduling your reminder.';
			} else {
				$this->_messages['success'][] = 'Reminder created successfully!';
				
				//set to defaults
				$clean = array(
					'reminder' => '',
					'email' => '',
					'time' => date( 'h:00 a', $timenow + 60*60 ),
					'date' => date( 'Y-m-d', $timenow )
				);
			}
			
		} else {
			$this->_messages['error'] = $error;
		}
		
		$this->_field_data = $clean;
	}
	
	function load_assets(){
		/** Scripts **/
		wp_enqueue_script('pder-admin-script' );
		
		/** Styles **/
		wp_enqueue_style('pder-admin-style' );
		wp_enqueue_style('pder-datepicker-css' );
		wp_enqueue_style('pder-datepicker-css-custom' );
	}
}