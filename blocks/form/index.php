<?php
return array('render_callback' => function($block_attributes, $content){
    do_action($block_attributes['action']);
    return $content;
});
?>