<?php

class PDER{

	/**
	 * Get Ereminders
	 *
	 * Selects all 'ereminders' custom post types from the 'posts' table whose 'post_date' is less than $date and 'post_status' = draft returns rows as a numerically indexed array of objects. Uses $wpdb->get_results() function to fetch the results from the database.
	 *
	 * @param string $date date in YYYY-MM-DD H:i:s format. Defaults to current local time
	 * @param string $status draft|publish. corresponds to scheduled and sent reminders respectively
	 * @return array numerically indexed array of row objects
	 */
	public function get_ereminders( $date = '', $status = 'draft' ) {
		global $wpdb;
		
		if( $date == '' ){
			$date = current_time( 'mysql',0 );
		}
		
		if( $status == 'sent' ) 
			$status = 'publish';
		elseif( $status == 'scheduled' ) 
			$status = 'draft';
		
		$ereminders = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM {$wpdb->posts}
			WHERE post_date < %s
				AND post_type = 'ereminder'
				AND post_status = %s
			ORDER BY post_date ASC
		", $date, $status) );
		
		return $ereminders;
	}//get_ereminders
	
	/**
	 * Send Ereminders
	 */
	public static function send_ereminders(){
		
		//credits
		$credits = 'This reminder was sent using <a href="http://pogidude.com/email-reminder/">Email Reminder plugin</a> by <a href="http://pogidude.com/about/">Pogidude Web Studio</a>';
		
		//get ereminders
		$pd = new PDER;
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
				
				wp_update_post( $args );
				//wp_delete_post( $ereminder->ID );
			}
			
		}
	}
	
}