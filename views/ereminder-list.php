<?php
$ereminder_array = $data['list'];

$type = $data['type'];
switch( $type ){
	case 'sent':
		$edit_text = 'reschedule';
		break;
	case 'schedule':
	default:
		$edit_text = 'edit';
		break;
}
?>
<table class="widefat">
	<thead>
		<tr>
			<th class="content">Reminder</th>
			<th class="date">Send Reminder on</th>
			<th class="email">Send To</th>
			<th class="action">Action</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th class="content">Reminder</th>
			<th class="date">Send Reminder on</th>
			<th class="email">Send To</th>
			<th class="action">Action</th>
		</tr>
	</tfoot>
	<tbody>
		<?php if( empty( $ereminder_array ) ) : ?>
			<tr><td colspan="4">No reminders found.</td></t>
		<?php else : ?>
			<?php foreach( $ereminder_array as $ereminder ): ?>
				<tr>
					<td class="content"><?php echo $ereminder->post_content; ?></td>
					<td class="date"><?php echo date( 'l, F j, Y @ g:i a', strtotime( $ereminder->post_date ) ); ?></td>
					<td class="email"><?php echo $ereminder->post_excerpt; ?></td>
					<td class="action"><a href="#"><?php echo $edit_text; ?></a> | <a href="#">Delete</a></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>