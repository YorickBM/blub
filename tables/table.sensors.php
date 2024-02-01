<?php

namespace ModulairDashboard;

class SensorTable extends AdminTable
{
	public function __construct() {
		parent::__construct();
	}
	
	public function get_columns() {
		return array(
			'cb'		=> '<input type="checkbox" />',
			'id' 		=> __('Id'),
			'name' 		=> __('Naam'),
			'type' 		=> __('Type')
		);;
	}
	
	public function get_sortable_columns() {
		return array(
			'id'		=> array('id', false),
			'name'		=> array('name', true),
			'type'		=> array('type', true)
		);
	}
	
	protected function column_name($item) {
		$page = wp_unslash($_REQUEST['page']);

		$actions['edit'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=edit_sensors&id='.$item['id'].'')),
			__('Edit')
		);
		$actions['delete'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=custom_sensors&action=delete&id='.$item['id'].'')),
			__('Delete')
		);
		
		return sprintf('%1$s %2$s',
			$item['name'],
			$this->row_actions($actions),
		);
	}
	
	protected function get_bulk_actions() {
		$actions = array(
			'delete' => __('Delete'),
		);		
		return $actions;
	}

	protected function process_row_actions() {
		
	}
	
	function prepare_items() {
		$data = array();
        $sensors = DashboardPlugin::get_instance()->sensor_table->select_data();

        foreach($sensors as $sensor) {
            $x = Sensor::fromJson($sensor->json);
            $x->setName($sensor->name);
            $x->setId($sensor->id);
            
            array_push($data, array(
				"Id" => $x->getId(),
                "id" => $x->getId(),
                "name" => $x->getName(),
                "type" => $x->getType(),
            ));
        }
		
		$this->set_pagination($data);
	}	
}
?>