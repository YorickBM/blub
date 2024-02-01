<?php

use ModulairDashboard\DashboardPlugin;
use ModulairDashboard\Dashboard;

/**
 * Make it so in the frontend we can run do_action shortcodes.
 * Handy for customization of notification actions!!
 */
function dashboard_shortcode_do_action($atts) {
    $atts = shortcode_atts(
        array(
            'action_name' => '', // Default action name
        ),
        $atts,
        'do_action_shortcode'
    );

    // Now $atts['action_name'] contains the value of the action_name attribute
    $action_name = $atts['action_name'];

    // Check if the action exists
    if (has_action($action_name)) {
        // Start output buffering
        ob_start();

        // Run the action
        do_action($action_name);

        // Get the content of the output buffer and clean the buffer
        $action_output = ob_get_clean();

        return $action_output;
    }

    return has_action($action_name);
}