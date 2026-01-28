<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WPBS_Queue_List_Table extends WP_List_Table
{
	/** @var string */
	private $status_filter = 'all';

	public function __construct()
	{
		parent::__construct(array(
			'singular' => 'queue_job',
			'plural' => 'queue_jobs',
			'ajax' => false,
		));
	}

	public function get_primary_column_name()
	{
		return 'id';
	}

	public function get_columns()
	{
		return array(
			'cb' => '<input type="checkbox" />',
			'id' => __('ID', 'wpbs'),
			'type' => __('Type', 'wpbs'),
			'status' => __('Status', 'wpbs'),
			'attempts' => __('Attempts', 'wpbs'),
			'not_before' => __('Not Before', 'wpbs'),
			'locked_at' => __('Locked', 'wpbs'),
			'updated_at' => __('Updated', 'wpbs'),
			'created_at' => __('Created', 'wpbs'),
			'payload' => __('Payload', 'wpbs'),
			'last_error' => __('Last Error', 'wpbs'),
		);
	}

	protected function get_sortable_columns()
	{
		return array(
			'id' => array('id', true),
			'type' => array('type', false),
			'status' => array('status', false),
			'attempts' => array('attempts', false),
			'updated_at' => array('updated_at', false),
			'created_at' => array('created_at', false),
		);
	}

	protected function column_cb($item)
	{
		return '<input type="checkbox" name="queue_ids[]" value="' . esc_attr((string)$item['id']) . '" />';
	}

	protected function column_id($item)
	{
		$id = (int)$item['id'];
		$actions = array();

		$actions['run'] = '<a href="#" class="wpbs-queue-run" data-job-id="' . esc_attr((string)$id) . '">' . esc_html__('Run now', 'wpbs') . '</a>';

		$delete_url = add_query_arg(array(
			'page' => 'wpbs-queue',
			'wpbs_action' => 'delete_one',
			'job_id' => $id,
			'_wpnonce' => wp_create_nonce('wpbs_queue_action'),
		), admin_url('admin.php'));
		$actions['delete'] = '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Delete this job?\');">' . esc_html__('Delete', 'wpbs') . '</a>';

		return '<strong>' . esc_html((string)$id) . '</strong>' . $this->row_actions($actions);
	}

	protected function column_type($item)
	{
		return esc_html((string)$item['type']);
	}

	protected function column_status($item)
	{
		$status = (string)$item['status'];
		return esc_html($status);
	}

	protected function column_attempts($item)
	{
		return esc_html((string)(int)$item['attempts']);
	}

	protected function column_not_before($item)
	{
		return !empty($item['not_before']) ? esc_html((string)$item['not_before']) : '—';
	}

	protected function column_locked_at($item)
	{
		return !empty($item['locked_at']) ? esc_html((string)$item['locked_at']) : '—';
	}

	protected function column_updated_at($item)
	{
		return !empty($item['updated_at']) ? esc_html((string)$item['updated_at']) : '—';
	}

	protected function column_created_at($item)
	{
		return !empty($item['created_at']) ? esc_html((string)$item['created_at']) : '—';
	}

	protected function column_payload($item)
	{
		$raw = isset($item['payload']) ? (string)$item['payload'] : '';
		$decoded = WPBS_Utils::json_decode_assoc($raw);

		$parts = array();
		if (is_array($decoded)) {
			if (!empty($decoded['document_id'])) {
				$parts[] = '<strong>document_id:</strong> ' . esc_html((string)$decoded['document_id']);
			}
			if (!empty($decoded['run_id'])) {
				$parts[] = '<strong>run_id:</strong> ' . esc_html((string)$decoded['run_id']);
			}
		}

		$preview = $raw;
		if (strlen($preview) > 200) {
			$preview = substr($preview, 0, 200) . '…';
		}

		$meta = !empty($parts) ? '<div style="margin-bottom:4px;">' . implode(' &nbsp; ', $parts) . '</div>' : '';
		return $meta . '<code style="white-space:pre-wrap;">' . esc_html($preview) . '</code>';
	}

	protected function column_last_error($item)
	{
		$err = isset($item['last_error']) ? (string)$item['last_error'] : '';
		if ($err === '') {
			return '—';
		}
		$preview = $err;
		if (strlen($preview) > 200) {
			$preview = substr($preview, 0, 200) . '…';
		}
		return '<code style="white-space:pre-wrap;">' . esc_html($preview) . '</code>';
	}

	protected function get_bulk_actions()
	{
		return array(
			'run_selected' => __('Run selected now', 'wpbs'),
			'delete_selected' => __('Delete selected', 'wpbs'),
		);
	}

	public function prepare_items()
	{
		global $wpdb;
		$table = WPBS_DB::table_queue();

		$per_page = 20;
		$paged = isset($_REQUEST['paged']) ? max(1, (int)$_REQUEST['paged']) : 1;
		$offset = ($paged - 1) * $per_page;

		$search = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
		$this->status_filter = isset($_REQUEST['wpbs_status']) ? sanitize_text_field(wp_unslash($_REQUEST['wpbs_status'])) : 'all';
		if (!in_array($this->status_filter, array('all', 'pending', 'processing', 'failed', 'done'), true)) {
			$this->status_filter = 'all';
		}

		$orderby = isset($_REQUEST['orderby']) ? sanitize_text_field(wp_unslash($_REQUEST['orderby'])) : 'id';
		$order = isset($_REQUEST['order']) ? sanitize_text_field(wp_unslash($_REQUEST['order'])) : 'DESC';
		$allowed_orderby = array('id', 'type', 'status', 'attempts', 'updated_at', 'created_at');
		if (!in_array($orderby, $allowed_orderby, true)) {
			$orderby = 'id';
		}
		$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

		$where = array();
		$args = array();

		if ($this->status_filter !== 'all') {
			$where[] = 'status = %s';
			$args[] = $this->status_filter;
		}
		if ($search !== '') {
			$where[] = '(type LIKE %s OR payload LIKE %s OR last_error LIKE %s)';
			$like = '%' . $wpdb->esc_like($search) . '%';
			$args[] = $like;
			$args[] = $like;
			$args[] = $like;
		}

		$where_sql = '';
		if (!empty($where)) {
			$where_sql = 'WHERE ' . implode(' AND ', $where);
		}

		$count_sql = "SELECT COUNT(*) FROM {$table} {$where_sql}";
		if (!empty($args)) {
			$count_sql = $wpdb->prepare($count_sql, $args);
		}
		$total_items = (int)$wpdb->get_var($count_sql);

		$data_sql = "SELECT * FROM {$table} {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$data_args = $args;
		$data_args[] = $per_page;
		$data_args[] = $offset;
		$data_sql = $wpdb->prepare($data_sql, $data_args);
		$items = $wpdb->get_results($data_sql, ARRAY_A);

		$this->items = is_array($items) ? $items : array();
		$this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns(), $this->get_primary_column_name());
		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => (int)ceil($total_items / $per_page),
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
		echo '<option value="pending" ' . selected($this->status_filter, 'pending', false) . '>' . esc_html__('Pending', 'wpbs') . '</option>';
		echo '<option value="processing" ' . selected($this->status_filter, 'processing', false) . '>' . esc_html__('Processing', 'wpbs') . '</option>';
		echo '<option value="failed" ' . selected($this->status_filter, 'failed', false) . '>' . esc_html__('Failed', 'wpbs') . '</option>';
		echo '<option value="done" ' . selected($this->status_filter, 'done', false) . '>' . esc_html__('Done', 'wpbs') . '</option>';
		echo '</select>';
		submit_button(__('Filter'), 'secondary', 'filter_action', false);
		echo '</div>';
	}

	public function no_items()
	{
		echo esc_html__('No queue jobs found.', 'wpbs');
	}
}
