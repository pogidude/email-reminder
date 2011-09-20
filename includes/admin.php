<?php
/**
 * Admin Pages
 * dashboard_page_pogidude-create-email-reminder 
 */

add_action('admin_init', 'pogidude_ereminder_stylesscripts' );
add_action('admin_menu', 'pogidude_ereminder_create_menu' );

/** Add the admin menu page */
function pogidude_ereminder_create_menu(){
	$hook = add_dashboard_page( 'Create E-Reminder', 'E-Reminder', 'manage_options', 'pogidude-create-ereminder', 'pogidude_ereminder_page' );
	
	add_action( 'admin_print_scripts-' . $hook, 'pogidude_ereminder_display_scripts' );
	add_action( 'admin_print_styles-' . $hook, 'pogidude_ereminder_display_styles' );
}

/** Draw the menu page **/
function pogidude_ereminder_page(){
	
	$timenow = strtotime( current_time('mysql',0) );//local time
	$timenow_gmt = time();//utc time
	$timedelta = $timenow_gmt - $timenow;//if positive, local time is -gmt. else +gmt
	$error = array();
	
	/* check if submitted. TODO: Use nonces */
	if( empty($_POST) || $_POST['checker'] !== 'submit' ){
		//default
		$content = '';
		$email = '';
		$time = date( 'h:00 a', $timenow + 60*60 );
		$date = date( 'Y-m-d', $timenow );
		$message = '';
		
	} else {
	//if( !empty( $_POST ) && $_POST['checker'] === 'submit' ){
	
		//validate and sanitize content
		if( '' == $_POST['pd-reminder-content'] ){
			$error['content'] = 'Please enter a reminder.';
			$content = '';
		} else {
			$content = esc_attr( $_POST['pd-reminder-content'] );
		}
		
		//create shortened version of content to use as title
		$title = substr( $content, 0, 30 );
		//add elipses to title if needed
		if( strlen( $content ) > 30 ){
			$title = $title . '...';
		}
		
		//validate email
		if( '' == $_POST['pd-reminder-email'] || !is_email( $_POST['pd-reminder-email'] ) ){
			$error['email'] = 'Please enter a valid e-mail address.';
			$email = '';
		} else {
			$email = $_POST['pd-reminder-email'];
		}
		
		//validate dates and specify default ones if needed
		$date_unformatted = empty( $_POST['pd-reminder-date'] )? $timenow : strtotime( $_POST['pd-reminder-date'] );
		$time_unformatted = empty( $_POST['pd-reminder-time'] ) ? $timenow + 60*60 : strtotime( $_POST['pd-reminder-time'] );
		
		//convert date and time into required format for database entry (YYYY-MM-DD HH:MM:SS)
		$date = date( 'Y-m-d', $date_unformatted );
		$time = date( 'H:i:s', $time_unformatted );
		$date_all = "{$date} {$time}";
		
		//determine gmt time for schedule
		$date_all_gmt = date( 'Y-m-d H:i:s', strtotime( $date_all ) + $timedelta );
		
		$reminder = array(
			'post_title' => $title,
			'post_content' => $content,
			'post_type' => 'ereminder',
			'post_date' => $date_all,
			'post_date_gmt' => $date_all_gmt,
			'post_excerpt' => $email,
			'post_status' => 'draft'
		);
		
		if( empty( $error ) ){
			//create new post
			$insert_post_success = wp_insert_post( $reminder );
			
			if( empty( $insert_post_success ) ){
				$message = '<div class="error message">There was an error scheduling your reminder.</div>' . "\n";
			} else {
				$message = '<div class="updated message">Reminder created successfully!</div>' . "\n";
				//set to default
				$content = '';
				$email = '';
				$time = date( 'h:00 a', $timenow + 60*60 );
				$date = date( 'Y-m-d', $timenow );
			}
			
		} else {
			$message = '<div class="message error">' . "\n";
			foreach( $error as $eid => $e ){
				$message .= $e . "<br />\n";
			}
			$message .= '</div>' . "\n";
		}
		
	}
	
	?>
	
	<div class="wrap ereminder">
		<?php screen_icon('edit-comments'); ?>
		<h2 class="page-title">Create Email Reminder</h2>
		
		<?php if( !empty( $message ) ) :
			echo $message;
		endif; ?>
		
		<form method="POST" action="">
			<p class="field">
				<label for="pd-reminder-content">Enter your reminder</label><br />
				<input type="text" size="40" name="pd-reminder-content" id="pd-reminder-content" placeholder="Send Dad a birthday card" value="<?php echo $content; ?>" title="Type your reminder here." />
			</p>
			<p class="field">
				<label for="pd-reminder-email" title="Leave this field blank to send email to yourself">Email address to send reminder to</label><br />
				<input type="email" size="40" name="pd-reminder-email" id="pd-reminder-email" placeholder="youemailaddress@email.com" title="Where to email the reminder to. Leave this field blank to send email to yourself" value="<?php echo $email; ?>" />
			</p>
			<p class="field">
				<label for="pd-reminder-date">When to send reminder</label><br />
				<input type="text" size="20" name="pd-reminder-date" id="pd-reminder-date" value="<?php echo $date; ?>" placeholder="YYYY-MM-DD" title="Set the date for the reminder (Format: YYYY-MM-DD)" />
				<input type="text" size="15" name="pd-reminder-time" id="pd-reminder-time" value="<?php echo $time; ?>" placeholder="<?php echo date( 'H:00', strtotime( current_time('mysql',0) ) ); ?>" title="Set the time for the reminder. Format: HH:MM. Example: 15:30 or 3:30pm" />
				<br />
				<span class="regular server-time description"><strong>Current Time:</strong> <code><?php echo  date( 'F j, Y h:i A', strtotime( current_time('mysql') ) ); ?></code> as set in the <a href="<?php echo admin_url('options-general.php'); ?>">Timezone settings</a></span>
			</p>
			<input type="submit" value="Set Reminder" class="button-primary" />
			<input type="hidden" name="checker" value="submit" />
		</form>
		
		<div class="reminder-list">
			<h3>Scheduled Reminders</h3>
			<?php
				global $wpdb;
				
				$current_time = current_time('mysql') + 60;
				
				$ereminder_array = $wpdb->get_results("
								SELECT *
								FROM {$wpdb->posts}
								WHERE post_date <= '{$current_time}'
									AND post_type = 'ereminder'
									
								ORDER BY post_date ASC
								");
			?>
			
			<table class="widefat">
				<thead>
					<tr>
						<th class="content">Reminder</th>
						<th class="date">Send Reminder on</th>
						<th class="email">Send To</th>
						<?php //<th class="status">Status</th> ?>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class="content">Reminder</th>
						<th class="date">Reminder Date</th>
						<th class="email">Send To</th>
						<?php //<th class="status">Status</th> ?>
					</tr>
				</tfoot>
				<tbody>
					<?php if( empty( $ereminder_array ) ) : ?>
						<tr><td colspan="4">There are currently no scheduled reminders.</td></t>
					<?php else : ?>
						<?php foreach( $ereminder_array as $ereminder ): ?>
							<tr>
								<td class="content"><?php echo $ereminder->post_content; ?></td>
								<td class="date"><?php echo date( 'l, F j, Y @ g:i a', strtotime( $ereminder->post_date ) ); ?></td>
								<td class="email"><?php echo $ereminder->post_excerpt; ?></td>
								<?php //<td class="status"><?php echo $ereminder->post_status == 'draft' ? 'Scheduled' : 'Sent'; </td> ?>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		
	</div>
	
	<?php
}

/** Register styles and scripts **/
function pogidude_ereminder_stylesscripts(){
	wp_register_script('jquery-datepicker', PD_EREMINDER_JS . '/jquery-ui-1.8.16.custom.min.js', array( 'jquery', 'jquery-ui-core' ) );
	wp_register_script('jquery-timepicker', PD_EREMINDER_JS . '/jquery.ui.timepicker.addon.js', array( 'jquery-datepicker' ) );
	wp_register_script('pogidude-ereminder-script', PD_EREMINDER_JS . '/script.js', array( 'jquery-datepicker', 'jquery-timepicker' ) );
	wp_register_style('pogidude-ereminder-style', PD_EREMINDER_CSS . '/style.css' );
	wp_register_style('jquery-datepicker-css', PD_EREMINDER_CSS . '/jquery.ui.datepicker.css' );
	wp_register_style('jquery-datepicker-css-custom', PD_EREMINDER_CSS . '/jquery-ui-1.8.16.custom.css' );
}

/** Enqueue scripts **/
function pogidude_ereminder_display_scripts(){
	wp_enqueue_script('pogidude-ereminder-script' );
}

/** Enqueue styles **/
function pogidude_ereminder_display_styles(){
	wp_enqueue_style('pogidude-ereminder-style' );
	wp_enqueue_style('jquery-datepicker-css' );
	wp_enqueue_style('jquery-datepicker-css-custom' );
}