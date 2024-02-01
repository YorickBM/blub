<?php

namespace ModulairDashboard;

class AdminGraphs {

    public static function register() {
        add_menu_page(
            'Grafieken',     // page title
            'Grafieken',     // menu title
            'manage_graphs',   // capability
            'custom_graphs',     // menu slug
            array( '\ModulairDashboard\AdminGraphs', 'render_overview' ), // callback function
            "dashicons-chart-line", //Icon URL
            43 //Position
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

        if(isset($_REQUEST['page']) && $_REQUEST['page'] === 'edit_graphs')
            add_submenu_page(
                "custom_graphs", // parent slug
                "Bewerk grafiek", // page title
                "Bewerk grafiek", // menu title
                "manage_graphs", //capability
                "edit_graphs", // menu slug
                array( '\ModulairDashboard\AdminGraphs', 'render_edit' ), // callback function
            );
    }

    public static function render_overview() {
        include DashboardPlugin::get_instance()->getDir() . "/pages/admin.graphs.all.php";
    }

    public static function render_add() {
        AdminGraphs::process_new_graph();
        include DashboardPlugin::get_instance()->getDir() . "/pages/admin.graphs.new.php";
    }

    public static function render_edit() {
        AdminGraphs::process_edit_graph();
        include DashboardPlugin::get_instance()->getDir() . "/pages/admin.graphs.edit.php";
    }

    private static function process_edit_graph() {
        $plugin = DashboardPlugin::get_instance();
        if(isset($_POST['updategraph'])) {
            if(strlen($_POST['graph_name']) < 4) {
                $plugin->setMsg("Geef uw grafiek alsjeblieft een naam.");
                add_action( 'graph_notices', array( $plugin , 'error_notice' ) );
                return;
            }

            if(strlen($_POST['graph_script']) < 4) {
                $plugin->setMsg("U kunt geen grafiek aanmaken, zonder een script en/of element te specificeren.");
                add_action( 'graph_notices', array( $plugin , 'error_notice' ) );
                return;
            }

            $plugin->graphs_table->update_data(array(
                "name" => $_POST['graph_name'],
                "css" => $_POST['graph_css'],
                "js" => $_POST['graph_js'],
                "filter" => $_POST['graph_filter'],
                "script" => $_POST['graph_script'],
            ), array("id" => $_POST['graph_id']));

            $plugin->setMsg("De grafiek '{$_POST['graph_name']}' is successvol geupdate!");
            add_action( 'graph_notices', array( $plugin , 'success_notice' ) );
            unset($_POST);
        }
    }

    private static function process_new_graph() {
        $plugin = DashboardPlugin::get_instance();
        if(isset($_POST['creategraph'])) {
            if(strlen($_POST['graph_name']) < 4) {
                $plugin->setMsg("Geef uw grafiek alsjeblieft een naam.");
                add_action( 'graph_notices', array( $plugin , 'error_notice' ) );
                return;
            }

            if(strlen($_POST['graph_script']) < 4) {
                $plugin->setMsg("U kunt geen grafiek aanmaken, zonder een script en/of element te specificeren.");
                add_action( 'graph_notices', array( $plugin , 'error_notice' ) );
                return;
            }

            $id = $plugin->graphs_table->insert_data(array(
                "name" => $_POST['graph_name'],
                "js" => $_POST['graph_js'],
                "css" => $_POST['graph_css'],
                "filter" => $_POST['graph_filter'],
                "script" => $_POST['graph_script']
            ));

            if($id !== 0) {
                $plugin->setMsg("De grafiek '{$_POST['graph_name']}' is successvol geregisteerd!");
                add_action( 'graph_notices', array( $plugin , 'success_notice' ) );
                unset($_POST);
            } else {
                $plugin->setMsg("De grafiek '{$_POST['graph_name']}' hebben we helaas niet kunnen registreren.");
                add_action( 'graph_notices', array( $plugin , 'error_notice' ) );
                return;
            }
        }
    }
}

?>