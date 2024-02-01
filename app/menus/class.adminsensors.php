<?php

namespace ModulairDashboard;

/**
 * Class to manage admin page for sensors
 * @author - Yorick <info@yorickblom.nl>
 */
class AdminSensor {

    public static function register() {
        add_menu_page(
            'Sensors',     // page title
            'Sensors',     // menu title
            'manage_sensors',   // capability
            'custom_sensors',     // menu slug
            array( '\ModulairDashboard\AdminSensor', 'render_overview' ), // callback function
            "dashicons-rest-api", //Icon URL
            42 //Position
        );

        add_submenu_page(
            "custom_sensors", // parent slug
            "Alle sensors", // page title
            "Alle sensors", // menu title
            "manage_sensors", //capability
            "custom_sensors", // menu slug (Same as main)
            array( '\ModulairDashboard\AdminSensor', 'render_overview' ), // callback function
        );

        add_submenu_page(
            "custom_sensors", // parent slug
            "Nieuwe sensor", // page title
            "Nieuwe sensor", // menu title
            "manage_sensors", //capability
            "new_sensors", // menu slug
            array( '\ModulairDashboard\AdminSensor', 'render_add' ), // callback function
        );

        if(isset($_REQUEST['page']) && $_REQUEST['page'] === 'edit_sensors')
            add_submenu_page(
                "custom_sensors", // parent slug
                "Bewerk sensor", // page title
                "Bewerk sensor", // menu title
                "manage_sensors", //capability
                "edit_sensors", // menu slug
                array( '\ModulairDashboard\AdminSensor', 'render_edit' ), // callback function
            );

        add_submenu_page(
            "custom_sensors", // parent slug
            "Verzoeken", // page title
            "Verzoeken", // menu title
            "manage_sensors", //capability
            "request_sensor", // menu slug
            array( '\ModulairDashboard\AdminSensor', 'render_request' ), // callback function
        );
    }

    public static function render_overview() {
        AdminSensor::process_delete_sensor();
        include DashboardPlugin::get_instance()->getDir() . "/pages/admin.sensors.all.php";
    }

    public static function render_add() {
        AdminSensor::process_new_sensor();
        include DashboardPlugin::get_instance()->getDir() . "/pages/admin.sensors.new.php";
    }

    public static function render_request() {
        include DashboardPlugin::get_instance()->getDir() . "/pages/admin.sensors.requests.php";
    }

    public static function render_edit() {
        AdminSensor::process_edit_sensor();
        include DashboardPlugin::get_instance()->getDir() . "/pages/admin.sensors.edit.php";
    }

    private static function process_delete_sensor() {
        $plugin = DashboardPlugin::get_instance();
        if(isset($_GET['action']) && $_GET['action'] === 'delete') {
            $id = (int) esc_html(esc_js($_GET['Id']));
            $plugin->sensor_table->delete_data(array("Id" => $id));
        }
    }

    private static function process_edit_sensor() {
        $plugin = DashboardPlugin::get_instance();
        if(isset($_POST['updatesensor']) || isset($_POST['testsensor']) ) {

            switch($_POST['sensor_type']) {
                case "RestApi":
                    if(strlen($_POST['rest_url']) < 1) {
                        $plugin->setMsg("Vul alstublieft de API endpoint in voor het opvragen van de data.");
                        add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                        break;
                    }

                    if($_POST['rest_method'] == "POST") {
                        if(strlen($_POST['rest_data']) < 1) {
                            $plugin->setMsg("Vul alstublieft POST data in voor de API.");
                            add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                            break;
                        }

                        if(!json_validate($_POST['rest_data'])) {
                            $plugin->setMsg("Vul alstublieft geldige JSON in als POST data.");
                            add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                            break;
                        }
                    }

                    $api = new RestApi($_POST['rest_url'], $_POST['rest_method'], $_POST['rest_data']);
                    $sensor = new Sensor($api);

                    if(isset($_POST['testsensor'])) {
                        
                        try {
                            $data = $api->connect();
                        } catch (RuntimeException $er) {
                            $plugin->setMsg("Connectie naar '".$_POST['rest_url']."' is mislukt (".$er->getMessage().")!");
                            add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                            break;
                        }

                        $plugin->setMsg("Connectie naar '".$_POST['rest_url']."' is successvol!");
                        add_action( 'sensor_notices', array( $plugin , 'success_notice' ) );
                        break;
                    }

                    if(isset($_POST['updatesensor'])) {
                        DashboardPlugin::get_instance()->sensor_table->update_data(array(
                            "Name" => $_POST['sensor_name'],
                            "Json" => $sensor->toJson(),
                            "created_by" => get_current_user_id(),
                            "created_on" => strtotime('now')
                        ), array("Id" => $_POST['sensor_id']));

                        $plugin->setMsg("Sensor {$_POST['sensor_name']} is successvol bijgewerkt!");
                        add_action( 'sensor_notices', array( $plugin , 'success_notice' ) );
                        unset($_POST);
                    } 

                    break;

                case "MySQL":
                    if(strlen($_POST['mysql_ip']) < 7 || strlen($_POST['mysql_port']) < 1) {
                        $plugin->setMsg("Vul alstublieft het IP-Adres en port van de database in.");
                        add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                        break;
                    }

                    if(strlen($_POST['mysql_user']) < 2) {
                        $plugin->setMsg("Vul alstublieft database credentials in.");
                        add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                        break;
                    }

                    if(strlen($_POST['mysql_dbname']) < 2 || strlen($_POST['mysql_table']) < 2) {
                        $plugin->setMsg("Vul alstublieft een database en tabel in voor de sensor.");
                        add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                        break;
                    }

                    $mysql = new MySQL($_POST['mysql_ip'], $_POST['mysql_port'], $_POST['mysql_dbname'], $_POST['mysql_user'], $_POST['mysql_password'], $_POST['mysql_table']);
                    $sensor = new Sensor($mysql);

                    try {
                        $mysql->connect();
                        $mysql->disconnect();
                    } catch (PDOException $er) {
                        $plugin->setMsg("Connectie met database is mislukt: {$er->errorInfo[2]}");
                        add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                        break;
                    }

                    if(isset($_POST['updatesensor'])) {
                        DashboardPlugin::get_instance()->sensor_table->update_data(array(
                            "Name" => $_POST['sensor_name'],
                            "Json" => $sensor->toJson(),
                            "created_by" => get_current_user_id(),
                            "created_on" => strtotime('now')
                        ), array("Id" => $_POST['sensor_id']));

                        $plugin->setMsg("Sensor {$_POST['sensor_name']} is successvol bijgewerkt!");
                        add_action( 'sensor_notices', array( $plugin , 'success_notice' ) );
                        unset($_POST);
                    } 

                    if(isset($_POST['testsensor'])) {
                        $plugin->setMsg("Connectie met {$_POST['mysql_ip']}:{$_POST['mysql_port']} is successvol!");
                        add_action( 'sensor_notices', array( $plugin , 'success_notice' ) );
                    }
                    
                    break;

                default:
                    $plugin->setMsg("Geselecteerde sensor type is niet geregistreerd, neem alsutblieft contact op met uw site administrator. (".$_POST['sensor_type'].")");
                    add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                    break;
            }

            
        }
    }

