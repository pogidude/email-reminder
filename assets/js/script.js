jQuery(document).ready(function(){

	jQuery("#pd-reminder-date").datepicker({
		//dateFormat : '@'
		dateFormat : 'yy-mm-dd'
	});
	
	jQuery("#pd-reminder-time").timepicker({
		ampm: true,
		timeFormat: 'hh:mm tt',
		stepHour: 1,
		hourGrid: 6,
		stepMinute: 15,
		minuteGrid: 15
	});
	
	
});
