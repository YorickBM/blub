<?php
namespace ModulairDashboard;

class AdminGraphs {

    public static function register() {
        add_admin_menu_separator(40);

        add_menu_page(
            'Grafieken',     // page title
            'Grafieken',     // menu title
            'manage_graphs',   // capability
            'custom_graphs',     // menu slug
            array( '\ModulairDashboard\AdminGraphs', 'render_overview' ), // callback function
            "dashicons-chart-line", //Icon URL
            41 //Position
        );

        add_submenu_page(
            "custom_graphs", // parent slug
            "Alle grafieken", // page title
            "Alle grafieken", // menu title
            "manage_graphs", //capability
            "custom_graphs", // menu slug (Same as main)
            array( '\ModulairDashboard\AdminGraphs', 'render_overview' ), // callback function
        );

        add_submenu_page(
            "custom_graphs", // parent slug
            "Nieuwe grafiek", // page title
            "Nieuwe grafiek", // menu title
            "manage_graphs", //capability
            "new_graphs", // menu slug
            array( '\ModulairDashboard\AdminGraphs', 'render_add' ), // callback function
        );
    }

    public static function render_overview() {
        ?>

        <?php
    }

    public static function render_add() {
        ?>

        <?php
    }
}

?>