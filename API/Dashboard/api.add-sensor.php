<?php
use ModulairDashboard\Dashboard;
use ModulairDashboard\DashboardPlugin;

/**
 * Action triggered upon requesting to remove user from dashboard
 * Raw Data: 
 */
add_action('wp_ajax_add_sensor_to_dashboard', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));
    if(!isset($_POST['sensor_id']) || empty($_POST['sensor_id']))  wp_send_json_error(new WP_Error( 'invalid.sensor.id', 'Invalid Sensor id given.', '' ));

    //Sanitize inputs
    $post_id = sanitize_text_field($_POST['post_id']);
    $sensor_id = sanitize_text_field($_POST['sensor_id']);

    //Get object for dashboard with its data
    $dashboard = new Dashboard($post_id);
    $sensor = DashboardPlugin::get_instance()->sensor_table->select_data("`id` = $sensor_id");

    //Object controle
    if ($dashboard === null || $dashboard->Name == '') wp_send_json_error(new WP_Error( 'dashboard.not.found', "Uw dashboard is niet gevonden...", '' ));
    if(count($sensor) != 1) wp_send_json_error(new WP_Error( 'sensor.not.found', "De sensor die u wilt verwijderen is niet gevonden...", '' ));

    //Toegang controle
    if (!$dashboard->userHasAccess(get_current_user_id())) wp_send_json_error(new WP_Error( 'dashboard.no.access', "U heeft geen toegang tot deze functionaliteit!", '' ));
    if ($dashboard->hasSensor($sensor_id)) wp_send_json_error(new WP_Error( 'sensor.has.access', "De sensor die u wilt toevoegen, heeft al toegang of is al aangevraagd voor dit dashboard!", '' ));

    $dashboard->requestSensor($sensor_id);
    if(!current_user_can('manage_sensors')) 
        wp_send_json_success(array("msg" => "Er is een verzoek aangevraagd voor het toevoegen van de sensor '" . $sensor[0]->name . "'."));
    else {
        $dashboard->approveSensor($sensor_id);
        wp_send_json_success(array("msg" => "We hebben de sensor '" . $sensor[0]->name . "' toegevoegd aan dit dashboard."));
    }

});