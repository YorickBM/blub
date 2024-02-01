<?php
use ModulairDashboard\Widget;
use ModulairDashboard\Widget_Position;
use ModulairDashboard\Widget_Size;

/**
 * Action triggered upon requesting to remove a widget
 * Raw Data: 
 */
add_action('wp_ajax_create_widget', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));
    if(!isset($_POST['widget_id']) || empty($_POST['widget_id']))  wp_send_json_error(new WP_Error( 'invalid.widget.id', 'Invalid widget id geven.', '' ));

    //Sanitze inputs
    $widget_id = sanitize_text_field($_POST['widget_id']);
    $post_id = sanitize_text_field($_POST['post_id']);
    
    //Load existing widgets
    $widgets = get_post_meta( $post_id, 'graphs', true );
    if(!is_array($widgets)) $widgets = array(); //Its empty

    $last_row = 1;
    $bigest_width = 1;
    foreach($widgets as $widget) {
        if($last_row < $widget->Position->Row) $last_row = $widget->Position->Row;
        if($bigest_width < $widget->Size->Width) $bigest_width = $widget->Size->Width;
    }


    //Create default objects
    $widget = new Widget("Titel", new Widget_Position(NULL,NULL), new Widget_Size(NULL,NULL), NULL, NULL, array(), array());
    $widget_data = (object)$_POST['widget'];

    //Update the data
    $widget->Title = $widget_data->title;
    $widget->Sensor = $widget_data->sensor;

    $widget->Size->Width = $bigest_width;
    $widget->Size->Height = 1;

    $widget->Position->Row = ($last_row + 1);
    $widget->Position->Column = 1;

    $widget->Graph = $widget_data->graph;
    
    if(isset($widget_data->filterX)) $widget->FilterX = $widget_data->filterX;
    else $widget->FilterX = array();
    if(isset($widget_data->filterY)) $widget->FilterY = $widget_data->filterY;
    else $widget->FilterY = array();
    
    //Push new widget into array
    array_push($widgets, $widget);
    update_post_meta($post_id, 'graphs', $widgets);

    //Return result
    wp_send_json_success(array("msg" => "Widget " . $widget->Title . " is aangemaakt!"));
});