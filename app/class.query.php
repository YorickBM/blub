<?php

namespace ModulairDashboard;

/**
 * Query using PDO construct to run select query
 * @author - Yorick <info@yorickblom.nl>
 */
class SelectQuery {

    //Private variables
    private $dbh;
    private $query = "SELECT %KEY% FROM `%TABLE%` %FILTER% %LIMIT%;";
    private $attributes = array(
        "KEY" => "",
        "TABLE" => "",
        "FILTER" => "",
        "LIMIT" => ""
    );

    /**
     * Constructor
     */
    public function __construct($dbh, $table, $key) {
        $this->dbh = $dbh;

        $this->attributes['TABLE'] = $table;
        $this->attributes['KEY'] = $key;
    }

    /**
     * Set limit on query
     */
    public function limit($min, $max = NULL) {
        if($max == NULL) $this->attributes['LIMIT'] = "LIMIT $min";
        else $this->attributes['LIMIT'] = "LIMIT $min,$max";

        return $this;
    }

    /**
     * Finalize query and run using PDO
     * Returns fetchALL
     */
    public function execute() {
        $result = $this->query;
        foreach($this->attributes as $key => $value) {
            $result = str_replace("%$key%", $value, $result);
        }

        $stmt = $this->dbh->prepare($result);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}