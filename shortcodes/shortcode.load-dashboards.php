<?php

use ModulairDashboard\DashboardPlugin;
use ModulairDashboard\Dashboard;

/**
 * Render code for settings form shortcode.
 */
function dashboard_shortcode_load_dashboards() {
    if(!is_user_logged_in()) return; //Not loggedin so we cannot load settings

    $current_user = get_userdata(get_current_user_id());
    ob_start();

    $all_posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1, // Get all posts
    ));

    $public_pinned = array();
    $public_non_pinned = array();
    $pinned_dashboards = array();
    $non_pinned_dashboards = array();

    foreach ($all_posts as $post) {
        $dashboard = new Dashboard($post->ID);

        if($dashboard->IsPublic) {
            if($dashboard->IsPinned) array_push($public_pinned, $dashboard);
            else array_push($public_non_pinned, $dashboard);
            continue; //Skip if its public, no need for duplicates
        }
        if ($dashboard->userHasAccess(get_current_user_id()) ) {
            if($dashboard->IsPinned) array_push($pinned_dashboards, $dashboard);
            else array_push($non_pinned_dashboards, $dashboard);
        }
    }
    ?>
    <div class="dashboard-container">
    <?php    

    foreach(array_merge($pinned_dashboards, $public_pinned) as $dashboard) {
        render_dashboard_widget($dashboard);
    }
    foreach(array_merge($non_pinned_dashboards, $public_non_pinned) as $dashboard) {
        render_dashboard_widget($dashboard);
    }
    ?>
    </div>

    <script>
        Dashboard.loadDashboardOverviewClientToServer();
    </script>

    <?php 
    $html_content = ob_get_clean();
    return $html_content;
};

function render_dashboard_widget($dashboard) {
    ?>
    <a class="dashboard-widget" href="<?php echo get_permalink($dashboard->Id); ?>">
        <div class="dashboard-image">
            <i class="fa-brands fa-hashnode"></i>
        </div>
        <div class="dashboard-information">
            <div class="dashboard-title">
                <span><?php echo $dashboard->Name; ?></span>
                <div class="dashboard-icons"><i post_id="<?php echo $dashboard->Id; ?>" class="fa-solid fa-thumbtack <?php echo ($dashboard->IsPinned) ? 'active' : ''; ?>"></i></div>
            </div>
            <span><?php echo substr($dashboard->Description, 0, 70); ?>...</span>
        </div>
        <div class="dashboard-footer">
            <span><i class="fa-solid fa-tags"></i> <?php echo substr($dashboard->getTagString(), 0, 35); ?><?php if(strlen($dashboard->getTagString()) > 35) echo '...';?></span>
            <span><i class="fa-solid fa-clock"></i> <?php echo date('d-m-Y', strtotime($dashboard->Modified)); ?></span>
        </div>
</a>
    <?php
}