    private static function process_new_sensor() {
        $plugin = DashboardPlugin::get_instance();
        if(isset($_POST['createsensor']) || isset($_POST['testsensor']) ) {

            switch($_POST['sensor_type']) {
                case "RestApi":
                    if(strlen($_POST['rest_url']) < 1) {
                        $plugin->setMsg("Vul alstublieft de API endpoint in voor het opvragen van de data.");
                        add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                        break;
                    }

                    if($_POST['rest_method'] == "POST") {
                        if(strlen($_POST['rest_data']) < 1) {
                            $plugin->setMsg("Vul alstublieft POST data in voor de API.");
                            add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                            break;
                        }

                        if(!json_validate($_POST['rest_data'])) {
                            $plugin->setMsg("Vul alstublieft geldige JSON in als POST data.");
                            add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                            break;
                        }
                    }

                    $api = new RestApi($_POST['rest_url'], $_POST['rest_method'], $_POST['rest_data']);
                    $sensor = new Sensor($api);

                    if(isset($_POST['testsensor'])) {
                        
                        try {
                            $data = $api->connect();
                        } catch (RuntimeException $er) {
                            $plugin->setMsg("Connectie naar '".$_POST['rest_url']."' is mislukt (".$er->getMessage().")!");
                            add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                            break;
                        }

                        $plugin->setMsg("Connectie naar '".$_POST['rest_url']."' is successvol!");
                        add_action( 'sensor_notices', array( $plugin , 'success_notice' ) );
                        break;
                    }

                    if(isset($_POST['createsensor'])) {
                        DashboardPlugin::get_instance()->sensor_table->insert_data(array(
                            "Name" => $_POST['sensor_name'],
                            "Json" => $sensor->toJson()
                        ));

                        $plugin->setMsg("Sensor {$_POST['sensor_name']} is successvol geregisteerd!");
                        add_action( 'sensor_notices', array( $plugin , 'success_notice' ) );
                        unset($_POST);
                    } 

                    break;

                case "MySQL":
                    if(strlen($_POST['mysql_ip']) < 7 || strlen($_POST['mysql_port']) < 1) {
                        $plugin->setMsg("Vul alstublieft het IP-Adres en port van de database in.");
                        add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                        break;
                    }

                    if(strlen($_POST['mysql_user']) < 2) {
                        $plugin->setMsg("Vul alstublieft database credentials in.");
                        add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                        break;
                    }

                    if(strlen($_POST['mysql_dbname']) < 2 || strlen($_POST['mysql_table']) < 2) {
                        $plugin->setMsg("Vul alstublieft een database en tabel in voor de sensor.");
                        add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                        break;
                    }

                    $mysql = new MySQL($_POST['mysql_ip'], $_POST['mysql_port'], $_POST['mysql_dbname'], $_POST['mysql_user'], $_POST['mysql_password'], $_POST['mysql_table']);
                    $sensor = new Sensor($mysql);

                    try {
                        $mysql->connect();
                        $mysql->disconnect();
                    } catch (PDOException $er) {
                        $plugin->setMsg("Connectie met database is mislukt: {$er->errorInfo[2]}");
                        add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                        break;
                    }

                    if(isset($_POST['createsensor'])) {
                        DashboardPlugin::get_instance()->sensor_table->insert_data(array(
                            "Name" => $_POST['sensor_name'],
                            "Json" => $sensor->toJson()
                        ));

                        $plugin->setMsg("Sensor {$_POST['sensor_name']} is successvol geregisteerd!");
                        add_action( 'sensor_notices', array( $plugin , 'success_notice' ) );
                        unset($_POST);
                    } 

                    if(isset($_POST['testsensor'])) {
                        $plugin->setMsg("Connectie met {$_POST['mysql_ip']}:{$_POST['mysql_port']} is successvol!");
                        add_action( 'sensor_notices', array( $plugin , 'success_notice' ) );
                    }
                    
                    break;

                default:
                    $plugin->setMsg("Geselecteerde sensor type is niet geregistreerd, neem alsutblieft contact op met uw site administrator. (".$_POST['sensor_type'].")");
                    add_action( 'sensor_notices', array( $plugin , 'error_notice' ) );
                    break;
            }

            
        }
    }
}

?>