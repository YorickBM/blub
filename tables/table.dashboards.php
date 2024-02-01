<?php

namespace ModulairDashboard;

class DashboardTable extends AdminTable
{
    public function __construct() {
		parent::__construct("grafiek", "grafieken");
	}
	
	public function get_columns() {
		return array(
			'cb'		    => '<input type="checkbox" />',
			'title' 		=> __('Title'),
			'categories' 	=> __('Categories'),
			'author'		=> __('Author'),
			'last_modified' 	=> __('Last modified')
		);;
	}
	
	public function get_sortable_columns() {
		return array(
			'title'		    => array('title', false),
			'categories'	=> array('categories', true),
			'author'		=> array('author', true),
			'last_modified'		=> array('last_modified', true)
		);
	}
	
	protected function column_title($item) {
		$page = wp_unslash($_REQUEST['page']);

		$trash = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=custom_dashboards&action=trash&id='.$item['id'].'')),
			__('Remove')
		);
		$edit = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=edit_dashboards&id='.$item['id'].'')),
			__('Edit')
		);

		$restore = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=custom_dashboards&action=restore&id='.$item['id'].'')),
			__('Restore')
		);
		$deleteperm = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(admin_url('admin.php?page=custom_dashboards&action=deleteperm&id='.$item['id'].'')),
			__('Delete Permanently')
		);

		$actions = array();
		$item['status'] = get_post_status($item['id']);

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

		return sprintf('<a class="row-title" href="%2$s">%1$s</a>%3$s',
			$item['title'],
            esc_url(site_url('/'.str_replace(" ", "-", $item['title']).'')),
			$this->row_actions($actions),
		);
	}

	protected function get_views() {
		$status = ! empty($_REQUEST['status']) ? wp_unslash( $_REQUEST['status'] ) : NULL;

		$stats_links  = array();
		$url = 'admin.php?page=custom_dashboards';

		$not_trashed_posts = get_posts(array(
			'post_type'      => 'post',  // Set to 'post' for regular posts
			'post_status'    => 'publish',  // Exclude drafts and other statuses
			'posts_per_page' => -1,  // Retrieve all posts
			'fields'         => 'ids',  // Return post IDs only
		));
		$trashed_posts = get_posts(array(
			'post_type'      => 'post',  // Set to 'post' for regular posts
			'post_status'    => 'trash',  // Exclude drafts and other statuses
			'posts_per_page' => -1,  // Retrieve all posts
			'fields'         => 'ids',  // Return post IDs only
		));

		$stats_links['all'] = array(
			'url'     => $url,
			'label'   => sprintf('%1$s <span class="count">(%2$s)</span>',
				__( 'All' ),
				number_format_i18n(count($not_trashed_posts))
			),
			'current' => empty( $status ),
		);

		$trash_count = count($trashed_posts);
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
					wp_trash_post($_REQUEST['id']);
					break;
				case "restore":
					wp_untrash_post($_REQUEST['id']);
					wp_update_post(array(
						'ID'           => $_REQUEST['id'],
						'post_status'  => 'publish',
					));
					break;
				case "deleteperm":
					wp_delete_post($_REQUEST['id']);
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
		$args = array(
			'post_type'      => 'post',  // Set to 'post' for regular posts
			'posts_per_page' => -1,  // Retrieve all posts
		);

		//Process view filter if we got one
		if(!empty( $_REQUEST['status'] )) { 
			switch(wp_unslash( $_REQUEST['status'] )) {
				case "trash":
					$args['post_status'] = 'trash';
					break;
				default:
					$args['post_status'] = 'published';
					break;
			}
		}



		//Load items
        $posts = DashboardPlugin::get_instance()->getDashboards($args);
        foreach($posts as $post) {
            $categories = DashboardPlugin::get_instance()->getCategoriesByDashboardId($post->ID);
            array_push($data, array(
                "id" => $post->ID,
                "title" => $post->post_title,
				"author" => get_user_by('id', $post->post_author)->display_name,
				"last_modified" => $post->post_modified,
                "status" => 0,
                "categories" => implode(", ", $categories),
            ));
        }
		
		$this->set_pagination($data);
	}	
}