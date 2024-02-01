<?php

namespace ModulairDashboard;

class DatabaseTable {
    
    private $table_name;
    
    public function __construct($table) {
        global $wpdb;
        $this->table_name = $wpdb->prefix . $table;
    }

    public function get_table() {
        return $this->table_name;
    }
    
    public function insert_data($data) {
        global $wpdb;
        $wpdb->insert($this->table_name, $data);
        echo $wpdb->print_error();
        return $wpdb->insert_id;
    }
    
    public function update_data($data, $where) {
        global $wpdb;
        $wpdb->update($this->table_name, $data, $where);
    }
    
    public function delete_data($where) {
        global $wpdb;
        $wpdb->delete($this->table_name, $where);
    }
    
    public function delete_data_filter($column, $operation, $value) {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$this->table_name} WHERE `{$column}` {$operation} %s", $value);
        $wpdb->query($sql);
    }

    public function row_count($where = '') {
        global $wpdb;
        $sql = "SELECT COUNT(*) AS `rows` FROM {$this->table_name}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        $result = $wpdb->get_results($sql);

        if(empty($result)) return 0;
        return $result[0]->rows;
    }
    
    public function select_data($where = '', $select = '*') {
        global $wpdb;
        $sql = "SELECT {$select} FROM {$this->table_name}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        return $wpdb->get_results($sql);
    }

    public function select_query($query, $select = '*') {
        global $wpdb;
        $sql = "SELECT {$select} FROM {$this->table_name} {$query}";
        return $wpdb->get_results($sql);
    }
    
    public function data_exists($where, $value) {
        global $wpdb;
    
        $query = $wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE {$where}", $value);
        $result = $wpdb->get_var($query);
        return $result > 0;
    }
}
