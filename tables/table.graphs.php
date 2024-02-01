<?php

namespace ModulairDashboard;

class GraphTable extends AdminTable
{
    public function __construct() {
		parent::__construct("grafiek", "grafieken");
	}
	
	public function get_columns() {
		return array(
			'cb'		=> '<input type="checkbox" />',
			'id' 		=> _x('Id', 'Column label', 'yorickblom'),
			'name' 		=> _x('Naam', 'Column label', 'yorickblom'),
			'css'		=> _x('CSS', 'Column label', 'yorickblom'),
			'js' 		=> _x('Javascript', 'Column label', 'yorickblom')
		);;
	}
	
	public function get_sortable_columns() {
		return array(
			'id'		=> array('id', false),
			'name'		=> array('name', true),
			'css'		=> array('css', true),
			'js'		=> array('js', true)
		);
	}
	
	protected function column_name($item) {
		$page = wp_unslash($_REQUEST['page']);
		$graph = DashboardPlugin::get_instance()->graphs_table->select_data("`id` = '{$item['id']}'", "name")[0];

		$trash = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=custom_graphs&action=trash&id='.$item['id'].'')),
			__('Remove')
		);
		$edit = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=edit_graphs&id='.$item['id'].'')),
			__('Edit')
		);

		$restore = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=custom_graphs&action=restore&id='.$item['id'].'')),
			__('Restore')
		);
		$deleteperm = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=custom_graphs&action=deleteperm&id='.$item['id'].'')),
			__('Delete Permanently')
		);

		$actions = array();
		switch(strtolower($item['status'])) {
			case "trash":
				$actions['restore'] = $restore;
				$actions['trash'] = $deleteperm;
				break;

			default:
				$actions['edit'] = $edit;
				$actions['trash'] = $trash;
				break;
		}

		return sprintf('%1$s %2$s',
			$graph->name,
			$this->row_actions($actions),
		);
	}

	protected function column_css($item) {
		return count($values = explode(';', $item['css'])) == 1 ? $values[0] : $values[0] . '... ' . __('en') . ' ' . (count($values) - 1) . ' ' . __('meer');
	}
	protected function column_js($item) {
		return count($values = explode(';', $item['js'])) == 1 ? $values[0] : $values[0] . '... ' . __('en') . ' ' . (count($values) - 1) . ' ' . __('meer');
	}

	protected function get_views() {
		$status = ! empty($_REQUEST['status']) ? wp_unslash( $_REQUEST['status'] ) : NULL;

		$stats_links  = array();
		$url = 'admin.php?page=custom_graphs';

		$stats_links['all'] = array(
			'url'     => $url,
			'label'   => sprintf('%1$s <span class="count">(%2$s)</span>',
				__( 'All' ),
				number_format_i18n(DashboardPlugin::get_instance()->graphs_table->row_count('`trashed` = \'0\''))
			),
			'current' => empty( $status ),
		);

		$trash_count = DashboardPlugin::get_instance()->graphs_table->row_count('`trashed` = \'1\'');
		if($trash_count > 0) $stats_links['trash'] = array(
			'url'     => esc_url( add_query_arg( 'status', 'trash', $url ) ),
			'label'   => sprintf('%1$s <span class="count">(%2$s)</span>',
				__( 'Prullenbak' ),
				number_format_i18n($trash_count)
			),
			'current' => $status == 'trash',
		);

		return $this->get_views_links( $stats_links );
	}
	
	protected function get_bulk_actions() {
		$actions = array(
			'btrash' => __('Remove'),
		);		
		return $actions;
	}

	protected function process_bulk_actions() {

	}

	protected function process_row_actions() {
		if(!empty($_REQUEST['action']) && !empty($_REQUEST['id'])) {
			switch(strtolower(wp_unslash($_REQUEST['action']))) {
				case "trash":
					if(DashboardPlugin::get_instance()->graphs_table->row_count("`id` = '".wp_unslash($_REQUEST['id'])."' AND `trashed` = '0'") == 1) {
						$query_data = array(
							"trashed" => 1
						);
						$query_filter = array(
							"id" => wp_unslash($_REQUEST['id'])
						);
						DashboardPlugin::get_instance()->graphs_table->update_data($query_data, $query_filter);
					}
					break;
				case "restore":
					if(DashboardPlugin::get_instance()->graphs_table->row_count("`id` = '".wp_unslash($_REQUEST['id'])."' AND `trashed` = '1'") == 1) {
						$query_data = array(
							"trashed" => 0
						);
						$query_filter = array(
							"id" => wp_unslash($_REQUEST['id'])
						);
						DashboardPlugin::get_instance()->graphs_table->update_data($query_data, $query_filter);
					}
					break;
				case "deleteperm":
					if(DashboardPlugin::get_instance()->graphs_table->row_count("`id` = '".wp_unslash($_REQUEST['id'])."' AND `trashed` = '1'") == 1) {
						$query_filter = array(
							"id" => wp_unslash($_REQUEST['id'])
						);
						DashboardPlugin::get_instance()->graphs_table->delete_data($query_filter);
					}
					break;

				default:
					//Silence is golden
					break;
			}
		}
	}
	
	function prepare_items() {		
		$data = array();
		$filter = '';

		//Process view filter if we got one
		if(!empty( $_REQUEST['status'] )) { 
			switch(wp_unslash( $_REQUEST['status'] )) {
				case "trash":
					$filter = '`trashed` = \'1\'';
					break;

				default:
					//Silence is golden
					break;
			}
		} else {
			$filter = '`trashed` = \'0\'';
		}
		//Load items
        $requests = DashboardPlugin::get_instance()->graphs_table->select_data($filter);
        foreach($requests as $request) {
            array_push($data, array(
                "id" => $request->id,
                "name" => $request->name,
				"css" => $request->css,
				"js" => $request->js,
				"status" => $request->trashed == 1 ? "trash" : "normal"
            ));
        }
		
		$this->set_pagination($data);
	}	
}