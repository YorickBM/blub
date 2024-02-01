<?php

namespace ModulairDashboard;

/**
 * Basic sensor class to act between front-end and back-end dataset
 * @author - Yorick <info@yorickblom.nl>
 */
class Sensor {

    private $dataset;
    private $name;
    private $id;
    public $metadata;

    public function __construct($dataset) {
        $this->dataset = $dataset;
        $this->metadata = new \stdClass();
    }

    //Convert sensor and underlying dataset into JSON Object
    //This object can be used to save the object
    function toJson() {
        return json_encode(array(
            "dataset" => (string) $this->dataset,
            "data" => $this->dataset->getObject()
        ), 2);
    }

    //Load JSON for underlying dataset
    function loadJson($data) {
        $this->dataset->fromJson($data);
    }

    //Get headers from underlying dataset
    function getHeaders() {
        return $this->dataset->getHeaders();
    }

    //Get data from underlying dataset
    function getData() {
        $data = new \stdClass();
        $this->syncMetaData();

        if($this->hasMetadata("cache", false) && $this->hasMetadata("synced_on", false)) {
            if((strtotime('now') - (int)$this->getMetadata("synced_on", false)) > $this->dataset->getDelay()) { //Check if atleast 60 seconds passed
                $data = $this->dataset->getData();

                $this->setMetadata("synced_on", strtotime('now'), false); //Update sync time
                $this->setMetadata("cache", $data, false); //Update cache
            } else {
                return $this->getMetadata("cache", false); //No need to update
            }
         } else {
            $data = $this->dataset->getData();
        
            $this->setMetadata("synced_on", strtotime('now'), false); //Update sync time
            $this->setMetadata("cache", $data, false); //Update cache
        }

        $this->saveToDatabase(); //Single sync instead of multiple

        return $data; //$data;
    }

    //Build sensor from JSON Object 
    //JSON Object can be collected by the toJson() function
    static function fromJson($json) {
        $data = (object) json_decode($json);
        $dataset = new $data->dataset();
        
        $sensor = new Sensor($dataset);
        $sensor->loadJson($data->data);

        return $sensor;
    }

    //Getters & Setters
    function setName($name) { $this->name = $name; }
    function getName() { return $this->name; }

    function setId($id) { $this->id = $id; }
    function getId() { return $this->id; }

    function getDataset() { return $this->dataset; }
    function getType() { return call_user_func(array($this->dataset, 'toString')); }

    function getMetadata($shouldSync = true) { 
        if($shouldSync) $this->syncMetaData();
        return $this->metadata; 
    }
    function setMetadata($key, $value, $saveToDB = true) { 
        $this->metadata->{$key} = $value; 
        if($saveToDB) $this->saveToDatabase();
    }
    function unsetMetadata($key) { 
        unset($this->metadata->{$key}); 
        $this->saveToDatabase();
    }
    function hasMetadata($key, $shouldSync = true) { 
        if($shouldSync) $this->syncMetaData();
        return property_exists($this->metadata, $key); 
    }

    function syncMetaData() {
        $data = DashboardPlugin::get_instance()->sensor_table->select_data("`id` = ".$this->id." AND `trashed` = '0'", "metadata");
        foreach(json_decode($data[0]->metadata) as $key => $value) {
            if($key != 'cache') $this->metadata->{$key} = $value;
            else  $this->metadata->{$key} = json_decode(DashboardPlugin::get_instance()->decrypt($value));
        }
    }
    function saveToDatabase() {
        $savedata = $this->metadata;

        if(property_exists($savedata, "cache")) {
            $savedata->cache = DashboardPlugin::get_instance()->encrypt(json_encode($savedata->cache));
        }

        DashboardPlugin::get_instance()->sensor_table->update_data(array(
            "metadata" => json_encode($savedata),
        ), array(
            "id" => $this->id,
        ));
    }
}