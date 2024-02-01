<?php
use ModulairDashboard\Dashboard;

/**
 * Action triggered upon requesting to remove user from dashboard
 * Raw Data: 
 */
add_action('wp_ajax_update_dashboard', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));
    if(!isset($_POST['settings']) || empty($_POST['settings']))  wp_send_json_error(new WP_Error( 'invalid.settings', 'Invalid dashboard settings given.', '' ));

    //Sanitize inputs
    $post_id = sanitize_text_field($_POST['post_id']);
    $settings = $_POST['settings'];

    //Get object for dashboard with its data
    $dashboard = new Dashboard($post_id);

    //Object controle
    if ($dashboard === null || $dashboard->Name == '') wp_send_json_error(new WP_Error( 'dashboard.not.found', "Uw dashboard is niet gevonden...", '' ));
    
    //Toegang controle
    if (!$dashboard->userHasAccess(get_current_user_id())) wp_send_json_error(new WP_Error( 'dashboard.no.access', "U heeft geen toegang tot deze functionaliteit!", '' ));
    
    if(isset($settings['name'])) $dashboard->Name = sanitize_text_field($settings['name']);
    if(isset($settings['description'])) $dashboard->Description = sanitize_text_field($settings['description']);
    if(isset($settings['is_public'])) $dashboard->IsPublic = (sanitize_text_field($settings['is_public']) === "false") ? false : true;
    if(isset($settings['is_pinned'])) $dashboard->IsPinned = (sanitize_text_field($settings['is_pinned']) === "false") ? false : true;
    if(isset($settings['columns'])) $dashboard->Columns = (int)sanitize_text_field($settings['columns']);

    $active_tags = array();
    if((isset($settings['force_tags']) && $settings['force_tags'] === "true") && isset($settings['tags'])) {
        $dashboard->Tags = $settings['tags'];
    }

    $dashboard->saveToWordpress();

    wp_send_json_success(array(
        "msg" => "Dashboard '" . $dashboard->Name . "' is bijgewerkt!",
        "tags" => $dashboard->Tags,
    ));

});