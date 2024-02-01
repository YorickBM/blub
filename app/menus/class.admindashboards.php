<?php

namespace ModulairDashboard;

class AdminDashboards {
    public static function register() {

        add_menu_page(
            'Dashboards',     // page title
            'Dashboards',     // menu title
            'manage_dashboards',   // capability
            'custom_dashboards',     // menu slug
            array( '\ModulairDashboard\AdminDashboards', 'render_overview' ), // callback function
            "dashicons-screenoptions", //Icon URL
            41 //Position
        );

        add_submenu_page(
            "custom_dashboards", // parent slug
            "Alle dashboards", // page title
            "Alle dashboards", // menu title
            "manage_dashboards", //capability
            "custom_dashboards", // menu slug (Same as main)
            array( '\ModulairDashboard\AdminDashboards', 'render_overview' ), // callback function
        );

        add_submenu_page(
            "custom_dashboards", // parent slug
            "Nieuwe dashboard", // page title
            "Nieuwe dashboard", // menu title
            "manage_dashboards", //capability
            "new_dashboards", // menu slug
            array( '\ModulairDashboard\AdminDashboards', 'render_add' ), // callback function
        );

        if(isset($_REQUEST['page']) && $_REQUEST['page'] === 'edit_dashboards')
            add_submenu_page(
                "custom_dashboards", // parent slug
                "Bewerk grafiek", // page title
                "Bewerk grafiek", // menu title
                "manage_dashboards", //capability
                "edit_dashboards", // menu slug
                array( '\ModulairDashboard\AdminDashboards', 'render_edit' ), // callback function
            );
    }

    public static function render_overview() {
        include DashboardPlugin::get_instance()->getDir() . "/pages/admin.dashboards.all.php";
    }

    public static function render_add() {
    }

    public static function render_edit() {
    }
}