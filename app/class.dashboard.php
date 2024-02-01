<?php

namespace ModulairDashboard;

/**
 * Basic dashboard class to convert from post structure to our dashboard structure.
 * Used within endpoints for communication.
 *
 * @author Yorick <info@yorickblom.nl>
 */
class Dashboard {
    public $Id;
    public $Name;
    public $Description;
    public $Author;
    public $Modified;
    public $Columns;

    public $IsPublic = false;
    public $IsPinned = false;
    public $SharedUsers = array();
    public $Tags = array();

    /**
     * Constructor to initialize Dashboard object.
     *
     * @param int    $id      Dashboard ID.
     * @param string $name    Dashboard name.
     * @param string $desc    Dashboard description.
     * @param int    $author  Dashboard author/user ID.
     */
    public function __construct($id, $name = '', $desc = '', $author = -1) {
        $this->Id = $id;
        $this->Name = $name;
        $this->Description = $desc;
        $this->Author = $author;

        // Load propperties from Wordpress if Id is given
        if($id !== null && $name == '') {
            $this->populateFromWordPress();
        }
    }

    /**
     * Get a public available JSON structure, this way we do not expose unecesarry values.
     * We can also add required values such as all tags and all users available.
     */
    public function getPublicStructure() { 
        $user_array = array();
        foreach($this->SharedUsers as $user_id) {
            $user_data = get_userdata($user_id);
            array_push($user_array, array(
                'id'    => $user_id,
                'name'  => $user_data->display_name,
                'email' => $user_data->user_email,
            ));
        }

        $tags = get_categories(array(
            'parent'  => 0,
            'hide_empty' => 0,
            'order'    => 'ASC',
        ));

        $allowed_sensors = DashboardPlugin::get_instance()->sensor_table->select_data("`id` IN (SELECT `sensor_id` FROM wp_sensor_requests WHERE `post_id` = ".$this->Id." AND `approved_on` IS NOT NULL AND `trashed` = '0')");
        $not_allowed_sensors = DashboardPlugin::get_instance()->sensor_table->select_data("`id` NOT IN (SELECT `sensor_id` FROM wp_sensor_requests WHERE `post_id` = ".$this->Id." AND `approved_on` IS NOT NULL AND `trashed` = '0')");

        return array(
            'name'          => sanitize_text_field($this->Name),
            'description'   => sanitize_text_field($this->Description),
            'can_edit'      => $this->canUserEdit(get_current_user_id()),
            'tags'          => array_map(function($tag) { return array("id" => $tag->term_id, "value" => $tag->name); }, $this->Tags),

            'is_public'     => $this->IsPublic,
            'is_pinned'     => $this->IsPinned,
            'columns'       => $this->Columns,
            
            'shared_users'  => $user_array,
            'all_tags'      => array_map(function($tag) { return array("id" => $tag->term_id, "value" => $tag->name); }, $tags),

            'all_users'     => array_map(function($user) { return array("id" => $user->ID, "value" => $user->display_name); }, get_users()),

            'shared_sensors' => array_map(function($sensor) { return array("id" => $sensor->id, "name" => $sensor->name); }, $allowed_sensors),
            'all_sensors'   => array_map(function($sensor) { return array("id" => $sensor->id, "value" => $sensor->name); }, $not_allowed_sensors),
        );
    }

    /**
     * Get the WordPress-compatible structure for the dashboard.
     *
     * @return array WordPress-compatible structure.
     */
    public function getWordpressStructure() {
        return array(
            'ID'            => $this->Id,
            'post_title'    => sanitize_text_field($this->Name),
            'post_status'   => 'publish',
            'post_author'   => $this->Author,
            'post_category' => $this->Tags,
            'meta_input'    => array(
                "description"   => sanitize_text_field($this->Description),
                "shared_users"  => $this->SharedUsers,
                "is_public"     => $this->IsPublic,
                "is_pinned"     => $this->IsPinned,
                "columns"       => $this->Columns,
            )
        );
    }

    /**
     * Save the dashboard to WordPress.
     *
     * @return int|WP_Error Post ID on success, WP_Error on failure.
     */
    public function saveToWordpress() {
        $post = $this->getWordpressStructure();

        // If ID is not set, unset 'ID' key to insert a new post
        if ($this->Id == NULL) {
            unset($post['ID']);
            return wp_insert_post($post);
        } else {
            return wp_update_post($post);
        }
    }

    /**
     * Move dashboard into trash, this will NOT permanently remove it.
     * 
     * @return int|WP_Error Post ID on success, WP_Error on failure.
     */
    public function moveToTrash() {
        return wp_trash_post($this->Id);
    }

