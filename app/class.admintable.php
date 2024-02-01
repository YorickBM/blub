<?php
namespace ModulairDashboard;

require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

class AdminTable extends \WP_List_Table {

    /**
     * Class constructor
     */
    public function __construct($singular = "item", $plural = "items", $ajax = false) {
		parent::__construct(array(
		'singular' => $singular,
		'plural' => $plural,
		'ajax' => $ajax,
		));
	}

    /**
     * Default render callback for columns within the table
     */
    protected function column_default($item, $column_name) {
		if(array_key_exists(strtolower($column_name), $item)) {
			return $item[strtolower($column_name)];
		} else {
			return "Could not find '".strtolower($column_name)."'.";
		}
	}

    /**
     * Render callback for cb column, this shows the checkbox in table
     */
    protected function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item['id']
		);
	}

    /**
     * Function resonpsible for handeling the column sorting of data
     */
    protected function usort_reorder($a, $b) {
		$orderby = ! empty( $_REQUEST['orderby'] ) ? wp_unslash( $_REQUEST['orderby'] ) : 'id';
		$order = ! empty( $_REQUEST['order'] ) ? wp_unslash( $_REQUEST['order'] ) : 'dsc';
		
		$result = strcmp($a[$orderby], $b[$orderby]);
		
		return ('asc' === $order) ? $result : - $result;
	}

    /**
     * Returns the columns that should be hidden on display.
     * By default hide: id
     */
    protected function get_hidden_columns() {
        return array('id');
    }


    /**
     * Dummy function for row action trigger in setup
     */
    protected function process_row_actions() {

    }

    /**
     * Dummy function for bulk action trigger in setup
     */
    protected function process_bulk_action() {

    }

    /**
     * Setup is responsible for setting up the table, with the correct columns and triggering the row and bulk action triggers
     */
    public function setup() {
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $hidden = $this->get_hidden_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable);

        $this->process_row_actions();
        $this->process_bulk_action();
    }

    /**
     * Is responsible for handeling pagination of the table, so only data is visible that should be visible for page.
     */
    protected function set_pagination($data, $per_page = 10) {
        usort($data, array($this, 'usort_reorder'));

        $current_page = $this->get_pagenum();
		$total_items = count($data);
		
		$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
		
		$this->items = $data;
		$this->set_pagination_args( array(
			'total_items'	=> $total_items,
			'per_page'		=> $per_page,
			'total_pages'	=> ceil($total_items / $per_page),
		));
    }
}