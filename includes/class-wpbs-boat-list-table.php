<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WPBS_Boat_List_Table extends WP_List_Table
{
	/** @var array<string, array> */
	private $boat_rows_by_document_id = array();

	/** @var string */
	private $status_filter = 'all';

	public function __construct()
	{
		parent::__construct(array(
			'singular' => 'boat',
			'plural' => 'boats',
			'ajax' => false,
		));
	}

	public function get_primary_column_name()
	{
		return 'title';
	}

	public function get_columns()
	{
		return array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Boat', 'wpbs'),
			'document_id' => __('DocumentID', 'wpbs'),
			'sales_status' => __('Sales Status', 'wpbs'),
			'last_sync_at' => __('Last Sync', 'wpbs'),
			'soldout_at' => __('Sold Out At', 'wpbs'),
		);
	}

	protected function get_sortable_columns()
	{
		return array(
			'title' => array('title', false),
		);
	}

	protected function column_cb($item)
	{
		return '<input type="checkbox" name="post_ids[]" value="' . esc_attr((string)$item['ID']) . '" />';
	}

	protected function column_title($item)
	{
		$post_id = (int)$item['ID'];
		$title = get_the_title($post_id);
		$edit_link = get_edit_post_link($post_id);
		$document_id = $this->get_document_id_for_post($post_id);

		$actions = array();
		if ($edit_link) {
			$actions['edit'] = '<a href="' . esc_url($edit_link) . '">' . esc_html__('Edit', 'wpbs') . '</a>';
		}
		if ($document_id) {
			$url = add_query_arg(array(
				'page' => 'wpbs-boats',
				'wpbs_action' => 'sync_one',
				'post_id' => $post_id,
				'_wpnonce' => wp_create_nonce('wpbs_boats_action'),
			), admin_url('admin.php'));
			$actions['sync'] = '<a href="' . esc_url($url) . '">' . esc_html__('Sync now', 'wpbs') . '</a>';
		}

		return '<strong><a class="row-title" href="' . esc_url($edit_link) . '">' . esc_html($title) . '</a></strong>' . $this->row_actions($actions);
	}

	protected function column_document_id($item)
	{
		return esc_html((string)$this->get_document_id_for_post((int)$item['ID']));
	}

	protected function column_sales_status($item)
	{
		$doc = (string)$this->get_document_id_for_post((int)$item['ID']);
		$row = $doc && isset($this->boat_rows_by_document_id[$doc]) ? $this->boat_rows_by_document_id[$doc] : null;
		if ($row && isset($row['sales_status']) && $row['sales_status'] !== null) {
			return esc_html((string)$row['sales_status']);
		}
		return esc_html((string)get_post_meta((int)$item['ID'], 'wpbs_sales_status', true));
	}

	protected function column_last_sync_at($item)
	{
		$post_id = (int)$item['ID'];
		$doc = (string)$this->get_document_id_for_post($post_id);
		$row = $doc && isset($this->boat_rows_by_document_id[$doc]) ? $this->boat_rows_by_document_id[$doc] : null;
		if ($row && !empty($row['last_sync_at'])) {
			return esc_html((string)$row['last_sync_at']);
		}
		$meta_last_sync = get_post_meta($post_id, '_wpbs_last_sync_at', true);
		if ($meta_last_sync) {
			return esc_html((string)$meta_last_sync);
		}
		return '—';
	}

	protected function column_soldout_at($item)
	{
		$val = get_post_meta((int)$item['ID'], '_wpbs_soldout_at', true);
		return $val ? esc_html((string)$val) : '—';
	}

	protected function get_bulk_actions()
	{
		return array(
			'sync_selected' => __('Sync selected (force)', 'wpbs'),
			'mark_update' => __('Mark selected for update', 'wpbs'),
			'unmark_update' => __('Unmark selected', 'wpbs'),
		);
	}

	public function prepare_items()
	{
		$per_page = 20;
		$paged = isset($_REQUEST['paged']) ? max(1, (int)$_REQUEST['paged']) : 1;
		$search = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
		$this->status_filter = isset($_REQUEST['wpbs_status']) ? sanitize_text_field(wp_unslash($_REQUEST['wpbs_status'])) : 'all';
		if (!in_array($this->status_filter, array('all', 'active', 'sold'), true)) {
			$this->status_filter = 'all';
		}

		$meta_query = array();
		if ($this->status_filter === 'active') {
			$meta_query[] = array(
				'key' => 'wpbs_sales_status',
				'value' => 'Active',
				'compare' => '=',
			);
		} elseif ($this->status_filter === 'sold') {
			$meta_query[] = array(
				'key' => 'wpbs_sales_status',
				'value' => 'Active',
				'compare' => '!=',
			);
		}

		$query = new WP_Query(array(
			'post_type' => WPBS_POST_TYPE,
			'post_status' => array('publish', 'draft', 'pending', 'private', 'trash'),
			'posts_per_page' => $per_page,
			'paged' => $paged,
			'orderby' => 'date',
			'order' => 'DESC',
			'fields' => 'ids',
			's' => $search,
			'meta_query' => $meta_query,
		));

		$post_ids = $query->posts;
		$items = array();
		$document_ids = array();

		foreach ($post_ids as $post_id) {
			$items[] = array('ID' => (int)$post_id);
			$doc = (string)$this->get_document_id_for_post((int)$post_id);
			if ($doc !== '') {
				$document_ids[] = $doc;
			}
		}

		$this->prefetch_boat_rows($document_ids);

		$this->items = $items;
		$this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns(), $this->get_primary_column_name());
		$this->set_pagination_args(array(
			'total_items' => (int)$query->found_posts,
			'per_page' => $per_page,
			'total_pages' => (int)$query->max_num_pages,
		));
	}

	protected function extra_tablenav($which)
	{
		if ($which !== 'top') {
			return;
		}

		echo '<div class="alignleft actions">';
		echo '<label class="screen-reader-text" for="wpbs_status">' . esc_html__('Filter by status', 'wpbs') . '</label>';
		echo '<select name="wpbs_status" id="wpbs_status">';
		echo '<option value="all" ' . selected($this->status_filter, 'all', false) . '>' . esc_html__('All statuses', 'wpbs') . '</option>';
		echo '<option value="active" ' . selected($this->status_filter, 'active', false) . '>' . esc_html__('Active', 'wpbs') . '</option>';
		echo '<option value="sold" ' . selected($this->status_filter, 'sold', false) . '>' . esc_html__('Sold / Not active', 'wpbs') . '</option>';
		echo '</select>';
		submit_button(__('Filter'), 'secondary', 'filter_action', false);
		echo '</div>';
	}

	public function no_items()
	{
		echo esc_html__('No boats found.', 'wpbs');
	}

	private function prefetch_boat_rows($document_ids)
	{
		$this->boat_rows_by_document_id = array();
		$document_ids = array_values(array_filter(array_unique(array_map('strval', $document_ids))));
		if (empty($document_ids)) {
			return;
		}

		global $wpdb;
		$table = WPBS_DB::table_boats();

		$placeholders = implode(',', array_fill(0, count($document_ids), '%s'));
		$sql = $wpdb->prepare("SELECT document_id, sales_status, last_sync_at FROM {$table} WHERE document_id IN ({$placeholders})", $document_ids);
		$rows = $wpdb->get_results($sql, ARRAY_A);
		foreach ($rows as $row) {
			$this->boat_rows_by_document_id[(string)$row['document_id']] = $row;
		}
	}

	private function get_document_id_for_post($post_id)
	{
		$post_id = (int)$post_id;
		$doc = (string)get_post_meta($post_id, '_wpbs_document_id', true);
		if ($doc !== '') {
			return $doc;
		}
		$doc = (string)get_post_meta($post_id, 'wpbs_document_id', true);
		if ($doc !== '') {
			// Backfill underscore meta for older installs.
			update_post_meta($post_id, '_wpbs_document_id', $doc);
			return $doc;
		}
		return '';
	}
}
