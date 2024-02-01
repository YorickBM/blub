<?php
use ModulairDashboard\DashboardPlugin;
use ModulairDashboard\Sensor;

/**
 * Action triggered upon requesting to edit a widget
 * Raw Data: 
 */
add_action('wp_ajax_get_widget_config', function () {
    if(!isset($_POST['sensor_id']) || empty($_POST['sensor_id']))  wp_send_json_error(new WP_Error( 'invalid.sensor.id', 'Invalid sensor id given.', '' ));
    if(!isset($_POST['graph_id']) || empty($_POST['graph_id']))  wp_send_json_error(new WP_Error( 'invalid.grpah.id', 'Invalid graph id geven.', '' ));

    $sensor_id = sanitize_text_field($_POST['sensor_id']);
    $graph_id = sanitize_text_field($_POST['graph_id']);
   
    $sensor = DashboardPlugin::get_instance()->getSensorById($sensor_id);
    $graph = DashboardPlugin::get_instance()->getGraphById($graph_id);

    if (empty($sensor) || $sensor == null) wp_send_json_error(new WP_Error( 'sensor.not.found', "Sensor $sensor_id could not be found.", '' ));
    if (empty($graph) || $graph == null) wp_send_json_error(new WP_Error( 'graph.not.found', "Graph $graph_id could not be found.", '' ));

    wp_send_json_success(array("type" => $graph->filter, "options" =>  $sensor->getHeaders()));

});