<?php

/**
 * Action triggered upon requesting to remove a widget
 * Raw Data: 
 */
add_action('wp_ajax_remove_widget', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));
    if(!isset($_POST['widget_id']) || empty($_POST['widget_id']))  wp_send_json_error(new WP_Error( 'invalid.widget.id', 'Invalid widget id geven.', '' ));

    $widget_id = sanitize_text_field($_POST['widget_id']);
    $post_id = sanitize_text_field($_POST['post_id']);

    $widgets = get_post_meta( $post_id, 'graphs', true );
    if(!is_array($widgets) || count($widgets) <= 0) wp_send_json_error(new WP_Error( 'no.widgets.found', 'No widgets found for post.', '' ));

    $widget = array_filter($widgets, function ($item) use ($widget_id) {
        return $item->Id === $widget_id;
    });
    
    if (empty($widget)) wp_send_json_error(new WP_Error( 'widget.not.found', "Widget $widget_id could not be found.", '' ));
    
    
    update_post_meta($post_id, 'graphs', array_values(array_filter($widgets, function ($item) use ($widget_id) {
        return $item->Id !== $widget_id;
    })));
    wp_send_json_success(array("msg" => "Widget " . reset($widget)->Title . " is verwijderd!"));
});