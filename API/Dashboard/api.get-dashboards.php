<?php

add_action('admin_init', function() {
    add_action('wp_ajax_get_my_dashboards', function() {
        $data = array();

        $user = wp_get_current_user();
        $posts = get_posts(array(
            'author'            => $user->ID,
            'orderby'           =>  'post_date',
            'order'             =>  'ASC',
            'posts_per_page'    => -1
            
        ));
        foreach($posts as $post) {
            $catagories = wp_get_post_categories($post->ID);
            $cats = "";
            $index = 0;

            foreach($catagories as $c){
                $cats .= get_category( $c )->name;
                if(++$index < count($catagories)) $cats .= ", ";
            }

            $url = get_permalink($post->ID);
            array_push($data, array(
                "naam" => "<div class=\"tooltips\"><span class=\"title\">$post->post_title</span><span class=\"tooltip\"><a href=\"$url\"><i class=\"fa-solid fa-arrow-up-right-from-square\"></i> bekijk</a></span></div>",
                "categorieen" => $cats,
                "bijgewerkt_op" => $post->post_modified,
                "auteur" => get_user_by('id', $post->post_author)->display_name
            ));
        }

        wp_die(json_encode(array( "data" => $data, "user" => $user->ID)), 200); //Return json object with success
    });
});