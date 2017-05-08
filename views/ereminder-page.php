<?php
$fields = $data['fields'];
$messages = $data['messages'];
$action = $data['action'];
?>
<div class="wrap ereminder">
	<?php screen_icon('edit-comments'); ?>
	<h2 class="page-title"><?php _e( 'Create Email Reminder', 'email-reminder' ); ?></h2>
	
	<?php if( !empty( $messages ) ) : ?>
		<?php if( !empty( $messages['error'] ) ): ?>
			<div class="error message">
			<?php foreach( $messages['error'] as $message ): ?>
				<?php echo $message; ?><br />
			<?php endforeach; ?>
			</div>
		<?php elseif( !empty( $messages['success'] ) ): ?>
			<div class="updated message">
			<?php foreach( $messages['success'] as $message ): ?>
				<?php echo $message; ?><br />
			<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	
	<form method="POST" action="">
		<p class="field">
			<label for="pd-reminder-content"><?php _e( 'Enter your reminder', 'email-reminder' ); ?></label>
			<input type="text" size="40" name="pder[reminder]" id="pd-reminder-content" placeholder="<?php _e( 'Send Dad a birthday card', 'email-reminder' ); ?>" value="<?php echo $fields['reminder']; ?>" title="<?php _e( 'Type your reminder here.', 'email-reminder' ); ?>" />
		</p>
		<p class="field">
			<label for="pd-reminder-email" title="<?php _e( 'Leave this field blank to send email to yourself', 'email-reminder' ); ?>"><?php _e( 'Email address to send reminder to', 'email-reminder' ); ?></label>
			<input type="email" size="40" name="pder[email]" id="pd-reminder-email" placeholder="<?php _e( 'youemailaddress@email.com', 'email-reminder' ); ?>" title="<?php _e( 'Where to email the reminder to. Leave this field blank to send email to yourself', 'email-reminder' ); ?>" value="<?php echo $fields['email']; ?>" />
		</p>
		<p class="field">
			<label for="pd-reminder-date"><?php _e( 'When to send reminder', 'email-reminder' ); ?></label>
			<input type="text" size="20" name="pder[date]" id="pd-reminder-date" value="<?php echo $fields['date']; ?>" placeholder="YYYY-MM-DD" title="<?php _e( 'Set the date for the reminder (Format: YYYY-MM-DD)', 'email-reminder' ); ?>" />
			<input type="text" size="15" name="pder[time]" id="pd-reminder-time" value="<?php echo $fields['time']; ?>" placeholder="<?php echo date( 'H:00', strtotime( current_time('mysql',0) ) ); ?>" title="<?php _e( 'Set the time for the reminder. Format: HH:MM. Example: 15:30 or 3:30pm', 'email-reminder' ); ?>" />
			<br />
			<span class="regular server-time description"><strong><?php _e( 'Current Time:', 'email-reminder' ); ?></strong> <code><?php echo  date( 'F j, Y h:i A', strtotime( current_time('mysql') ) ); ?></code> <?php _e( 'as set in the', 'email-reminder' ); ?> <a href="<?php echo admin_url('options-general.php'); ?>"><?php _e( 'Timezone settings', 'email-reminder' ); ?></a></span>
		</p>
		
		<?php if( $action == 'update' ) : ?>
			<input type="submit" value="<?php _e( 'Edit Reminder', 'email-reminder' ); ?>" class="button-primary button" />
			<a href="<?php echo admin_url('admin.php?page=pogidude-ereminder'); ?>" class="button-secondary button"><?php _e( 'Cancel Editing', 'email-reminder' ); ?></a>
		<?php else: ?>
			<input type="submit" value="<?php _e( 'Add Reminder', 'email-reminder' ); ?>" class="button-primary" />
		<?php endif; ?>
		
		<input type="hidden" name="pder-action" value="<?php echo $action; ?>" />
		<input type="hidden" name="pder-submit" value="true" />
		<input type="hidden" id="pder-postid" name="postid" value="<?php echo $fields['id']; ?>" />
		<?php wp_nonce_field( 'pder-submit-reminder', 'pder-submit-reminder-nonce' ); ?>
	</form>
	
	<div class="reminder-list pder-scheduled">
		<h3><?php _e( 'Scheduled Reminders', 'email-reminder' ); ?></h3>
		<?php
			global $wpdb;
			
			$current_time = current_time('mysql') + 60;
			
			$ereminder_array = $wpdb->get_results( $wpdb->prepare("
							SELECT *
							FROM {$wpdb->posts}
							WHERE post_date <= %s
								AND post_type = 'ereminder'
								AND post_status = 'draft'
							ORDER BY post_date ASC
							", $current_time) );
			$scheduled_data = array(
				'list' => $ereminder_array,
				'type' => 'scheduled'
			);
			echo PDER_Utils::get_view( 'ereminder-list.php', $scheduled_data );
		?>
	</div>
	
	<div class="reminder-list pder-sent">
		<?php
		$delete_all_link = add_query_arg( array(
			'page' => 'pogidude-ereminder',
			'pder-action' => 'delete-all',
			'pder-submit' => 'true',
			'pder-delete-all-sent-nonce' => wp_create_nonce( 'pder-delete-all-sent' ),
		), admin_url('admin.php') );
		?>
		<h3>Sent Reminders <a href="<?php echo esc_url( $delete_all_link ); ?>" class="button-secondary"><?php _e( 'Delete all sent reminders', 'email-reminder' ); ?></a></h3>
		<?php
			global $wpdb;
			
			$current_time = current_time('mysql') + 60;
			
			$ereminder_array = $wpdb->get_results( $wpdb->prepare("
							SELECT *
							FROM {$wpdb->posts}
							WHERE post_date <= %s
								AND post_type = 'ereminder'
								AND post_status = 'publish'
							ORDER BY post_date DESC
							", $current_time) );
			$scheduled_data = array(
				'list' => $ereminder_array,
				'type' => 'sent'
			);
			echo PDER_Utils::get_view( 'ereminder-list.php', $scheduled_data );
		?>
	</div>
</div>
