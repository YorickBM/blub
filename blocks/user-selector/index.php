<?php
return array('render_callback' => function($block_attributes, $content){
    $users = get_users();

    ?>
    <script>
        $(document).ready(function() {
            $('.user-selector').select2();
        });
    </script>
    <?php

    $element = "<select class='user-selector' name='users[]' multiple='multiple' placeholder='Selecteer gebruikers met wie u dit dashboard wilt delen...'>";
    foreach($users as $user) {
        $element .= "<option value='".$user->ID."'>".$user->display_name."</option>";
    }    
    $element .= "</select>";

    return str_replace("<select></select>", $element, $content);
});
?>