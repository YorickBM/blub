<?php
use ModulairDashboard\DashboardPlugin;

/**
 * Action triggered upon requesting to edit a widget
 * Raw Data: 
 */
add_action('wp_ajax_get_widgets', function () {
    if(!isset($_POST['post_id']) || empty($_POST['post_id']))  wp_send_json_error(new WP_Error( 'invalid.post.id', 'Invalid POST id given.', '' ));

    $post_id = sanitize_text_field($_POST['post_id']);
    $widgets = get_post_meta( $post_id, 'graphs', true );

    $html = array();

    if(is_array($widgets)) {
        foreach($widgets as $widget) {
            $sensor = DashboardPlugin::get_instance()->getSensorById($widget->Sensor); //Get sensor object
            $graph = DashboardPlugin::get_instance()->getGraphById($widget->Graph); //Get graph object
            
            ob_start();
            ?>
            <div class="widget-container" id="<?php echo $widget->Id; ?>">
                <div class="widget-title">
                    <span><?php echo $widget->Title; ?></span>
                    <div>
                        <button onClick="Dashboard.editWidget('<?php echo $post_id; ?>', '<?php echo $widget->Id; ?>','widget-editor')"><i class="fas fa-cogs"></i></button>
                        <button class="hideb" onClick="hide(this)" style="display:inline-block;"><i class="fas fa-chevron-up"></i></button>
                        <button class="showb" onClick="show(this)" style="display:none;"><i class="fas fa-chevron-down"></i></button>
                    </div>
                </div>
                <div class="widget-content">
                    <?php 
                        ob_start();
                        echo str_replace("%id%", uniqid(), stripslashes($graph->script)); 
                        $code = ob_get_contents();
                        ob_end_clean();

                        $cols = "[";
                        foreach($sensor->getHeaders() as $head) {
                            $cols .= "'" . $head . "',";
                        }
                        $cols .= "]";

                        $data = "[";
                        foreach($sensor->getData() as $raw) {
                            $data .= "{";
                            foreach($raw as $key => $value) {
                                $data .= "\"$key\": \"$value\",";
                            }
                            substr_replace($data ,"", -1);
                            $data .= "},";
                        }
                        substr_replace($data ,"", -1);
                        $data .= "]";

                        $filterX = "[";
                        foreach($widget->FilterX as $filter) {
                            $filterX .= "'" . $filter . "',";
                        }
                        $filterX .= "]";

                        $filterY = "[";
                        foreach($widget->FilterY as $filter) {
                            $filterY .= "'" . $filter . "',";
                        }
                        $filterY .= "]";

                        echo str_replace("<script>", "<script> var data = { \"cols\": $cols, \"entries\": $data, \"filterX\": $filterX, \"filterY\": $filterY};", $code);
                    ?>

                    <!--Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                    -->
                </div>
            </div>
            <?php
            array_push($html, array(
                "content" => ob_get_contents(),
                "id" => $widget->Id,
                "row" => $widget->Position->Row,
                "col" => $widget->Position->Column,
                "width" => $widget->Size->Width,
            ));
            ob_end_clean(); 
        }
    }

    wp_send_json_success(array("widgets" => $html));
});