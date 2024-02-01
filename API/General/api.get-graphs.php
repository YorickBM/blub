<?php
use ModulairDashboard\DashboardPlugin;

/**
 * Action triggered upon requesting sensors for post
 * Raw Data: 
 */
add_action('wp_ajax_get_graphs', function () {
    $graphs = DashboardPlugin::get_instance()->graphs_table->select_data("`trashed` LIKE 0");
    $data = array("Graphs" => $graphs);

    if (empty($graphs)) wp_send_json_error(new WP_Error( 'widget.not.found', "No graphs found", '' ));
    wp_send_json_success($data);

});