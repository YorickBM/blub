<?php
use ModulairDashboard\Dashboard;

/**
 * Action triggered upon requesting to remove user from dashboard
 * Raw Data: 
 */
add_action('wp_ajax_add_user_to_dashboard', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));
    if(!isset($_POST['user_id']) || empty($_POST['user_id']))  wp_send_json_error(new WP_Error( 'invalid.user.id', 'Invalid User id given.', '' ));

    //Sanitize inputs
    $post_id = sanitize_text_field($_POST['post_id']);
    $user_id = sanitize_text_field($_POST['user_id']);

    //Get object for dashboard with its data
    $dashboard = new Dashboard($post_id);

    //Object controle
    if ($dashboard === null || $dashboard->Name == '') wp_send_json_error(new WP_Error( 'dashboard.not.found', "Uw dashboard is niet gevonden...", '' ));
    if(!get_userdata($user_id)) wp_send_json_error(new WP_Error( 'user.not.found', "We hebben de gebruiker niet gevonden...", '' ));

    //Toegang controle
    if (!$dashboard->userHasAccess(get_current_user_id())) wp_send_json_error(new WP_Error( 'dashboard.no.access', "U heeft geen toegang tot deze functionaliteit!", '' ));
    if ($dashboard->userHasAccess($user_id)) wp_send_json_error(new WP_Error( 'user.has.access', "De gebruiker die u probeert te machtigen, heeft al toegang tot het dashboard!", '' ));

    $dashboard->addAccess($user_id);

    wp_send_json_success(array("msg" => "De gebruiker '" . get_userdata($user_id)->display_name . "' is toegevoegd aan het dashboard."));

});