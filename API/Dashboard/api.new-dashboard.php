<?php

/**
 * Action triggered upon creating a new dashboard
 * Raw Data: array(4) { ["dash_name"]=> string(4) "Test" ["tags"]=> array(1) { [0]=> string(7) "general" } ["users"]=> array(2) { [0]=> string(1) "2" [1]=> string(1) "1" } ["createdash"]=> string(14) "Maak dashboard" }
 */
add_action('new_dash_form', function () {
    if(!isset($_POST['createdash']))  return;

    $name = wp_strip_all_tags($_POST['dash_name']);
    $description = wp_strip_all_tags($_POST['dash_description']);

    $users = $_POST['users'];
    $tags = $_POST['tags'];

    $result = wp_insert_post(
        array(
            'post_title'    => sanitize_text_field($name),
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id(),
            'post_category' => $tags,
            'meta_input'    => array(
                "description"   => sanitize_text_field($description),
                "shared_users"  => $users
            )
        )
    );

    if ( $result && ! is_wp_error( $result ) ) {
        $url = get_permalink($result);
        echo "Klik <a href='$url'> hier </a> om uw zo net aangemaakte dashboard te bekijken.";
    }

});