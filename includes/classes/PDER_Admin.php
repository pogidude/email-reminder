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
		add_action('admin_init', array( &$this, 'process_submissions' ) );
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
			'date' => date( 'Y-m-d', $timenow ),
			'id' => ''
		);

		$data['fields'] = !empty( $this->_field_data ) ? $this->_field_data : $empty_fields;
		
		$data['messages'] = $this->_messages;
		
		$data['action'] = isset( $_REQUEST['pder-action'] ) && $_REQUEST['pder-action'] == 'edit' ? 'update' : 'add';
		
		$file = 'ereminder-page.php';
		//header('Content-type: text/html; charset=utf-8');
		echo PDER_Utils::get_view( $file, $data );
	}
	
	function process_submissions(){
		if( !isset( $_REQUEST['pder-submit'] ) || $_REQUEST['pder-submit'] != 'true' ) return;
		
		if( isset( $_POST['pder-action'] ) && $_POST['pder-action'] == 'add' && check_admin_referer( 'pder-submit-reminder', 'pder-submit-reminder-nonce' ) ){
			//A reminder was submitted for scheduling
			$this->schedule_reminder( $_POST );
			
		} elseif( isset( $_POST['pder-action'] ) && $_POST['pder-action'] == 'update' && check_admin_referer( 'pder-submit-reminder', 'pder-submit-reminder-nonce' ) ){
			//Update reminder
			$this->update_reminder( $_POST );
			
		} elseif( $_REQUEST['pder-action'] == 'edit' && wp_verify_nonce( $_REQUEST['pder-edit-reminder-nonce'], 'pder-edit-reminder' ) ){
			//Edit reminder. Stages reminder for updating
			$this->edit_reminder( $_REQUEST );
			
		} elseif( $_REQUEST['pder-action'] == 'delete' && wp_verify_nonce( $_REQUEST['pder-delete-reminder-nonce'], 'pder-delete-reminder' ) ){
			//Delete reminder
			$this->delete_reminder( $_REQUEST );
			
		} elseif( $_REQUEST['pder-action'] == 'delete-all' && wp_verify_nonce( $_REQUEST['pder-delete-all-sent-nonce'], 'pder-delete-all-sent' ) ){
			//delete all sent reminders
			$this->delete_reminders_many( $_REQUEST, 'sent' );
		}
	}
	
	function update_reminder( $data ){
		$this->schedule_reminder( $data );
	}
	
	function schedule_reminder( $data ){
		$clean = array();
		$error = array();

		if( empty( $data['pder'] ) || !is_array( $data['pder'] ) ) return;

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
		
		if( isset( $data['postid'] ) && "" !== $data['postid'] && $data['pder-action'] == 'update' ){
			$reminder['ID'] = $data['postid'];
		}
		
		if( empty( $error ) ){
			//create new post
			$insert_post_id = wp_insert_post( $reminder );
			
			/** In theory, $insert_post_id can be 0, but very unlikely on a WP site **/
			if( empty( $insert_post_id ) ){
				$this->_messages['error'][] = 'There was an error scheduling your reminder.';
			} else {
				if( $data['pder-action'] == 'update' ){
					$this->_messages['success'][] = 'Updated reminder <strong>#' . $insert_post_id . '</strong> scheduled for ' . date( 'F j, Y h:i A', strtotime( $date_all ) ) . '.';
				} else {
					$this->_messages['success'][] = 'Reminder <strong>#' . $insert_post_id . '</strong> scheduled for ' . date( 'F j, Y h:i A', strtotime( $date_all ) ) . ' added.';
				}
				
				//set to defaults
				$clean = array(
					'reminder' => '',
					'email' => '',
					'time' => date( 'h:00 a', $timenow + 60*60 ),
					'date' => date( 'Y-m-d', $timenow ),
					'id' => ''
				);
			}
			
		} else {
			$this->_messages['error'] = $error;
		}
		
		$this->_field_data = $clean;
	}
	
	function edit_reminder( $data ){
		//get ID
		$post_id = $data['postid'];
		$post = get_post( $post_id );
		
		$fields = array(
			'reminder' => isset($post->post_content) ? $post->post_content : '',
			'email' => isset( $post->post_excerpt ) ? $post->post_excerpt : '',
			'time' => isset( $post->post_date ) ? date( 'h:i a', strtotime($post->post_date) ) : date( 'h:00 a', $timenow + 60*60 ),
			'date' => isset( $post->post_date ) ? date( 'Y-m-d', strtotime( $post->post_date) ) : date( 'Y-m-d', $timenow ),
			'id' => $post->ID
		);
		
		$message = 'Editing Reminder <strong>#' . $post->ID.'</strong>';
		
		if( isset( $data['ajax'] ) && $data['ajax'] == 'true' ){
			$return = array(
				'fields' => $fields,
				/** TODO: add new nonce? Refer http://wordpress.stackexchange.com/questions/19826/multiple-ajax-nonce-requests **/
				'messages' => array(
					'success' => array( 
						$message
					)
				)
			);
			echo json_encode( $return );
			exit();
		} else {
			$this->_field_data = $fields;
			$this->_messages['success'][] = $message;
		}
	}
	
	function delete_reminder( $data ){

		$post_id = $data['postid'];
		$post = get_post( $post_id );
		$error = array();
		$success = array();
		
		if( empty( $post ) ){
			$error[] = 'Error: Invalid ID: <strong>#'. $post_id . '</strong>.';
		} else {
			$result = wp_delete_post( $post_id, true ); //bypass trash and force deletion
			if( !$result ){
				//failure
				$error[] = 'Error: Failure deleting reminder <strong>#'. $post_id . '</strong>. Please try again.';
			} else {
				//successful
				$success[] = 'Reminder <strong>#' . $post_id . '</strong> deleted.';
			}
		}
		
		/** Return response **/
		if( isset( $data['ajax'] ) && $data['ajax'] == 'true' ){
			$response = array(
				/** TODO: add new nonce? Refer http://wordpress.stackexchange.com/questions/19826/multiple-ajax-nonce-requests **/
				'messages' => array(
					'success' => $success,
					'error' => $error
				)
			);
			echo json_encode( $response );
			exit();
		} else {
			$this->_messages = array(
				'success' => $success,
				'error' => $error
			);
		}
	}
	
	function delete_reminders_many( $data, $status = 'sent' ){
		//get ereminders
		$pd = new PDER;
		$ereminders = $pd->get_ereminders(current_time( 'mysql',0 ), 'sent');
		$success = array();
		$error = array();
		if( empty( $ereminders ) ){
			/** TODO: error message? **/
		} else {
			foreach( $ereminders as $ereminder ){
				if( wp_delete_post( $ereminder->ID ) ){
					$success[] = 'Reminder <strong>#' . $ereminder->ID . '</strong> deleted.';
				} else {
					$error[] = 'Error deleting reminder <strong>#'. $ereminder->ID . '</strong>.';
				}
			}
		}
		
		/** Return responses **/
		if( isset( $data['ajax'] ) && $data['ajax'] == 'true' ){
			$response = array(
				/** TODO: add new nonce? Refer http://wordpress.stackexchange.com/questions/19826/multiple-ajax-nonce-requests **/
				'messages' => array(
					'success' => $success,
					'error' => $error
				)
			);
			echo json_encode( $response );
			exit();
		} else {
			$this->_messages = array(
				'success' => $success,
				'error' => $error
			);
		}
		
	}
	
	function load_assets(){
		/** Scripts **/
		wp_enqueue_script('pder-admin-script' );
		
		/** Styles **/
		wp_enqueue_style('pder-admin-style' );
		wp_enqueue_style('pder-datepicker-css' );
		wp_enqueue_style('pder-datepicker-css-custom' );
	}
	
	function get_js_vars(){
		
	}
}
