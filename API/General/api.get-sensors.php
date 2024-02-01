<?php
use ModulairDashboard\DashboardPlugin;

/**
 * Action triggered upon requesting sensors for post
 * Raw Data: 
 */
add_action('wp_ajax_get_sensors', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));
    $post_id = sanitize_text_field($_POST['post_id']);

    $sensors = DashboardPlugin::get_instance()->sensor_request_table->select_query("INNER JOIN wp_sensors ON wp_sensor_requests.sensor_id = wp_sensors.id WHERE `post_id` = '$post_id' AND `approved_on` IS NOT NULL");
    $data = array("Sensors" => $sensors);

    if (empty($sensors)) wp_send_json_error(new WP_Error( 'widget.not.found', "No sensors found for $post_id.", '' ));
    wp_send_json_success($data);

});