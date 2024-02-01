<?php

/**
 * Action triggered upon requesting to remove a widget
 * Raw Data: 
 */
add_action('wp_ajax_update_widget_position', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));
    if(!isset($_POST['widget_id']) || empty($_POST['widget_id']))  wp_send_json_error(new WP_Error( 'invalid.widget.id', 'Invalid widget id geven.', '' ));
    if(!isset($_POST['position']) || empty($_POST['position']))  wp_send_json_error(new WP_Error( 'invalid.position', 'Invalid position geven.', '' ));

    $widget_id = sanitize_text_field($_POST['widget_id']);
    $post_id = sanitize_text_field($_POST['post_id']);

    $widgets = get_post_meta( $post_id, 'graphs', true );
    if(!is_array($widgets) || count($widgets) <= 0) wp_send_json_error(new WP_Error( 'no.widgets.found', 'No widgets found for post.', '' ));

    $widget = reset(array_filter($widgets, function ($item) use ($widget_id) {
        return $item->Id === $widget_id;
    }));
    $widgets = array_values(array_filter($widgets, function ($item) use ($widget_id) {
        return $item->Id !== $widget_id;
    }));
    
    if (empty($widget)) wp_send_json_error(new WP_Error( 'widget.not.found', "Widget $widget_id could not be found.", '' ));
    
    $widget_data = (object)$_POST['position'];
    $widget->Position->Row = $widget_data->row;
    $widget->Position->Column = $widget_data->col;
    
    array_push($widgets, $widget);
    update_post_meta($post_id, 'graphs', $widgets);
    wp_send_json_success(array("msg" => "Widget " . $widget->Title . " is geupdate!"));
});