<?php
use ModulairDashboard\Dashboard;

/**
 * Action triggered upon requesting sensors for post
 * Raw Data: 
 */
add_action('wp_ajax_get_dashboard', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));

    $post_id = sanitize_text_field($_POST['post_id']);
    $dashboard = new Dashboard($post_id);

    if ($dashboard === null || $dashboard->Name == '') wp_send_json_error(new WP_Error( 'dashboard.not.found', "Uw dashboard is niet gevonden...", '' ));
    if (!$dashboard->userHasAccess(get_current_user_id())) wp_send_json_error(new WP_Error( 'dashboard.no.access', "U heeft geen toegang tot deze functionaliteit!", '' ));

    wp_send_json_success($dashboard->getPublicStructure());

});