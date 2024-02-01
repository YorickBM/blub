<?php
return array('render_callback' => function($block_attributes, $content){
    $tags = get_categories(array(
        'parent'  => 0,
        'hide_empty' => 0,
        'order'    => 'ASC',
    ));

    ?>
    <script>
        $(document).ready(function() {
            $('.tag-selector').select2();
        });
    </script>
    <?php

    $element = "<select class='tag-selector' name='tags[]' multiple='multiple' placeholder='Selecteer dashboard catagorie'>";
    foreach($tags as $tag) {
        $element .= "<option value='".$tag->slug."'>".$tag->name."</option>";
    }    
    $element .= "</select>";

    return str_replace("<select></select>", $element, $content);
});
?>