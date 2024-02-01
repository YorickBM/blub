<?php
use ModulairDashboard\Dashboard;

/**
 * Action triggered upon requesting to remove user from dashboard
 * Raw Data: 
 */
add_action('wp_ajax_trash_dashboard', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));

    //Sanitize inputs
    $post_id = sanitize_text_field($_POST['post_id']);

    //Get object for dashboard with its data
    $dashboard = new Dashboard($post_id);

    //Object controle
    if ($dashboard === null || $dashboard->Name == '') wp_send_json_error(new WP_Error( 'dashboard.not.found', "Uw dashboard is niet gevonden...", '' ));
    
    //Toegang controle
    if (!$dashboard->userHasAccess(get_current_user_id())) wp_send_json_error(new WP_Error( 'dashboard.no.access', "U heeft geen toegang tot deze functionaliteit!", '' ));
    
    $dashboard->moveToTrash();
    wp_send_json_success(array("msg" => "Dashboard '" . $dashboard->Name . "' is verwijderd!", "url" => esc_url(home_url())));

});