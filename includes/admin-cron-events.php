<?php

add_action( 'admin_menu', 'boj_view_cron_menu' );

function boj_view_cron_menu() {
  //create view cron jobs settings page
  add_dashboard_page( 'View Cron Jobs', 'View Cron Jobs', 'manage_options', 'boj-view-cron', 'boj_view_cron_settings' );
}
function boj_view_cron_settings() {
  $cron = _get_cron_array();
  $schedules = wp_get_schedules();
  $date_format = 'M j, Y @ G:i';
  ?>
  <div class="wrap" id="cron-gui">
    <h2><?php _e( 'Cron Events Scheduled', 'email-reminder' ); ?></h2>
    <table class="widefat fixed">
      <thead>
        <tr>
          <th scope="col"><?php _e( 'Next Run (GMT/UTC)', 'email-reminder' ); ?></th>
          <th scope="col"><?php _e( 'Schedule', 'email-reminder' ); ?></th>
          <th scope="col"><?php _e( 'Hook Name', 'email-reminder' ); ?></th>
        </tr>
      </thead>
    <tbody>
      <?php foreach ( $cron as $timestamp => $cronhooks ) { ?>
        <?php foreach ( (array) $cronhooks as $hook => $events ) { ?>
          <?php foreach ( (array) $events as $event ) { ?>
          <tr>
            <td>
              <?php echo date_i18n( $date_format, wp_next_scheduled( $hook ) ); ?>
            </td>
            <td>
              <?php
              if ( $event[ 'schedule' ] ) {
                echo $schedules[ $event[ 'schedule' ] ][ 'display' ];
              } else {
                _e( 'One-time', 'email-reminder' );
              }
              ?>
            </td>
            <td><?php echo $hook; ?></td>
          </tr>
          <?php } ?>
        <?php } ?>
      <?php } ?>
    </tbody>
    </table>
    <?php echo __( 'Current Time:', 'email-reminder' ) . ' ' . current_time('mysql',1); ?>
  </div>
  <?
}
?>
