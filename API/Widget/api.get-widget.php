<?php
use ModulairDashboard\DashboardPlugin;

/**
 * Action triggered upon requesting to edit a widget
 * Raw Data: 
 */
add_action('wp_ajax_get_widget', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));
    if(!isset($_POST['widget_id']) || empty($_POST['widget_id']))  wp_send_json_error(new WP_Error( 'invalid.widget.id', 'Invalid widget id geven.', '' ));

    $widget_id = sanitize_text_field($_POST['widget_id']);
    $post_id = sanitize_text_field($_POST['post_id']);

    $widgets = get_post_meta( $post_id, 'graphs', true );
    if(!is_array($widgets) || count($widgets) <= 0) wp_send_json_error(new WP_Error( 'no.widgets.found', 'No widgets found for post.', '' ));

    $widget = array_filter($widgets, function ($item) use ($widget_id) {
        return $item->Id === $widget_id;
    });

    $sensors = DashboardPlugin::get_instance()->sensor_request_table->select_query("INNER JOIN wp_sensors ON wp_sensor_requests.sensor_id = wp_sensors.id WHERE `post_id` = '$post_id' AND `approved_on` IS NOT NULL");
    $data = array("Widget" => reset($widget), "Sensors" => $sensors);

    if (empty($widget)) wp_send_json_error(new WP_Error( 'widget.not.found', "Widget $widget_id could not be found.", '' ));
    wp_send_json_success($data);

});