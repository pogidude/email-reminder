<?php
$ereminder_array = $data['list'];

$type = $data['type'];
switch( $type ){
	case 'sent':
		$edit_text = __( 'Reschedule', 'email-reminder' );
		break;
	case 'schedule':
	default:
		$edit_text = __( 'Edit', 'email-reminder' );
		break;
}
?>
<table class="widefat">
	<thead>
		<tr>
			<th class="id">ID</th>
			<th class="content"><?php _e( 'Reminder', 'email-reminder' ); ?></th>
			<th class="date"><?php _e( 'Send Reminder on', 'email-reminder' ); ?></th>
			<th class="email"><?php _e( 'Send To', 'email-reminder' ); ?></th>
			<th class="action"><?php _e( 'Action', 'email-reminder' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th class="id"><?php _e( 'ID', 'email-reminder' ); ?></th>
			<th class="content"><?php _e( 'Reminder', 'email-reminder' ); ?></th>
			<th class="date"><?php _e( 'Send Reminder on', 'email-reminder' ); ?></th>
			<th class="email"><?php _e( 'Send To', 'email-reminder' ); ?></th>
			<th class="action"><?php _e( 'Action', 'email-reminder' ); ?></th>
		</tr>
	</tfoot>
	<tbody>
		<?php if( empty( $ereminder_array ) ) : ?>
			<tr><td colspan="5"><?php _e( 'No reminders found.', 'email-reminder' ); ?></td></t>
		<?php else : ?>
			<?php foreach( $ereminder_array as $ereminder ): ?>
				<tr data-id="<?php echo $ereminder->ID; ?>">
					<td class="id"><?php echo $ereminder->ID; ?></td>
					<td class="content"><?php echo $ereminder->post_content; ?></td>
					<td class="date"><?php echo date( 'l, F j, Y @ g:i a', strtotime( $ereminder->post_date ) ); ?></td>
					<td class="email"><?php echo $ereminder->post_excerpt; ?></td>
					<?php
					$edit_link = add_query_arg( array(
						'page' => 'pogidude-ereminder',
						'pder-submit' => 'true',
						'pder-action' => 'edit',
						'pder-edit-reminder-nonce' => wp_create_nonce( 'pder-edit-reminder' ),
						'postid' => $ereminder->ID
					), admin_url('admin.php') );
					
					$delete_link = add_query_arg( array(
						'page' => 'pogidude-ereminder',
						'pder-action' => 'delete',
						'pder-submit' => 'true',
						'pder-delete-reminder-nonce' => wp_create_nonce( 'pder-delete-reminder' ),
						'postid' => $ereminder->ID
					), admin_url('admin.php') );
					?>
					<td class="action"><a class="pder-edit-link" href="<?php echo esc_url( $edit_link ); ?>"><?php echo $edit_text; ?></a> | <a class="pder-delete-link" href="<?php echo esc_url( $delete_link ); ?>"><?php _e( 'Delete', 'email-reminder' ); ?></a></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>