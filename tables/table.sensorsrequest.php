<?php

namespace ModulairDashboard;

class SensorRequestTable extends AdminTable
{
	public function __construct() {
		parent::__construct("verzoek", "verzoeken");
	}
	
	public function get_columns() {
		return array(
			'cb'		=> '<input type="checkbox" />',
			'id' 		=> _x('Id', 'Column label', 'yorickblom'),
			'by' 		=> _x('Bij', 'Column label', 'yorickblom'),
			'name'		=> _x('Naam', 'Column label', 'yorickblom'),
			'dashboard' => _x('Dashboard', 'Column label', 'yorickblom')
		);;
	}
	
	public function get_sortable_columns() {
		return array(
			'id'		=> array('id', false),
			'by'		=> array('by', true),
			'name'		=> array('name', true),
			'dashboard'	=> array('dashboard', true)
		);
	}
	
	protected function column_name($item) {
		$page = wp_unslash($_REQUEST['page']);
		$sensor = DashboardPlugin::get_instance()->sensor_table->select_data("`Id` = '{$item['name']}'", "Name")[0];

		$approve = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=request_sensor&action=approve&id='.$item['id'].'')),
			__('Goedkeuren')
		);
		$pending = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=request_sensor&action=revoke&id='.$item['id'].'')),
			__('Revoke')
		);
		$trash = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=request_sensor&action=trash&id='.$item['id'].'')),
			__('Remove')
		);

		$restore = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=request_sensor&action=restore&id='.$item['id'].'')),
			__('Restore')
		);
		$deleteperm = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=request_sensor&action=deleteperm&id='.$item['id'].'')),
			__('Delete Permanently')
		);

		$actions = array();
		switch(strtolower($item['status'])) {
			case "approved":
				$actions['pending'] = $pending;
				$actions['trash'] = $trash;
				break;
			case "pending":
				$actions['approval'] = $approve;
				$actions['trash'] = $trash;
				break;
			case "trash":
				$actions['restore'] = $restore;
				$actions['trash'] = $deleteperm;
				break;

			default:
				//Silence is golden
				break;
		}
				
		return sprintf('%1$s %2$s',
			$sensor->Name,
			$this->row_actions($actions),
		);
	}

	protected function column_by($item) {
		$requester = get_user_by("id", (int) $item['by']);

		return sprintf('%1$s <div class="row-actions visible"><span class="email"><a href="%3$s">%2$s</a></span></div>',
			$requester->display_name,
			$requester->user_email,
			esc_url(admin_url("profile.php?id=".$requester->ID.""))
		);
	}

	protected function column_dashboard($item) {
		return sprintf('<a href="%1$s">%2$s</a>',
			get_permalink($item['dashboard']),
			get_the_title($item['dashboard'])
		);
	}

	protected function get_views() {
		$status = ! empty($_REQUEST['status']) ? wp_unslash( $_REQUEST['status'] ) : NULL;

		$stats_links  = array();
		$url = 'admin.php?page=request_sensor';

		$stats_links['all'] = array(
			'url'     => $url,
			'label'   => sprintf('%1$s <span class="count">(%2$s)</span>',
				__( 'All' ),
				number_format_i18n(DashboardPlugin::get_instance()->sensor_request_table->row_count())
			),
			'current' => empty( $status ),
		);

		$pending_count = DashboardPlugin::get_instance()->sensor_request_table->row_count('`approved_by` IS NULL AND `trashed` = \'0\'');
		if($pending_count > 0) $stats_links['pending'] = array(
			'url'     => esc_url( add_query_arg( 'status', 'pending', $url ) ),
			'label'   => sprintf('%1$s <span class="count">(%2$s)</span>',
				__( 'Pending' ),
				number_format_i18n($pending_count)
			),
			'current' => $status == 'pending',
		);

		$approved_count = DashboardPlugin::get_instance()->sensor_request_table->row_count('`approved_by` IS NOT NULL AND `trashed` = \'0\'');
		if($approved_count > 0) $stats_links['approved'] = array(
			'url'     => esc_url( add_query_arg( 'status', 'approved', $url ) ),
			'label'   => sprintf('%1$s <span class="count">(%2$s)</span>',
				__( 'Approved' ),
				number_format_i18n($approved_count)
			),
			'current' => $status == 'approved',
		);

		$trash_count = DashboardPlugin::get_instance()->sensor_request_table->row_count('`trashed` = \'1\'');
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
			'bapprove' => __('Goedkeuren'),
			'btrash' => __('Remove'),
		);		
		return $actions;
	}

	protected function process_bulk_actions() {

	}

	protected function process_row_actions() {
		if(!empty($_REQUEST['action']) && !empty($_REQUEST['id'])) {
			switch(strtolower(wp_unslash($_REQUEST['action']))) {
				case "approve":
						if(DashboardPlugin::get_instance()->sensor_request_table->row_count("`id` = '".wp_unslash($_REQUEST['id'])."' AND `approved_by` IS NULL") == 1) {
							$query_data = array(
								"approved_by" => get_current_user_id(), 
								"approved_on" => strtotime('now')
							);
							$query_filter = array(
								"id" => wp_unslash($_REQUEST['id'])
							);
							DashboardPlugin::get_instance()->sensor_request_table->update_data($query_data, $query_filter);
						}
					break;
				case "trash":
					if(DashboardPlugin::get_instance()->sensor_request_table->row_count("`id` = '".wp_unslash($_REQUEST['id'])."' AND `trashed` = '0'") == 1) {
						$query_data = array(
							"trashed" => 1,
							"approved_by" => NULL, //Always revoke on deletion
							"approved_on" => NULL
						);
						$query_filter = array(
							"id" => wp_unslash($_REQUEST['id'])
						);
						DashboardPlugin::get_instance()->sensor_request_table->update_data($query_data, $query_filter);
					}
					break;
				case "revoke":
					if(DashboardPlugin::get_instance()->sensor_request_table->row_count("`id` = '".wp_unslash($_REQUEST['id'])."' AND `approved_by` IS NOT NULL") == 1) {
						$query_data = array(
							"approved_by" => NULL, 
							"approved_on" => NULL
						);
						$query_filter = array(
							"id" => wp_unslash($_REQUEST['id'])
						);
						DashboardPlugin::get_instance()->sensor_request_table->update_data($query_data, $query_filter);
					}
					break;
				case "restore":
					if(DashboardPlugin::get_instance()->sensor_request_table->row_count("`id` = '".wp_unslash($_REQUEST['id'])."' AND `trashed` = '1'") == 1) {
						$query_data = array(
							"trashed" => 0
						);
						$query_filter = array(
							"id" => wp_unslash($_REQUEST['id'])
						);
						DashboardPlugin::get_instance()->sensor_request_table->update_data($query_data, $query_filter);
					}
					break;
				case "deleteperm":
					if(DashboardPlugin::get_instance()->sensor_request_table->row_count("`id` = '".wp_unslash($_REQUEST['id'])."' AND `trashed` = '1'") == 1) {
						$query_filter = array(
							"id" => wp_unslash($_REQUEST['id'])
						);
						DashboardPlugin::get_instance()->sensor_request_table->delete_data($query_filter);
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
				case "approved":
					$filter = '`approved_by` IS NOT NULL AND `trashed` = \'0\'';
					break;
				case "pending":
					$filter = '`approved_by` IS NULL AND `trashed` = \'0\'';
					break;
				case "trash":
					$filter = '`trashed` = \'1\'';
					break;

				default:
					//Silence is golden
					break;
			}
		}

		//Load items
        $requests = DashboardPlugin::get_instance()->sensor_request_table->select_data($filter);
        foreach($requests as $request) {
            array_push($data, array(
                "id" => $request->id,
                "name" => $request->sensor_id,
				"by" => $request->requested_by,
				"dashboard" => $request->post_id,
				"status" => (($request->trashed === '1') ? 'trash' : (($request->approved_by === NULL) ? 'pending' : 'approved'))
            ));
        }
		
		$this->set_pagination($data);
	}	
}
?>