    /**
     * Populate class properties from WordPress based on the provided ID.
     */
    public function populateFromWordPress() {
        // Make sure ID is set
        if ($this->Id !== null) {
            $post = get_post($this->Id);

            if ($post instanceof \WP_Post) {
                // Populate class properties
                $this->Name = $post->post_title;
                $this->Author = (int)$post->post_author;
                $this->Modified = $post->post_modified_gmt;

                // Get tags
                $catagories = wp_get_post_categories($this->Id);
                foreach($catagories as $c){
                    array_push($this->Tags, get_category( $c ));
                }

                // Get additional metadata
                $this->Description = get_post_meta($this->Id, 'description', true);
                $this->SharedUsers = get_post_meta($this->Id, 'shared_users', true);

                // Handle bool metadata (Potentially empty value handler)
                $this->IsPublic = get_post_meta($this->Id, 'is_public', true);
                $this->IsPublic = $this->IsPublic === '' ? false : (bool)$this->IsPublic;

                $this->IsPinned = get_post_meta($this->Id, 'is_pinned', true);
                $this->IsPinned = $this->IsPinned === '' ? false : (bool)$this->IsPinned;

                $this->Columns  = get_post_meta($this->Id, 'columns', true);
                $this->Columns = $this->Columns === '' ? 4 : $this->Columns;
            }
        }
    }

    /**
     * Check if a user has access to the dashboard.
     *
     * @param int $userId User ID to check for access.
     * @return bool Returns true if the user is the author or is in the shared users, otherwise false.
     */
    public function userHasAccess($userId) {
        return $userId == $this->Author ? true : in_array($userId, (array)$this->SharedUsers);
    }

    /**
     * Check if user has the ability to edit the dashboard settings.
     * 
     * @param int $userId User ID to check for capability.
     * @return bool Returns true if the user is the author or has the capability 'edit_shared', otherwise false.
     */
    public function canUserEdit($userId) {
        return $userId == $this->Author || user_can($userId, 'edit_shared');
    }

    /**
     * Remove shared user from dashboard and update to database.
     * 
     * @param int $userId User ID to remove.
     */
    public function removeAccess($userId) {
       $key = array_search($userId, $this->SharedUsers); 
       if ($key !== false) {
            unset($this->SharedUsers[$key]);
        }
        $this->saveToWordpress();
    }

    /**
     * Add user id to shared users on dashboard and update database.
     * 
     * @param int $userId User ID to add.
     */
    public function addAccess($userId) {
        array_push($this->SharedUsers, $userId);
        $this->saveToWordpress();
    }

    /**
     * Determine if a sensor request for the sensor has been approved on this dashboard
     * It checks if the sensor has an non trashed approved entry in the database
     * 
     * @param int $sensorId Sensor ID to check.
     */
    public function hasSensor($sensorId) { //AND `approved_by` IS NOT NULL Also except requests
        return DashboardPlugin::get_instance()->sensor_request_table->data_exists("`post_id` = %s AND `sensor_id` = %s AND `trashed` = '0'", array($this->Id, $sensorId));
    }
    
    /**
     * Remove a sensor from the dashboard, and moving the request to the trash
     * 
     * @param int $sensorId Sensor ID to remove.
     */
    public function removeSensor($sensorId) {
        $query_data = array(
            "trashed" => 1,
            "approved_by" => NULL, 
            "approved_on" => NULL
        );
        $query_filter = array(
            "trashed" => 0,
            "sensor_id" => $sensorId,
            "post_id" => $this->Id
        );
        DashboardPlugin::get_instance()->sensor_request_table->update_data($query_data, $query_filter);
    }
 
     /**
      * Request a sensor to be added to the dashboard.
      * A request is required to be approved by someone with the capability to manage sensors
      * 
      * @param int $sensorId Sensor ID to request.
      */
    public function requestSensor($sensorId) {
        $query_data = array(
            "trashed" => 0,
            "approved_by" => NULL, 
            "approved_on" => NULL,
            "sensor_id" => $sensorId,
            "post_id" => $this->Id,
            "requested_by" => get_current_user_id(),
            "requested_on" => strtotime('now')
        );
        DashboardPlugin::get_instance()->sensor_request_table->insert_data($query_data);
    }

    /**
     * Approve a sensor request for the dashboard, this will use the current logged-in user as the approval user
     * 
     * @param int $sensorId Sensor ID to approve.
     */
    public function approveSensor($sensorId) {
        $query_data = array(
            "approved_by" => get_current_user_id(), 
            "approved_on" => strtotime('now')
        );
        $query_filter = array(
            "trashed" => 0,
            "sensor_id" => $sensorId,
            "post_id" => $this->Id
        );
        DashboardPlugin::get_instance()->sensor_request_table->update_data($query_data, $query_filter);
    }

    /**
     * Get all tags for dashboard converted into a string that is , seperated.
     * 
     * @return string , seperated value of all tags, N/A value if no tags found.
     */
    public function getTagString() {
        if(count($this->Tags) <= 0) return 'N/A';

        $data = array_map(function($tag) {
            return $tag->name;
        }, $this->Tags);

        return implode(', ', $data);
    }
}