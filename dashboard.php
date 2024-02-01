<?php
/**
 * Plugin Name: Dashboard
 * Description: Plugin voor bij het thema Modulair Dashboard, om support te leveren voor sensors, grafieken en block editor features.
 * Version:     1.0.1
 * Author:      Yorick
 * Author URI:  https://yorickblom.nl
 * Text Domain: dashboard
 *
 * Deze plugin is ontwikkelt voor het Modulaire Dashboard van Green Tech Lab. Deze plugin facilliteerd de verbinding met sensors & grafieken die deze data weergeven.
 *
 * @package   Dashboard
 * @version   1.0.1
 * @author    Yorick <info@yorickblom.nl>
 */

namespace ModulairDashboard;

class DashboardPlugin {

    // Private variables
    private $dir = "";
    private $uri = "";
    private $version = "1.0.1";
    
    public $sensor_table;
    public $graphs_table;
    public $sensor_request_table;

    private $connectionTypes = array();

    /**
     * Get instance for singleton class
     */
    public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->setup();
			$instance->includes();
            $instance->setupActions();
            $instance->register_custom_blocks();
		}

		return $instance;
	}

    // Getters
    public function getDir() { return $this->dir; }
    public function getUri() { return $this->uri; }
    public function getConnectionTypes() { return $this->connectionTypes; }

    /**
     * Directory variables setup
     */
    private function setup() {
        $this->dir = plugin_dir_path( __FILE__ );
        $this->uri = plugin_dir_url( __FILE__ );
    }

    /**
     * Include plugin files
     */
    private function includes() {
        ///Classes
        require_once $this->dir . "/app/class.dataset.php";
        require_once $this->dir . "/app/class.databasetable.php";
        require_once $this->dir . "/app/class.admintable.php";
        require_once $this->dir . "/app/class.query.php";
        require_once $this->dir . "/app/class.sensor.php"; 
        require_once $this->dir . "/app/class.widget.php";
        require_once $this->dir . "/app/class.mails.php";
        require_once $this->dir . "/app/class.dashboard.php";

        ///Admin pages
        require_once $this->dir . "/app/menus/class.adminsensors.php"; 
        require_once $this->dir . "/app/menus/class.admingraphs.php"; 
        require_once $this->dir . "/app/menus/class.admindashboards.php"; 
        require_once $this->dir . "/app/menus/class.admin.php"; 

        ///Sensor types
        require_once $this->dir . "/data/class.MySQL.php";
        require_once $this->dir . "/data/class.REST_Api.php";

        array_push($this->connectionTypes, "MySQL");
        array_push($this->connectionTypes, "RestApi");

        ///Tables
        require_once $this->dir . "/tables/table.dashboards.php";
        require_once $this->dir . "/tables/table.graphs.php";
        require_once $this->dir . "/tables/table.sensors.php";
        require_once $this->dir . "/tables/table.sensorsrequest.php";

        ///Api calls
        //Include all Dashboard API endpoints
        foreach (glob($this->dir . '/API/Dashboard/*.php') as $file) {
            require_once $file;
        }
        
        //Include all Widget API endpoints
        foreach (glob($this->dir . '/API/Widget/*.php') as $file) {
            require_once $file;
        }

        //Include all general API endpoints
        foreach (glob($this->dir . '/API/General/*.php') as $file) {
            require_once $file;
        }

        ///Shortcodes
        require_once $this->dir . '/shortcodes/shortcode.settings-form.php';
        require_once $this->dir . '/shortcodes/shortcode.do-action.php';
        require_once $this->dir . '/shortcodes/shortcode.load-dashboards.php';
    
    }

    /**
     * Basic wordpress action setup
     * Triggered on each load
     */
    private function setupActions() {
        //Hook activation & deactivation
        register_activation_hook( __FILE__, array( $this, 'activation' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
        register_uninstall_hook( __FILE__, array( "\ModulairDashboard\DashboardPlugin", 'uninstall' ) );

        //Hook custom admin pages
        add_action( 'admin_menu', function () { \db_Navigator::add_admin_menu_separator(40); });
        add_action( 'admin_menu', array( 'ModulairDashboard\AdminSensor', 'register' ) ); //Sensor admin page
        add_action( 'admin_menu', array( 'ModulairDashboard\AdminGraphs', 'register' ) ); //Grafieken admin page
        add_action( 'admin_menu', array( 'ModulairDashboard\AdminWordpress', 'register' ) ); //Grafieken admin page
        add_action( 'admin_menu', array( 'ModulairDashboard\AdminDashboards', 'register' ) ); //Grafieken admin page

        //Hook filters
        add_filter( 'wp_new_user_notification_email', 'dashboard_new_user_email_notification', 10, 3 );
        add_filter( 'retrieve_password_notification_email', 'dashboard_reset_password_notification', 10, 4);
        add_filter( 'email_change_email', 'dashboard_update_user_email', 10, 3);

        //Hook styles 
        wp_enqueue_script( 'chosen-jquery', 'https://cdn.jsdelivr.net/gh/harvesthq/chosen@gh-pages/chosen.jquery.min.js', array( 'jquery' ), '1.8.7', true ); //Custom selector jquery
        
        //Data tables basic
        wp_enqueue_script( 'datatables-core-jquery', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array( 'jquery' ), '1.13.6', true ); //Custom datatables jquery
        wp_enqueue_style( 'datatables-core-css', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', array(), '1.13.6' ); //Custom datatables css

        //Data tables responsive
        wp_enqueue_script( 'datatables-responsive-jquery', 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js', array( 'jquery' ), '2.5.0', true ); //Custom datatables jquery
        wp_enqueue_style( 'datatables-responsive-css', 'https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css', array(), '2.5.0' ); //Custom datatables css
        
        //Data tables custom iniator for block
        wp_enqueue_script( 'datatables-iniator', $this->uri . "/scripts/data-table-iniator.js", array( 'jquery', 'datatables-core-jquery', 'datatables-responsive-jquery' ), filemtime($this->dir . "/scripts/data-table-iniator.js") ); //Custom datatables jquery

        //Encryption initialization
        $this->encr_key = openssl_digest(php_uname('m')."_".php_uname('s') . "_" . substr(get_option( 'blogname' ), 0, 5), 'MD5', TRUE);

        //Register databasetables
        $this->sensor_table = new DatabaseTable("sensors");
        $this->graphs_table = new DatabaseTable("graphs");
        $this->sensor_request_table = new DatabaseTable("sensor_requests");

        //Register shortcodes
        add_shortcode('form_user_settings', 'dashboard_shortcode_form_user_settings');
        add_shortcode('do_action', 'dashboard_shortcode_do_action');
        add_shortcode('load_dashboards', 'dashboard_shortcode_load_dashboards');
        
        //Register form submission
        add_action('admin_post_dashboard_form_submit_user_settings', 'dashboard_form_submit_user_settings', 10);
        add_action('admin_post_dashboard_form_submit_user_password', 'dashboard_form_submit_user_password', 10);
    }

    /**
     * Get the version string as an integer
     */
    public function getVersion() {
        return preg_replace('/[^0-9.]/', '', $this->version);
    }

    /**
     * Register custom blocks from plugin, so they can be used within block editor
     */
    private function register_custom_blocks() {
        $blocks_dir = plugin_dir_path(__FILE__) . "blocks";
    
        foreach(glob($blocks_dir."/*", GLOB_ONLYDIR) as $block) {
            $data = array();
            
            if(file_exists($block . '/index.php')) {
                $data = include $block . '/index.php';
                
            }
    
            register_block_type($block, $data);
            
        };
    }

    /**
     * Function that is triggered on activation of plugin
     */
    public function activation() {
        //Register custom capabilities
        $role = get_role( 'administrator' ); // Get the administrator role.
        if ( ! empty( $role ) ) { // If the administrator role exists, add required capabilities for the plugin.
            $role->add_cap( 'manage_sensors' ); // Edit sensors role
            $role->add_cap( 'manage_graphs' ); // Edit graphs role
            $role->add_cap( 'manage_dashboards' ); // Edit dashboards role
            $role->add_cap( 'edit_shared' ); // Allow to modify shared dashboards
        }

        $this->setup_databases();
    }

    /**
     * Function that is triggered on deactivation of plugin
     */
    public function deactivation() {
        //TODO: Remove custom caps
        //TODO: Cleanup metadata
    }

    /**
     * Function that is triggered on uninstallation of plugin
     */
    public static function uninstall() {
        global $wpdb;

        //Drop database tables
        $wpdb->query("DROP TABLE IF EXISTS `{$this->sensor_table->get_table()}`;");
        $wpdb->query("DROP TABLE IF EXISTS `{$this->graphs_table->get_table()}`;");
        $wpdb->query("DROP TABLE IF EXISTS `{$this->sensor_request_table->get_table()}`;");
    }

    // Encryption variables
    private $ciphering = "BF-CBC";
    private $encryption_iv = '83674849';
    private $encr_options = 0;
    private $encr_key;

    /**
     * SSL Encryption method
     */
    public function encrypt($string) {
        return openssl_encrypt($string, $this->ciphering, $this->encr_key, $this->encr_options, $this->encryption_iv);
    }

    /**
     * SSL Decryption method
     */
    public function decrypt($string) {
        return openssl_decrypt($string, $this->ciphering, $this->encr_key, $this->encr_options, $this->encryption_iv);
    }

    /**
     * Sensor database creation
     */
    private function setup_databases() {
        global $wpdb;

        require_once ABSPATH . '/wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->sensor_table->get_table()}'") !== $this->sensor_table->get_table()) {
            $sql = "CREATE TABLE {$this->sensor_table->get_table()} (
                `id` INT(10) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(60) NOT NULL COLLATE 'utf8mb4_unicode_520_ci',
                `json` JSON NOT NULL,
                `created_by` INT(10) NOT NULL,
                `created_on` VARCHAR(16) NOT NULL COLLATE 'utf8mb4_unicode_520_ci',
                `trashed` ENUM('0','1') NOT NULL DEFAULT '0' COLLATE 'utf8mb4_unicode_520_ci',
                `metadata` JSON NOT NULL,
                PRIMARY KEY (`Id`),
                UNIQUE INDEX `Id` (`Id`)
                ) $charset_collate;";
            dbDelta($sql);
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->graphs_table->get_table()}'") !== $this->graphs_table->get_table()) {
            $sql = "CREATE TABLE {$this->graphs_table->get_table()} (
                	`id` INT(10) NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(60) NOT NULL COLLATE 'utf8mb4_unicode_520_ci',
                    `js` TEXT NOT NULL COLLATE 'utf8mb4_unicode_520_ci',
                    `css` TEXT NOT NULL COLLATE 'utf8mb4_unicode_520_ci',
                    `script` TEXT NOT NULL COLLATE 'utf8mb4_unicode_520_ci',
                    `filter` ENUM('XY','X','Y') NOT NULL DEFAULT 'XY' COLLATE 'utf8mb4_unicode_520_ci',
                    `trashed` ENUM('0','1') NOT NULL DEFAULT '0' COLLATE 'utf8mb4_unicode_520_ci',
                    PRIMARY KEY (`id`),
                    UNIQUE INDEX `id` (`id`)
                ) $charset_collate;";
            dbDelta($sql);
        }

        if($wpdb->get_var("SHOW TABLES LIKE '{$this->sensor_request_table->get_table()}'") !== $this->sensor_request_table->get_table()) {
            $sql = "CREATE TABLE {$this->sensor_request_table->get_table()} (
                `id` INT(10) NOT NULL AUTO_INCREMENT,
                `sensor_id` INT(10) NOT NULL,
                `post_id` INT(10) NOT NULL,
                `requested_by` INT(10) NOT NULL,
                `requested_on` VARCHAR(16) NOT NULL COLLATE 'utf8_general_ci',
                `approved_by` INT(10) NULL DEFAULT NULL,
                `approved_on` VARCHAR(16) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                `trashed` ENUM('0','1') NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
                PRIMARY KEY (`id`),
                UNIQUE INDEX `id` (`id`)
                ) $charset_collate;";
            dbDelta($sql);
            
        }
    }

    /**
     * Error message handeling for Admin Dashboard
     */
    private $msg;
    public function setMsg($msg) { $this->msg = $msg; }
    public function error_notice() {
		// Output notice.
		printf(
			'<div class="notice notice-error is-dismissible" style="margin-left: 0px;"><p><strong>%s</strong></p></div>',
			esc_html( $this->msg )
		);

        $this->msg = "";
	}
    public function success_notice() {
		// Output notice.
		printf(
			'<div class="notice notice-success is-dismissible" style="margin-left: 0px;"><p><strong>%s</strong></p></div>',
			esc_html( $this->msg )
		);

        $this->msg = "";
	}

    /**
     * Front-end notices
     */
    public function notification($type, $msg) {
		// Output notice.
		printf(
			'<div class="notice notice-%s is-dismissible" style="margin-left: 0px;"><p>%s</p><button type="button" class="notice-dismiss" onclick="this.closest(\'.notice\').remove()"><i class="fa-solid fa-circle-xmark"></i></button></div>',
            esc_html( $type ),
			esc_html( $msg )
		);
	}

    /**
     * Get sensor by id
     */
    public function getSensorById($id) {
        $data = $this->sensor_table->select_data("`id` = '$id'");

        if(count($data) === 1) $data = $data[0];
        else return null;

        $sensor = Sensor::fromJson($data->json);
        $sensor->setName($data->name);
        $sensor->setId($data->id);

        if(!$sensor->hasMetadata("type")) {
            $sensor->setMetadata("type", $sensor->getType());
        }

        return $sensor;
    }

    /**
     * Get graph by id
     */
    public function getGraphById($id) {
        $data = $this->graphs_table->select_data("`id` = '$id'");
        return count($data) === 1 ? $data[0] : null;
    }

    /**
     * Get all dashboards
     */
    public function getDashboards($filter = array()) {
        $posts = get_posts(array_merge($filter, array(
            'orderby'           =>  'post_date',
            'order'             =>  'ASC',
            'posts_per_page'    => -1
            
        )));
        return $posts;
    }

    /**
     * Get categories for dashboard
     */
    public function getCategoriesByDashboardId($id) {
        $catagories = wp_get_post_categories($id);
        $cats = array();

        foreach($catagories as $c){
            array_push($cats, get_category( $c )->name);
        }

        return $cats;
    }

    /**
     * Get client IP-Address
     */
    function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            // IP from shared internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // IP passed from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            // Direct IP address
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}

//Plugin hook
$dashboard = DashboardPlugin::get_instance();