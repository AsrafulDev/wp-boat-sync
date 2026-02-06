<?php

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Admin
{
	/** @var WPBS_Sync */
	private $sync;

	public function __construct($sync)
	{
		$this->sync = $sync;
	}

	public function init()
	{
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
		add_action('admin_post_wpbs_save_settings', array($this, 'handle_save_settings'));
		add_action('admin_post_wpbs_manual_sync', array($this, 'handle_manual_sync'));
		add_action('admin_post_wpbs_sync_marked', array($this, 'handle_sync_marked'));
		add_action('admin_post_wpbs_run_queue_now', array($this, 'handle_run_queue_now'));
		add_action('wp_ajax_wpbs_queue_start', array($this, 'ajax_queue_start'));
		add_action('wp_ajax_wpbs_queue_tick', array($this, 'ajax_queue_tick'));
		add_action('wp_ajax_wpbs_queue_worker_status', array($this, 'ajax_queue_worker_status'));
		add_action('wp_ajax_wpbs_queue_worker_tick', array($this, 'ajax_queue_worker_tick'));
		add_action('wp_ajax_wpbs_dedupe_start', array($this, 'ajax_dedupe_start'));
		add_action('wp_ajax_wpbs_dedupe_tick', array($this, 'ajax_dedupe_tick'));
		add_action('wp_ajax_wpbs_queue_recover_processing', array($this, 'ajax_queue_recover_processing'));
		// One-off retrofit to assign brand terms from existing meta
		add_action('admin_post_wpbs_retrofit_brands', array($this, 'handle_retrofit_brands'));
		// Dry-run version (reports what would change without making assignments)
		add_action('admin_post_wpbs_retrofit_brands_dryrun', array($this, 'handle_retrofit_brands_dryrun'));
	}

	public function enqueue_admin_assets($hook)
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		if (!in_array($page, array('wpbs', 'wpbs-queue'), true)) {
			return;
		}

		// Add responsive CSS for admin pages
		wp_add_inline_style('wp-admin', '
			@media (max-width: 782px) {
				.wpbs-stats-grid { grid-template-columns: 1fr !important; max-width: 100%; overflow-x: auto; }
				.card { max-width: 100%; overflow-x: auto; }
				#wpbs-worker-modal, #wpbs-dedupe-modal { width: calc(100% - 20px) !important; margin: 4vh 10px !important; }
				.wp-list-table { font-size: 13px; }
				.wp-list-table td, .wp-list-table th { padding: 8px 4px !important; }
			}
			@media (max-width: 600px) {
				.wpbs-stats-grid { gap: 8px !important; max-width: 100%; overflow-x: auto; }
				.card { max-width: 100%; overflow-x: auto; }
				.wpbs-chart-container { height: 250px !important; }
				#wpbs-worker-modal > div:first-child, #wpbs-dedupe-modal > div:first-child { flex-wrap: wrap; gap: 8px; }
				.button { font-size: 13px; padding: 4px 8px; }
			}
		');

		wp_register_script('wpbs-admin-queue-recover', WPBS_PLUGIN_URL . 'assets/js/wpbs-admin-queue-recover.js', array('jquery'), WPBS_VERSION, true);
		wp_localize_script('wpbs-admin-queue-recover', 'WPBS_QUEUE_RECOVER', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('wpbs_queue_recover_ajax'),
			'staleSeconds' => 60,
		));

		if ($page === 'wpbs-queue') {
			wp_register_script('wpbs-admin-queue', WPBS_PLUGIN_URL . 'assets/js/wpbs-admin-queue.js', array('jquery'), WPBS_VERSION, true);
			wp_localize_script('wpbs-admin-queue', 'WPBS_QUEUE', array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('wpbs_queue_ajax'),
				'tickMs' => 1000,
				'batchSize' => 10,
			));
			wp_enqueue_script('wpbs-admin-queue');
			wp_enqueue_script('wpbs-admin-queue-recover');
			return;
		}

		// Chart.js (local vendored) + dashboard renderer.
		wp_register_script('wpbs-chartjs', WPBS_PLUGIN_URL . 'assets/vendor/chartjs/chart.umd.min.js', array(), '4.4.1', true);
		wp_register_script('wpbs-admin-dashboard', WPBS_PLUGIN_URL . 'assets/js/wpbs-admin-dashboard.js', array('wpbs-chartjs'), WPBS_VERSION, true);

		$dash = $this->get_dashboard_data();
		wp_localize_script('wpbs-admin-dashboard', 'WPBS_DASH', $dash);
		wp_enqueue_script('wpbs-admin-dashboard');

		wp_register_script('wpbs-admin-worker-popup', WPBS_PLUGIN_URL . 'assets/js/wpbs-admin-worker-popup.js', array('jquery'), WPBS_VERSION, true);
		wp_localize_script('wpbs-admin-worker-popup', 'WPBS_WORKER', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('wpbs_queue_worker_ajax'),
			'tickMs' => 500,
			'batchSize' => 5,
		));
		wp_enqueue_script('wpbs-admin-worker-popup');

		wp_register_script('wpbs-admin-dedupe-popup', WPBS_PLUGIN_URL . 'assets/js/wpbs-admin-dedupe-popup.js', array('jquery'), WPBS_VERSION, true);
		wp_localize_script('wpbs-admin-dedupe-popup', 'WPBS_DEDUPE', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('wpbs_dedupe_ajax'),
			'tickMs' => 1000,
			'batchSize' => 10,
		));
		wp_enqueue_script('wpbs-admin-dedupe-popup');
		wp_enqueue_script('wpbs-admin-queue-recover');
	}

	public function admin_menu()
	{
		add_menu_page(
			__('Boat Sync', 'wpbs'),
			__('Boat Sync', 'wpbs'),
			'manage_options',
			'wpbs',
			array($this, 'render_dashboard'),
			'dashicons-update'
		);

		add_submenu_page('wpbs', __('Boats', 'wpbs'), __('Boats', 'wpbs'), 'manage_options', 'wpbs-boats', array($this, 'render_boats_table'));
		add_submenu_page('wpbs', __('Queue', 'wpbs'), __('Queue', 'wpbs'), 'manage_options', 'wpbs-queue', array($this, 'render_queue_table'));
		add_submenu_page('wpbs', __('Settings', 'wpbs'), __('Settings', 'wpbs'), 'manage_options', 'wpbs-settings', array($this, 'render_settings'));
		add_submenu_page('wpbs', __('Shortcode Builder', 'wpbs'), __('Shortcode Builder', 'wpbs'), 'manage_options', 'wpbs-shortcodes', array($this, 'render_shortcode_builder'));
	}

	public function render_queue_table()
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-queue-list-table.php';

		// Handle row actions.
		if (!empty($_GET['wpbs_action']) && in_array((string)$_GET['wpbs_action'], array('run_one', 'delete_one'), true)) {
			check_admin_referer('wpbs_queue_action');
			$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
			if ($job_id > 0) {
				if ((string)$_GET['wpbs_action'] === 'run_one') {
					$res = $this->sync->run_queue_jobs_by_ids(array($job_id));
					wp_safe_redirect(admin_url('admin.php?page=wpbs-queue&ran=' . (int)$res['ran'] . '&done=' . (int)$res['done'] . '&failed=' . (int)$res['failed']));
					exit;
				}
				if ((string)$_GET['wpbs_action'] === 'delete_one') {
					global $wpdb;
					$table = WPBS_DB::table_queue();
					$wpdb->delete($table, array('id' => $job_id), array('%d'));
					wp_safe_redirect(admin_url('admin.php?page=wpbs-queue&deleted=1'));
					exit;
				}
			}
		}

		$table = new WPBS_Queue_List_Table();

		// Handle bulk actions.
		$action = $table->current_action();
		if ($action) {
			check_admin_referer('bulk-queue');
			$job_ids = isset($_REQUEST['queue_ids']) ? (array)$_REQUEST['queue_ids'] : array();
			$job_ids = array_map('intval', $job_ids);
			$job_ids = array_values(array_filter($job_ids));

			if (!empty($job_ids)) {
				switch ($action) {
					case 'run_selected':
						$res = $this->sync->run_queue_jobs_by_ids($job_ids);
						wp_safe_redirect(admin_url('admin.php?page=wpbs-queue&ran=' . (int)$res['ran'] . '&done=' . (int)$res['done'] . '&failed=' . (int)$res['failed']));
						exit;
					case 'delete_selected':
						global $wpdb;
						$qtable = WPBS_DB::table_queue();
						$id_list = implode(',', array_map('intval', $job_ids));
						if ($id_list !== '') {
							$wpdb->query("DELETE FROM {$qtable} WHERE id IN ({$id_list})");
						}
						wp_safe_redirect(admin_url('admin.php?page=wpbs-queue&deleted=' . count($job_ids)));
						exit;
				}
			}

			wp_safe_redirect(admin_url('admin.php?page=wpbs-queue'));
			exit;
		}

		$table->prepare_items();

		echo '<div class="wrap">';
		echo '<h1>Sync Queue</h1>';
		echo '<p style="margin:10px 0;">'
			. '<button type="button" class="button" id="wpbs-queue-recover-btn">Reset stuck processing jobs now</button>'
			. ' <span id="wpbs-queue-recover-status" style="margin-left:8px; opacity:.85;"></span>'
			. '</p>';
		echo '<div id="wpbs-queue-runner" style="display:none; margin:10px 0; padding:10px 12px; background:#fff; border:1px solid #ccd0d4; border-left:4px solid #2271b1;">'
			. '<strong>Running selected jobs…</strong> '
			. '<span id="wpbs-queue-runner-status" style="margin-left:8px; opacity:.85;"></span>'
			. '<div style="margin-top:6px;">'
			. '<button type="button" class="button" id="wpbs-queue-runner-cancel">Stop</button>'
			. '<button type="button" class="button button-primary" id="wpbs-queue-runner-refresh" style="display:none;">Refresh</button>'
			. '</div>'
			. '</div>';

		$ran = isset($_GET['ran']) ? (int)$_GET['ran'] : 0;
		$done = isset($_GET['done']) ? (int)$_GET['done'] : 0;
		$failed = isset($_GET['failed']) ? (int)$_GET['failed'] : 0;
		$deleted = isset($_GET['deleted']) ? (int)$_GET['deleted'] : 0;
		if ($ran > 0) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf('Ran %d job(s). Done: %d. Failed: %d.', $ran, $done, $failed)) . '</p></div>';
		} elseif ($deleted > 0) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf('Deleted %d job(s).', $deleted)) . '</p></div>';
		}

		// Single POST form so bulk actions + search/filter all work together.
		echo '<form method="post" action="' . esc_url(admin_url('admin.php?page=wpbs-queue')) . '">';
		echo '<input type="hidden" name="page" value="wpbs-queue" />';
		wp_nonce_field('bulk-queue');
		$table->search_box(__('Search jobs'), 'wpbs-queue');
		$table->display();
		echo '</form>';

		echo '<p style="opacity:.8;max-width:900px;">Tip: Select multiple jobs and use <strong>Bulk actions → Run selected now</strong> to immediately process those jobs (ignores the Not Before delay). This is useful when jobs are stuck or when you want to force a specific boat sync.</p>';

		// Payload viewer modal.
		echo '<div id="wpbs-payload-overlay" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.5);">'
			. '<div id="wpbs-payload-modal" role="dialog" aria-modal="true" style="background:#fff;width:700px;max-width:calc(100% - 40px);max-height:80vh;margin:10vh auto;border-radius:6px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.25);display:flex;flex-direction:column;">'
			. '<div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f0f0f1;border-bottom:1px solid #c3c4c7;">'
			. '<strong style="font-size:14px;">' . esc_html__('Payload Details', 'wpbs') . '</strong>'
			. '<button type="button" id="wpbs-payload-close" class="button button-small">&times;</button>'
			. '</div>'
			. '<pre id="wpbs-payload-content" style="margin:0;padding:16px;overflow:auto;flex:1;white-space:pre-wrap;word-break:break-word;font-size:13px;background:#fff;"></pre>'
			. '</div>'
			. '</div>';

		echo '</div>';
	}

	public function ajax_queue_start()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Forbidden'), 403);
		}
		check_ajax_referer('wpbs_queue_ajax', 'nonce');

		$job_ids = isset($_POST['jobIds']) ? (array)$_POST['jobIds'] : array();
		$job_ids = array_map('intval', $job_ids);
		$job_ids = array_values(array_filter(array_unique($job_ids)));
		if (empty($job_ids)) {
			wp_send_json_error(array('message' => 'No jobs selected.'), 400);
		}

		$token = wp_generate_password(12, false, false);
		$key = 'wpbs_queue_run_' . $token;

		$state = array(
			'token' => $token,
			'remaining' => $job_ids,
			'ran' => 0,
			'done' => 0,
			'failed' => 0,
			'started_at' => time(),
		);

		set_transient($key, $state, 10 * MINUTE_IN_SECONDS);
		wp_send_json_success(array(
			'token' => $token,
			'remaining' => count($job_ids),
		));
	}

	public function ajax_queue_tick()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Forbidden'), 403);
		}
		check_ajax_referer('wpbs_queue_ajax', 'nonce');

		$token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
		$batch_size = isset($_POST['batchSize']) ? (int)$_POST['batchSize'] : 3;
		$batch_size = max(1, min(10, $batch_size));
		if ($token === '') {
			wp_send_json_error(array('message' => 'Missing token.'), 400);
		}

		$key = 'wpbs_queue_run_' . $token;
		$state = get_transient($key);
		if (!is_array($state) || empty($state['remaining']) || !is_array($state['remaining'])) {
			wp_send_json_success(array(
				'finished' => true,
				'remaining' => 0,
				'ran' => 0,
				'done' => 0,
				'failed' => 0,
			));
		}

		$remaining = array_values(array_map('intval', $state['remaining']));
		$batch = array_slice($remaining, 0, $batch_size);
		$remaining = array_slice($remaining, $batch_size);

		$res = $this->sync->run_queue_jobs_by_ids($batch);
		$state['ran'] += isset($res['ran']) ? (int)$res['ran'] : 0;
		$state['done'] += isset($res['done']) ? (int)$res['done'] : 0;
		$state['failed'] += isset($res['failed']) ? (int)$res['failed'] : 0;
		$state['remaining'] = $remaining;

		set_transient($key, $state, 10 * MINUTE_IN_SECONDS);

		$finished = empty($remaining);
		wp_send_json_success(array(
			'finished' => $finished,
			'remaining' => count($remaining),
			'ran' => (int)$state['ran'],
			'done' => (int)$state['done'],
			'failed' => (int)$state['failed'],
		));
	}

	public function ajax_dedupe_start()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Forbidden'), 403);
		}
		check_ajax_referer('wpbs_dedupe_ajax', 'nonce');

		$total = (int)$this->sync->count_duplicate_boat_groups();
		$token = wp_generate_password(12, false, false);
		$key = 'wpbs_dedupe_' . $token;

		$state = array(
			'token' => $token,
			'total' => $total,
			'offset' => 0,
			'processed' => 0,
			'deleted_posts' => 0,
			'reparented_attachments' => 0,
			'started_at' => time(),
		);

		set_transient($key, $state, 20 * MINUTE_IN_SECONDS);

		wp_send_json_success(array(
			'token' => $token,
			'totalGroups' => $total,
			'processedGroups' => 0,
			'deletedPosts' => 0,
			'reparentedAttachments' => 0,
			'finished' => ($total === 0),
		));
	}

	public function ajax_dedupe_tick()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Forbidden'), 403);
		}
		check_ajax_referer('wpbs_dedupe_ajax', 'nonce');

		$token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
		$batch_size = isset($_POST['batchSize']) ? (int)$_POST['batchSize'] : 10;
		$batch_size = max(1, min(50, $batch_size));
		if ($token === '') {
			wp_send_json_error(array('message' => 'Missing token.'), 400);
		}

		$key = 'wpbs_dedupe_' . $token;
		$state = get_transient($key);
		if (!is_array($state)) {
			wp_send_json_error(array('message' => 'Expired. Please start again.'), 400);
		}

		$total = isset($state['total']) ? (int)$state['total'] : 0;
		$offset = isset($state['offset']) ? (int)$state['offset'] : 0;

		if ($total === 0) {
			wp_send_json_success(array(
				'totalGroups' => 0,
				'processedGroups' => 0,
				'deletedPosts' => 0,
				'reparentedAttachments' => 0,
				'finished' => true,
			));
		}

		$res = $this->sync->dedupe_boats_batch($offset, $batch_size);
		$state['offset'] = isset($res['next_offset']) ? (int)$res['next_offset'] : $offset;
		$state['processed'] += isset($res['processed_groups']) ? (int)$res['processed_groups'] : 0;
		$state['deleted_posts'] += isset($res['deleted_posts']) ? (int)$res['deleted_posts'] : 0;
		$state['reparented_attachments'] += isset($res['reparented_attachments']) ? (int)$res['reparented_attachments'] : 0;

		$finished = false;
		if (!empty($res['finished'])) {
			$finished = true;
		}
		if ((int)$state['processed'] >= $total) {
			$finished = true;
		}
		if ((int)$state['offset'] >= $total) {
			$finished = true;
		}

		set_transient($key, $state, 20 * MINUTE_IN_SECONDS);

		wp_send_json_success(array(
			'totalGroups' => $total,
			'processedGroups' => (int)$state['processed'],
			'deletedPosts' => (int)$state['deleted_posts'],
			'reparentedAttachments' => (int)$state['reparented_attachments'],
			'finished' => $finished,
		));
	}

	public function ajax_queue_recover_processing()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Forbidden'), 403);
		}
		check_ajax_referer('wpbs_queue_recover_ajax', 'nonce');

		$stale = isset($_POST['staleSeconds']) ? (int)$_POST['staleSeconds'] : 60;
		$stale = max(0, min(86400, $stale));

		$recovered = (int)$this->sync->recover_stale_processing_jobs($stale);
		wp_send_json_success(array('recovered' => $recovered));
	}

	public function render_boats_table()
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-boat-list-table.php';

		// Handle row action links.
		if (!empty($_GET['wpbs_action']) && $_GET['wpbs_action'] === 'sync_one') {
			check_admin_referer('wpbs_boats_action');
			$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
			$doc = $post_id ? get_post_meta($post_id, '_wpbs_document_id', true) : '';
			if ($doc) {
				$this->sync->enqueue_sync_boat((string)$doc, true);
			}
			wp_safe_redirect(admin_url('admin.php?page=wpbs-boats&synced=1'));
			exit;
		}

		$table = new WPBS_Boat_List_Table();

		// Handle bulk actions.
		$action = $table->current_action();
		if ($action) {
			check_admin_referer('bulk-boats');
			$post_ids = isset($_REQUEST['post_ids']) ? (array)$_REQUEST['post_ids'] : array();
			$post_ids = array_map('intval', $post_ids);
			$post_ids = array_values(array_filter($post_ids));

			if (!empty($post_ids)) {
				foreach ($post_ids as $post_id) {
					$doc = get_post_meta($post_id, '_wpbs_document_id', true);
					if (!$doc) {
						continue;
					}
					switch ($action) {
						case 'sync_selected':
							$this->sync->enqueue_sync_boat((string)$doc, true);
							break;
						case 'mark_update':
							update_post_meta($post_id, '_wpbs_force_update', '1');
							break;
						case 'unmark_update':
							delete_post_meta($post_id, '_wpbs_force_update');
							break;
					}
				}
			}

			wp_safe_redirect(admin_url('admin.php?page=wpbs-boats&bulk=1'));
			exit;
		}

		$table->prepare_items();

		echo '<div class="wrap">';
		echo '<h1>Boats</h1>';

		// Single POST form so bulk actions + search/filter all work together.
		echo '<form method="post" action="' . esc_url(admin_url('admin.php?page=wpbs-boats')) . '">';
		echo '<input type="hidden" name="page" value="wpbs-boats" />';
		wp_nonce_field('bulk-boats');
		$table->search_box(__('Search boats'), 'wpbs-boats');
		$table->display();
		echo '</form>';

		echo '</div>';
	}

	public function render_dashboard()
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$settings = WPBS_Utils::get_settings();
		$run = get_option(WPBS_OPTION_RUN_STATE, array());
		$dash = $this->get_dashboard_data();
		$eta = isset($dash['queueEtaSeconds']) ? (int)$dash['queueEtaSeconds'] : 0;
		$eta_label = $eta > 0 ? $this->format_duration($eta) : '—';
		$speed = isset($dash['queueMetrics']['avg_jobs_per_sec']) ? (float)$dash['queueMetrics']['avg_jobs_per_sec'] : 0.0;
		$speed_label = $speed > 0 ? number_format($speed * 60, 1) . ' jobs/min' : '—';

		echo '<div class="wrap">';
		echo '<h1>Boat Sync</h1>';

		echo '<div style="display:flex;gap:12px;flex-wrap:wrap;margin:12px 0 18px;">';
		echo '<div class="card" style="min-width:220px;flex:1;">';
		echo '<h2 style="margin-top:0;">Connection</h2>';
		echo '<p><strong>API Key:</strong> ' . esc_html($settings['api_key'] ? 'Saved' : 'Not set') . '</p>';
		echo '<p><strong>PartyId:</strong> ' . esc_html($settings['party_id'] ? $settings['party_id'] : 'Not set') . '</p>';
		echo '<p><strong>Auto sync:</strong> ' . esc_html(isset($settings['auto_sync_frequency']) ? (string)$settings['auto_sync_frequency'] : 'off') . '</p>';

		$freq = isset($settings['auto_sync_frequency']) ? (string)$settings['auto_sync_frequency'] : 'off';
		$next_ts = ($freq !== 'off') ? wp_next_scheduled(WPBS_CRON_AUTO_SYNC) : false;
		$next_label = '—';
		if ($freq !== 'off' && !$next_ts) {
			$next_label = 'Not scheduled';
		} elseif ($next_ts) {
			$next_label = wp_date('Y-m-d H:i:s', (int)$next_ts);
		}
		echo '<p><strong>Next sync:</strong> ' . esc_html($next_label) . '</p>';
		echo '</div>';

		echo '<div class="card" style="min-width:220px;flex:1;">';
		echo '<h2 style="margin-top:0;">Boats</h2>';
		echo '<p><strong>Total posts:</strong> ' . esc_html((string)$dash['postCounts']['total']) . '</p>';
		echo '<p><strong>Published:</strong> ' . esc_html((string)$dash['postCounts']['publish']) . ' &nbsp; <strong>Draft:</strong> ' . esc_html((string)$dash['postCounts']['draft']) . '</p>';
		echo '<p><strong>Active:</strong> ' . esc_html((string)$dash['statusCounts']['active']) . ' &nbsp; <strong>Sold/Other:</strong> ' . esc_html((string)$dash['statusCounts']['sold']) . '</p>';
		echo '<p><strong>Marked for update:</strong> ' . esc_html((string)$dash['pendingUpdateCount']) . '</p>';
		echo '<p><strong>Last sync:</strong> ' . esc_html($dash['lastSyncAt'] ? (string)$dash['lastSyncAt'] : '—') . '</p>';
		echo '</div>';

		echo '<div class="card" style="min-width:220px;flex:1;">';
		echo '<h2 style="margin-top:0;">Queue</h2>';
		echo '<p><strong>Complete:</strong> ' . esc_html((string)$dash['queueCounts']['done']) . ' &nbsp; <strong>Processing:</strong> ' . esc_html((string)$dash['queueCounts']['processing']) . '</p>';
		echo '<p><strong>Pending:</strong> ' . esc_html((string)$dash['queueCounts']['pending']) . ' &nbsp; <strong>Failed:</strong> ' . esc_html((string)$dash['queueCounts']['failed']) . '</p>';
		echo '<p><strong>Speed:</strong> ' . esc_html($speed_label) . '</p>';
		echo '<p><strong>ETA (estimate):</strong> ' . esc_html($eta_label) . '</p>';
		echo '</div>';
		
		echo '<div class="static-row">';
		echo '<div class="card col-8 col-md-12 col-sm-12">';
		echo '<h2 style="margin-top:0;">Sync activity (last 7 days hourly)</h2>';
		echo '<div style="height:260px;"><canvas id="wpbsChartSync" aria-label="Sync activity chart" role="img"></canvas></div>';
		echo '</div>';
		echo '<div class="card col-4 col-md-12 col-sm-12">';
		echo '<h2 style="margin-top:0;">Status</h2>';
		echo '<div style="height:260px;"><canvas id="wpbsChartStatus" aria-label="Status chart" role="img"></canvas></div>';
		echo '</div>';

		echo '<div class="card col-8 col-md-12 col-sm-12">';
		echo '<h2 style="margin-top:0;">Queue overview</h2>';
		echo '<div style="height:260px;"><canvas id="wpbsChartQueue" aria-label="Queue chart" role="img"></canvas></div>';
		echo '</div>';
		echo '<div class="card col-4 col-md-12 col-sm-12">';
		echo '<h2 style="margin-top:0;">Actions</h2>';
		echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
		echo '<input type="hidden" name="action" value="wpbs_manual_sync" />';
		wp_nonce_field('wpbs_manual_sync');
		echo '<input type="hidden" name="mode" value="full" />';
		echo '<p><button class="button button-primary">Run Full Sync Now</button></p>';
		echo '</form>';
		echo '<p><a class="button" href="' . esc_url(admin_url('admin.php?page=wpbs-boats')) . '">Open Boats Table</a></p>';
		echo '<p style="margin-top:10px;">';
		echo '<button type="button" class="button" id="wpbs-run-queue-worker">Run Queue Worker Now</button> <span style="opacity:.75;">(runs in background with progress)</span>';
		echo '</p>';
		echo '<p style="margin-top:10px;">';
		echo '<button type="button" class="button" id="wpbs-queue-recover-btn">Reset stuck processing jobs now</button>';
		echo ' <span id="wpbs-queue-recover-status" style="opacity:.75;"></span>';
		echo '</p>';
		echo '<p style="margin-top:10px;">';
		echo '<button type="button" class="button button-secondary" id="wpbs-dedupe-now">Deduplicate Boats Now</button> <span style="opacity:.75;">(removes duplicate boat posts)</span>';
		echo '</p>';
		// Fallback for no-JS.
		echo '<noscript><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
		echo '<input type="hidden" name="action" value="wpbs_run_queue_now" />';
		wp_nonce_field('wpbs_run_queue_now');
		echo '<p><button class="button">Run Queue Worker Now</button></p>';
		echo '</form></noscript>';
		echo '<p style="opacity:.85;max-width:520px;">If sync feels slow on localhost, it is usually because WP-Cron only runs when the site gets visits. The button above helps, or you can set up a real server cron to call wp-cron.php.</p>';
		echo '</div>';
		echo '</div>';

		// Simple modal (no dependencies) for the queue worker.
		?>
		<style>
			#wpbs-worker-overlay, #wpbs-dedupe-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 99999; display: none; }
			#wpbs-worker-modal, #wpbs-dedupe-modal { background: #fff; width: 520px; max-width: calc(100% - 24px); margin: 8vh auto; border-radius: 6px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
			.wpbs-modal-header { padding: 12px 14px; border-bottom: 1px solid #e5e5e5; display: flex; align-items: center; justify-content: space-between; }
			.wpbs-modal-body { padding: 14px; }
			.wpbs-status { margin-bottom: 10px; }
			.wpbs-progress { height: 10px; background: #f0f0f1; border-radius: 999px; overflow: hidden; }
			.wpbs-progress-bar { height: 10px; width: 0%; background: #2271b1; transition: width 0.3s ease; }
			.wpbs-stats { display: flex; gap: 14px; margin-top: 12px; flex-wrap: wrap; }
			.wpbs-actions { margin-top: 14px; }
			.wpbs-note { opacity: .75; margin-left: 8px; }
			@media (max-width: 600px) {
				#wpbs-worker-modal, #wpbs-dedupe-modal { width: calc(100% - 20px); margin: 4vh 10px; }
				.wpbs-modal-header, .wpbs-modal-body { padding: 10px !important; }
				#wpbs-worker-modal button, #wpbs-dedupe-modal button { font-size: 12px; padding: 4px 8px; }
				.wpbs-stats { gap: 8px; font-size: 13px; }
				.wpbs-note { display: block; margin-left: 0; margin-top: 8px; }
			}
			.static-row { display:flex;gap:12px;flex-wrap:wrap;margin:12px 0 18px;}
			.static-row .col-8 { width: 65.6667% !important; }
			.static-row .col-4 { width: 33.3333% !important; }
			.card { background: #fff; padding: 16px; border: 1px solid #e5e5e5; border-radius: 6px; }
			@media (max-width: 1080px) {
				.static-row .col-md-8 { width: 66.6667% !important; }
				.static-row .col-md-4 { width: 33.3333% !important; }
				.static-row .col-md-12 { width: 100% !important; }
			}
			@media (max-width: 756px) {
				.static-row { flex-direction: column; }
				.card { max-width: 100%; margin: 0 auto; }
				.static-row .col-sm-8, .static-row .col-sm-4, .static-row .col-sm-12 { width: 100%; }
			}
		</style>
		<?php
		echo '<div id="wpbs-worker-overlay">'
			. '<div id="wpbs-worker-modal" role="dialog" aria-modal="true">'
			. '<div class="wpbs-modal-header">'
			. '<strong>Queue Worker</strong>'
			. '<button type="button" class="button" id="wpbs-worker-close">Close</button>'
			. '</div>'
			. '<div class="wpbs-modal-body">'
			. '<div id="wpbs-worker-status" class="wpbs-status">Starting…</div>'
			. '<div id="wpbs-worker-progress" class="wpbs-progress">'
			. '<div id="wpbs-worker-progress-bar" class="wpbs-progress-bar"></div>'
			. '</div>'
			. '<div class="wpbs-stats">'
			. '<div><strong>Complete:</strong> <span id="wpbs-worker-done">0</span></div>'
			. '<div><strong>Processing:</strong> <span id="wpbs-worker-processing">0</span></div>'
			. '<div><strong>Pending:</strong> <span id="wpbs-worker-pending">0</span></div>'
			. '<div><strong>Failed:</strong> <span id="wpbs-worker-failed">0</span></div>'
			. '</div>'
			. '<div class="wpbs-actions">'
			. '<button type="button" class="button" id="wpbs-worker-stop">Stop</button>'
			. '<span class="wpbs-note">Updates every second. Stop/Close does not cancel jobs; queue continues normally.</span>'
			. '</div>'
			. '</div>'
			. '</div>'
			. '</div>';

		// Simple modal (no dependencies) for deduplicate all.
		echo '<div id="wpbs-dedupe-overlay">'
			. '<div id="wpbs-dedupe-modal" role="dialog" aria-modal="true">'
			. '<div class="wpbs-modal-header">'
			. '<strong>Deduplicate Boats</strong>'
			. '<button type="button" class="button" id="wpbs-dedupe-close">Close</button>'
			. '</div>'
			. '<div class="wpbs-modal-body">'
			. '<div id="wpbs-dedupe-status" class="wpbs-status">Starting…</div>'
			. '<div id="wpbs-dedupe-progress" class="wpbs-progress">'
			. '<div id="wpbs-dedupe-progress-bar" class="wpbs-progress-bar" style="background:#b32d2e;"></div>'
			. '</div>'
			. '<div class="wpbs-stats">'
			. '<div><strong>Groups:</strong> <span id="wpbs-dedupe-processed">0</span> / <span id="wpbs-dedupe-total">0</span></div>'
			. '<div><strong>Posts deleted:</strong> <span id="wpbs-dedupe-deleted">0</span></div>'
			. '<div><strong>Images moved:</strong> <span id="wpbs-dedupe-reparented">0</span></div>'
			. '</div>'
			. '<div class="wpbs-actions">'
			. '<button type="button" class="button" id="wpbs-dedupe-stop">Stop</button>'
			. '<span class="wpbs-note">Updates every second.</span>'
			. '</div>'
			. '</div>'
			. '</div>'
			. '</div>';

		echo '<h2 style="margin-top:18px;">Last run (debug)</h2>';
		echo '<pre style="background:#fff;padding:12px;max-width:100%;overflow:auto;">' . esc_html(print_r($run, true)) . '</pre>';

		echo '</div>';
	}

	private function get_dashboard_data()
	{
		global $wpdb;

		// Post counts by status.
		$post_counts = array(
			'total' => 0,
			'publish' => 0,
			'draft' => 0,
			'private' => 0,
			'pending' => 0,
			'trash' => 0,
		);
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_status, COUNT(*) AS c FROM {$wpdb->posts} WHERE post_type=%s GROUP BY post_status",
				WPBS_POST_TYPE
			),
			ARRAY_A
		);
		foreach ($rows as $r) {
			$st = (string)$r['post_status'];
			$c = (int)$r['c'];
			if (!isset($post_counts[$st])) {
				$post_counts[$st] = 0;
			}
			$post_counts[$st] += $c;
			$post_counts['total'] += $c;
		}

		// Pending update marks.
		$pending_update = (int)$wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE p.post_type=%s AND pm.meta_key=%s AND pm.meta_value=%s",
				WPBS_POST_TYPE,
				'_wpbs_force_update',
				'1'
			)
		);

		// Queue counts.
		$queue_table = WPBS_DB::table_queue();
		$queue_counts = array(
			'pending' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status='pending'"),
			'processing' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status='processing'"),
			'done' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status='done'"),
			'failed' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status='failed'"),
		);

		// Queue hourly overview (last 24 hours): how many jobs were updated each hour into each status.
		$hours = 24;
		$end_ts = current_time('timestamp');
		$start_ts = $end_ts - (($hours - 1) * HOUR_IN_SECONDS);
		$labels_hours = array();
		$keys_hours = array();
		for ($i = 0; $i < $hours; $i++) {
			$ts = $start_ts + ($i * HOUR_IN_SECONDS);
			$keys_hours[] = wp_date('Y-m-d H:00:00', $ts);
			$labels_hours[] = wp_date('m-d H:00', $ts);
		}

		$hourly_map = array(
			'pending' => array(),
			'processing' => array(),
			'done' => array(),
			'failed' => array(),
		);
		$since_mysql = wp_date('Y-m-d H:00:00', $start_ts);
		$hour_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT(updated_at, '%%Y-%%m-%%d %%H:00:00') AS h, status, COUNT(*) AS c
				 FROM {$queue_table}
				 WHERE updated_at >= %s
				   AND status IN ('pending','processing','done','failed')
				 GROUP BY h, status
				 ORDER BY h ASC",
				$since_mysql
			),
			ARRAY_A
		);
		if (is_array($hour_rows)) {
			foreach ($hour_rows as $hr) {
				$h = isset($hr['h']) ? (string)$hr['h'] : '';
				$st = isset($hr['status']) ? (string)$hr['status'] : '';
				$c = isset($hr['c']) ? (int)$hr['c'] : 0;
				if ($h !== '' && isset($hourly_map[$st])) {
					$hourly_map[$st][$h] = $c;
				}
			}
		}
		$queue_hourly = array(
			'labels' => $labels_hours,
			'pending' => array(),
			'processing' => array(),
			'done' => array(),
			'failed' => array(),
		);
		foreach ($keys_hours as $hk) {
			$queue_hourly['pending'][] = isset($hourly_map['pending'][$hk]) ? (int)$hourly_map['pending'][$hk] : 0;
			$queue_hourly['processing'][] = isset($hourly_map['processing'][$hk]) ? (int)$hourly_map['processing'][$hk] : 0;
			$queue_hourly['done'][] = isset($hourly_map['done'][$hk]) ? (int)$hourly_map['done'][$hk] : 0;
			$queue_hourly['failed'][] = isset($hourly_map['failed'][$hk]) ? (int)$hourly_map['failed'][$hk] : 0;
		}

		$metrics = get_option('wpbs_queue_metrics', array());
		if (!is_array($metrics)) {
			$metrics = array();
		}
		$avg_jobs_per_sec = isset($metrics['avg_jobs_per_sec']) ? (float)$metrics['avg_jobs_per_sec'] : 0.0;
		$remaining_jobs = max(0, (int)$queue_counts['pending'] + (int)$queue_counts['processing']);
		$eta_seconds = 0;
		if ($remaining_jobs > 0 && $avg_jobs_per_sec > 0) {
			$eta_seconds = (int)ceil($remaining_jobs / $avg_jobs_per_sec);
		} elseif ($remaining_jobs > 0) {
			$settings = WPBS_Utils::get_settings();
			$batch = isset($settings['processor_batch_size']) ? max(1, (int)$settings['processor_batch_size']) : 10;
			$delay = isset($settings['processor_reschedule_seconds']) ? max(1, (int)$settings['processor_reschedule_seconds']) : 10;
			$runs_needed = (int)ceil($remaining_jobs / $batch);
			$eta_seconds = $runs_needed * $delay;
		}

		// Status counts + last sync + series from custom boats table.
		$boats_table = WPBS_DB::table_boats();
		$active = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$boats_table} WHERE sales_status='Active'");
		$total_rows = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$boats_table}");
		$sold = max(0, $total_rows - $active);
		$last_sync_at = $wpdb->get_var("SELECT MAX(last_sync_at) FROM {$boats_table}");
		$last_sync_at = $last_sync_at ? (string)$last_sync_at : '';

		// Sync activity: last 7 days hourly (168 hours)
		$sync_hours = 7 * 24; // 168 hours = 7 days
		$sync_end_ts = current_time('timestamp');
		$sync_start_ts = $sync_end_ts - (($sync_hours - 1) * HOUR_IN_SECONDS);
		$labels = array();
		$values = array();
		$sync_keys = array();
		
		for ($i = 0; $i < $sync_hours; $i++) {
			$ts = $sync_start_ts + ($i * HOUR_IN_SECONDS);
			$sync_keys[] = wp_date('Y-m-d H:00:00', $ts);
			$labels[] = wp_date('m-d H:00', $ts);
		}
		
		$sync_since_mysql = wp_date('Y-m-d H:00:00', $sync_start_ts);
		$sync_map = array();
		$series_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT(last_sync_at, '%%Y-%%m-%%d %%H:00:00') AS h, COUNT(*) AS c FROM {$boats_table} WHERE last_sync_at IS NOT NULL AND last_sync_at >= %s GROUP BY h ORDER BY h ASC",
				$sync_since_mysql
			),
			ARRAY_A
		);
		foreach ($series_rows as $sr) {
			$sync_map[(string)$sr['h']] = (int)$sr['c'];
		}
		foreach ($sync_keys as $sk) {
			$values[] = isset($sync_map[$sk]) ? (int)$sync_map[$sk] : 0;
		}

		return array(
			'postCounts' => $post_counts,
			'pendingUpdateCount' => $pending_update,
			'queueCounts' => $queue_counts,
			'queueHourly' => $queue_hourly,
			'queueMetrics' => $metrics,
			'queueEtaSeconds' => $eta_seconds,
			'statusCounts' => array('active' => $active, 'sold' => $sold),
			'lastSyncAt' => $last_sync_at,
			'syncSeries' => array('labels' => $labels, 'values' => $values),
		);
	}

	private function format_duration($seconds)
	{
		$seconds = max(0, (int)$seconds);
		$h = (int)floor($seconds / 3600);
		$m = (int)floor(($seconds % 3600) / 60);
		$s = (int)($seconds % 60);
		if ($h > 0) {
			return sprintf('%dh %dm', $h, $m);
		}
		if ($m > 0) {
			return sprintf('%dm %ds', $m, $s);
		}
		return sprintf('%ds', $s);
	}

	public function render_settings()
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$settings = WPBS_Utils::get_settings();

		echo '<div class="wrap">';
		echo '<h1>Boat Sync Settings</h1>';
		echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
		echo '<input type="hidden" name="action" value="wpbs_save_settings" />';
		wp_nonce_field('wpbs_save_settings');

		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row"><label for="api_key">API Key</label></th><td><input name="api_key" id="api_key" type="password" class="regular-text" value="' . esc_attr($settings['api_key']) . '" autocomplete="new-password" /></td></tr>';
		echo '<tr><th scope="row"><label for="party_id">PartyId</label></th><td><input name="party_id" id="party_id" type="text" class="regular-text" value="' . esc_attr($settings['party_id']) . '" /></td></tr>';
		echo '<tr><th scope="row"><label for="auto_sync_frequency">Auto sync</label></th><td><select name="auto_sync_frequency" id="auto_sync_frequency">';
		$freq = isset($settings['auto_sync_frequency']) ? (string)$settings['auto_sync_frequency'] : 'off';
		echo '<option value="off" ' . selected($freq, 'off', false) . '>Off</option>';
		echo '<option value="hourly" ' . selected($freq, 'hourly', false) . '>Hourly</option>';
		echo '<option value="daily" ' . selected($freq, 'daily', false) . '>Daily</option>';
		echo '</select></td></tr>';
		echo '<tr><th scope="row"><label for="rows_per_page">Rows per page</label></th><td><input name="rows_per_page" id="rows_per_page" type="number" min="1" max="100" value="' . esc_attr($settings['rows_per_page']) . '" /> <span style="opacity:.75;">(Boats.com API max 100)</span></td></tr>';
		echo '<tr><th scope="row"><label for="delete_after_days">Delete after (days)</label></th><td><input name="delete_after_days" id="delete_after_days" type="number" min="1" max="365" value="' . esc_attr($settings['delete_after_days']) . '" /></td></tr>';
		echo '<tr><th scope="row">Download images</th><td><label><input type="checkbox" name="download_images" value="1" ' . checked(!empty($settings['download_images']), true, false) . ' /> Enable</label></td></tr>';
		echo '<tr><th scope="row"><label for="max_images_per_boat">Max images per boat</label></th><td><input name="max_images_per_boat" id="max_images_per_boat" type="number" min="1" max="200" value="' . esc_attr($settings['max_images_per_boat']) . '" /></td></tr>';
		echo '<tr><th scope="row">Treat missing as sold</th><td><label><input type="checkbox" name="treat_missing_as_sold" value="1" ' . checked(!empty($settings['treat_missing_as_sold']), true, false) . ' /> Mark missing listings as sold and schedule delete</label></td></tr>';
		echo '<tr><th scope="row"><label for="processor_batch_size">Queue batch size</label></th><td><input name="processor_batch_size" id="processor_batch_size" type="number" min="1" max="100" value="' . esc_attr($settings['processor_batch_size']) . '" /> <p class="description">How many jobs to process per cron run (higher = faster, but heavier).</p></td></tr>';
		echo '<tr><th scope="row"><label for="processor_reschedule_seconds">Queue reschedule (seconds)</label></th><td><input name="processor_reschedule_seconds" id="processor_reschedule_seconds" type="number" min="0" max="300" value="' . esc_attr($settings['processor_reschedule_seconds']) . '" /> <p class="description">Delay before the next worker run while jobs are pending.</p></td></tr>';
		echo '<tr><th scope="row">Use plugin templates</th><td><label><input type="checkbox" name="use_default_templates" value="1" ' . checked(!empty($settings['use_default_templates']), true, false) . ' /> Use built-in single and archive templates</label><p class="description">When disabled, your theme\'s templates will be used instead.</p></td></tr>';
		echo '<tr><th scope="row"><label for="style_grid_columns">Grid columns</label></th><td><input name="style_grid_columns" id="style_grid_columns" type="number" min="1" max="6" value="' . esc_attr($settings['style_grid_columns']) . '" /></td></tr>';
		echo '<tr><th scope="row"><label for="style_grid_gap">Grid spacing (px)</label></th><td><input name="style_grid_gap" id="style_grid_gap" type="number" min="0" max="80" value="' . esc_attr($settings['style_grid_gap']) . '" /></td></tr>';
		echo '<tr><th scope="row"><label for="style_font_size">Font size (px)</label></th><td><input name="style_font_size" id="style_font_size" type="number" min="12" max="22" value="' . esc_attr($settings['style_font_size']) . '" /></td></tr>';
		echo '<tr><th scope="row"><label for="style_accent_color">Accent color</label></th><td><input name="style_accent_color" id="style_accent_color" type="text" class="regular-text" value="' . esc_attr($settings['style_accent_color']) . '" placeholder="#0b5fff" /></td></tr>';
		echo '</table>';

		echo '<h2 style="margin-top:30px;">Loan Calculator Defaults</h2>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row"><label for="loan_down_payment">Down Payment (%)</label></th><td><input name="loan_down_payment" id="loan_down_payment" type="number" min="0" max="100" step="0.1" value="' . esc_attr($settings['loan_down_payment']) . '" class="small-text" /> <span style="opacity:.75;">Default: 20%</span></td></tr>';
		echo '<tr><th scope="row"><label for="loan_interest_rate">Interest Rate (%)</label></th><td><input name="loan_interest_rate" id="loan_interest_rate" type="number" min="0" max="30" step="0.01" value="' . esc_attr($settings['loan_interest_rate']) . '" class="small-text" /> <span style="opacity:.75;">Annual APR. Default: 7.5%</span></td></tr>';
		echo '<tr><th scope="row"><label for="loan_term_years">Loan Term (Years)</label></th><td><select name="loan_term_years" id="loan_term_years">';
		$term = isset($settings['loan_term_years']) ? (int)$settings['loan_term_years'] : 1;
		foreach (array(1, 2, 3, 5, 7, 10, 12, 15, 20, 25, 30) as $y) {
			echo '<option value="' . $y . '" ' . selected($term, $y, false) . '>' . $y . ' year' . ($y > 1 ? 's' : '') . '</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';

		submit_button('Save Settings');
		echo '</form>';

		// One-off retrofit button: assign brand terms from existing wpbs_make/_exact meta.
		echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:18px;">';
		echo '<input type="hidden" name="action" value="wpbs_retrofit_brands" />';
		wp_nonce_field('wpbs_retrofit_brands');
		echo '<p><strong>Retrofit existing boats:</strong> Assign `brand` terms from the existing <code>wpbs_make_exact</code> / <code>wpbs_make</code> post meta. This will create missing brand terms and attach them to boats.</p>';
		echo '<p><button type="submit" class="button button-secondary">Run retrofit now</button> <span style="margin-left:10px; color:#666;">You will be redirected back with a summary.</span></p>';
		echo '</form>';

		// Dry-run button: report what would change without making modifications
		echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:8px;">';
		echo '<input type="hidden" name="action" value="wpbs_retrofit_brands_dryrun" />';
		wp_nonce_field('wpbs_retrofit_brands_dryrun');
		echo '<p><strong>Dry-run:</strong> Simulate assigning `brand` terms and report counts (no changes will be made).</p>';
		echo '<p><button type="submit" class="button">Run dry-run</button> <span style="margin-left:10px; color:#666;">You will be redirected back with a summary of what would change.</span></p>';
		echo '</form>';

		echo '</div>';
	}

	/**
	 * Handle admin POST to retrofit brand terms for existing boat posts.
	 */
	public function handle_retrofit_brands()
	{
		if (!current_user_can('manage_options')) {
			wp_die('Forbidden', '', array('response' => 403));
		}
		check_admin_referer('wpbs_retrofit_brands');

		$assigned = 0;
		$skipped = 0;
		$created_terms = 0;

		$paged = 1;
		$per_page = 200;

		while (true) {
			$query = new WP_Query(array(
				'post_type' => WPBS_POST_TYPE,
				'post_status' => 'any',
				'posts_per_page' => $per_page,
				'paged' => $paged,
				'fields' => 'ids',
			));

			if (empty($query->posts)) break;

			foreach ($query->posts as $post_id) {
				$make = (string)get_post_meta($post_id, 'wpbs_make_exact', true);
				if ($make === '') {
					$make = (string)get_post_meta($post_id, 'wpbs_make', true);
				}
				$make = trim($make);
				if ($make === '') {
					$skipped++;
					continue;
				}

				$slug = sanitize_title($make);
				$term = get_term_by('slug', $slug, 'brand');
				if (!$term) {
					$res = wp_insert_term($make, 'brand', array('slug' => $slug));
					if (!is_wp_error($res)) {
						$created_terms++;
						$term_id = is_array($res) && isset($res['term_id']) ? (int)$res['term_id'] : 0;
					} else {
						// If term creation failed, skip
						$skipped++;
						continue;
					}
				} else {
					$term_id = (int)$term->term_id;
				}

				// Assign term by slug (non-hierarchical)
				wp_set_object_terms($post_id, $slug, 'brand', false);
				$assigned++;
			}

			$paged++;
			// Allow other processes to run between batches
			if (function_exists('wp_suspend_cache_invalidation')) {
				wp_suspend_cache_invalidation(true);
			}
		}

		// Redirect back to settings with summary
		$redirect = add_query_arg(array('page' => 'wpbs-settings', 'retro_assigned' => $assigned, 'retro_skipped' => $skipped, 'retro_created' => $created_terms), admin_url('admin.php'));
		wp_safe_redirect($redirect);
		exit;
	}

	/**
	 * Dry-run: simulate retrofit and report counts without making changes.
	 */
	public function handle_retrofit_brands_dryrun()
	{
		if (!current_user_can('manage_options')) {
			wp_die('Forbidden', '', array('response' => 403));
		}
		check_admin_referer('wpbs_retrofit_brands_dryrun');

		$assigned = 0; // would-be assignments
		$skipped = 0; // posts skipped (no make)
		$would_create = 0; // terms that would be created

		$paged = 1;
		$per_page = 200;

		while (true) {
			$query = new WP_Query(array(
				'post_type' => WPBS_POST_TYPE,
				'post_status' => 'any',
				'posts_per_page' => $per_page,
				'paged' => $paged,
				'fields' => 'ids',
			));

			if (empty($query->posts)) break;

			foreach ($query->posts as $post_id) {
				$make = (string)get_post_meta($post_id, 'wpbs_make_exact', true);
				if ($make === '') {
					$make = (string)get_post_meta($post_id, 'wpbs_make', true);
				}
				$make = trim($make);
				if ($make === '') {
					$skipped++;
					continue;
				}

				$slug = sanitize_title($make);
				$term = get_term_by('slug', $slug, 'brand');
				if (!$term) {
					$would_create++;
				}
				// In dry-run we count as would-be assignment regardless of existing term
				$assigned++;
			}

			$paged++;
		}

		$redirect = add_query_arg(array('page' => 'wpbs-settings', 'dry_assigned' => $assigned, 'dry_skipped' => $skipped, 'dry_would_create' => $would_create), admin_url('admin.php'));
		wp_safe_redirect($redirect);
		exit;
	}

	public function render_shortcode_builder()
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>Shortcode Builder</h1>';
		echo '<p style="color:#666;">Select a shortcode type, configure options, and copy the generated shortcode to use in your posts, pages, or page builders.</p>';

		// Shortcode type selector with categories
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row"><label for="wpbs_sc_type">Shortcode Type</label></th><td>';
		echo '<select id="wpbs_sc_type" style="min-width:280px;">';
		echo '<optgroup label="Layout">';
		echo '<option value="grid">Boat Grid - Display multiple boats in grid</option>';
		echo '<option value="single">Single Boat - Full boat display</option>';
		echo '</optgroup>';
		echo '<optgroup label="Boat Details (Accordion/Tabs)">';
		echo '<option value="accordion">Accordion Details - Collapsible boat specs</option>';
		echo '<option value="tabs">Tabs - Tabbed boat specs (legacy)</option>';
		echo '<option value="tab_description">Tab: Description Only</option>';
		echo '<option value="tab_measurements">Tab: Measurements Only</option>';
		echo '<option value="tab_propulsion">Tab: Propulsion Only</option>';
		echo '<option value="tab_features">Tab: Features Only</option>';
		echo '</optgroup>';
		echo '<optgroup label="Media">';
		echo '<option value="slider">Slider - Image slider (4 images)</option>';
		echo '<option value="gallery">Gallery - Full gallery with lightbox</option>';
		echo '</optgroup>';
		echo '<optgroup label="Boat Info">';
		echo '<option value="quick_specs">Quick Specs - Key specifications grid</option>';
		echo '<option value="overview">Overview - Boat overview section</option>';
		echo '<option value="price">Price - Display boat price</option>';
		echo '<option value="price_card">Price Card - Price with contact button</option>';
		echo '<option value="location">Location - Boat location info</option>';
		echo '<option value="loan_calculator">Loan Calculator - Monthly payment calculator</option>';
		echo '</optgroup>';
		echo '<optgroup label="Dealer">';
		echo '<option value="dealer_card">Dealer Card - Dealer contact info</option>';
		echo '<option value="more_boats">More Boats - Other boats from dealer</option>';
		echo '</optgroup>';
		echo '</select>';
		echo '</td></tr>';
		echo '</table>';

		// Description area
		echo '<div id="wpbs_sc_desc" style="background:#f0f6fc;border-left:4px solid #2271b1;padding:12px 16px;margin:16px 0;"></div>';

		// Options panels for each shortcode type
		echo '<div class="wpbs-sc-options" style="background:#fff;border:1px solid #c3c4c7;padding:16px;margin:16px 0;">';

		// Grid options
		echo '<div id="wpbs_sc_opt_grid" class="wpbs-sc-panel">';
		echo '<h3 style="margin-top:0;">Grid Options</h3>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th><label for="wpbs_sc_ppp">Posts per page</label></th><td><input id="wpbs_sc_ppp" type="number" value="12" min="1" max="100" class="small-text" /></td></tr>';
		echo '<tr><th><label for="wpbs_sc_cols">Columns</label></th><td><input id="wpbs_sc_cols" type="number" value="3" min="1" max="6" class="small-text" /></td></tr>';
		echo '<tr><th><label for="wpbs_sc_filter">Enable Filter Bar</label></th><td><select id="wpbs_sc_filter"><option value="false">No</option><option value="true">Yes</option></select><p class="description">Show filter bar with category, price, year, length range sliders and AJAX filtering.</p></td></tr>';
		echo '<tr id="wpbs_sc_filter_pos_row" style="display:none;"><th><label for="wpbs_sc_filter_pos">Filter Position</label></th><td><select id="wpbs_sc_filter_pos"><option value="top">Top (default)</option><option value="left">Left Sidebar</option><option value="right">Right Sidebar</option></select><p class="description">Position of the filter bar relative to the grid.</p></td></tr>';
		echo '<tr><th><label for="wpbs_sc_orderby">Default Order</label></th><td><select id="wpbs_sc_orderby"><option value="date">Date (newest first)</option><option value="price_low">Price: Low to High</option><option value="price_high">Price: High to Low</option><option value="year">Year: Newest</option></select></td></tr>';
		echo '</table>';
		echo '</div>';

		// Single/Element options (post_id or id)
		echo '<div id="wpbs_sc_opt_single" class="wpbs-sc-panel" style="display:none;">';
		echo '<h3 style="margin-top:0;">Boat Selection</h3>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th><label for="wpbs_sc_doc">Document ID</label></th><td><input id="wpbs_sc_doc" type="text" placeholder="e.g. 9963690" class="regular-text" /><p class="description">The boats.com document ID. Leave empty to use current boat (in single boat pages or loops).</p></td></tr>';
		echo '<tr><th><label for="wpbs_sc_postid">Or Post ID</label></th><td><input id="wpbs_sc_postid" type="number" placeholder="e.g. 123" class="small-text" /><p class="description">WordPress post ID of the boat. Takes priority over Document ID.</p></td></tr>';
		echo '</table>';
		echo '</div>';

		// Slider options
		echo '<div id="wpbs_sc_opt_slider" class="wpbs-sc-panel" style="display:none;">';
		echo '<h3 style="margin-top:0;">Slider Options</h3>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th><label for="wpbs_sc_doc_slider">Document ID</label></th><td><input id="wpbs_sc_doc_slider" type="text" placeholder="e.g. 9963690" class="regular-text" /></td></tr>';
		echo '<tr><th><label for="wpbs_sc_postid_slider">Or Post ID</label></th><td><input id="wpbs_sc_postid_slider" type="number" placeholder="e.g. 123" class="small-text" /></td></tr>';
		echo '<tr><th><label for="wpbs_sc_max">Max images</label></th><td><input id="wpbs_sc_max" type="number" value="4" min="1" max="20" class="small-text" /></td></tr>';
		echo '</table>';
		echo '</div>';

		// Price options (now uses basic panel)
		echo '<div id="wpbs_sc_opt_price" class="wpbs-sc-panel" style="display:none;">';
		echo '<h3 style="margin-top:0;">Price Options</h3>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th><label for="wpbs_sc_doc_price">Document ID</label></th><td><input id="wpbs_sc_doc_price" type="text" placeholder="e.g. 9963690" class="regular-text" /></td></tr>';
		echo '<tr><th><label for="wpbs_sc_postid_price">Or Post ID</label></th><td><input id="wpbs_sc_postid_price" type="number" placeholder="e.g. 123" class="small-text" /></td></tr>';
		echo '</table>';
		echo '</div>';

		// More boats options
		echo '<div id="wpbs_sc_opt_more" class="wpbs-sc-panel" style="display:none;">';
		echo '<h3 style="margin-top:0;">More Boats Options</h3>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th><label for="wpbs_sc_doc_more">Document ID</label></th><td><input id="wpbs_sc_doc_more" type="text" placeholder="e.g. 9963690" class="regular-text" /></td></tr>';
		echo '<tr><th><label for="wpbs_sc_postid_more">Or Post ID</label></th><td><input id="wpbs_sc_postid_more" type="number" placeholder="e.g. 123" class="small-text" /></td></tr>';
		echo '<tr><th><label for="wpbs_sc_limit">Limit</label></th><td><input id="wpbs_sc_limit" type="number" value="4" min="1" max="20" class="small-text" /><p class="description">Maximum number of boats to display.</p></td></tr>';
		echo '</table>';
		echo '</div>';

		// No options panel (for shortcodes with just id/post_id)
		echo '<div id="wpbs_sc_opt_basic" class="wpbs-sc-panel" style="display:none;">';
		echo '<h3 style="margin-top:0;">Boat Selection</h3>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th><label for="wpbs_sc_doc_basic">Document ID</label></th><td><input id="wpbs_sc_doc_basic" type="text" placeholder="e.g. 9963690" class="regular-text" /><p class="description">Leave empty to use current boat context.</p></td></tr>';
		echo '<tr><th><label for="wpbs_sc_postid_basic">Or Post ID</label></th><td><input id="wpbs_sc_postid_basic" type="number" placeholder="e.g. 123" class="small-text" /></td></tr>';
		echo '</table>';
		echo '</div>';

		// Loan Calculator options panel
		echo '<div id="wpbs_sc_opt_loan" class="wpbs-sc-panel" style="display:none;">';
		echo '<h3 style="margin-top:0;">Loan Calculator Options</h3>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th><label for="wpbs_sc_doc_loan">Document ID</label></th><td><input id="wpbs_sc_doc_loan" type="text" placeholder="e.g. 9963690" class="regular-text" /><p class="description">Leave empty to use current boat context.</p></td></tr>';
		echo '<tr><th><label for="wpbs_sc_postid_loan">Or Post ID</label></th><td><input id="wpbs_sc_postid_loan" type="number" placeholder="e.g. 123" class="small-text" /></td></tr>';
		echo '<tr><th><label for="wpbs_sc_loan_price">Override Price</label></th><td><input id="wpbs_sc_loan_price" type="text" placeholder="e.g. 150000" class="regular-text" /><p class="description">Custom purchase price. Leave empty to use boat price.</p></td></tr>';
		echo '<tr><th><label for="wpbs_sc_loan_down">Down Payment</label></th><td><input id="wpbs_sc_loan_down" type="text" value="20%" class="small-text" /><p class="description">Default down payment. Use % or fixed amount (e.g. 20% or 10000).</p></td></tr>';
		echo '<tr><th><label for="wpbs_sc_loan_term">Loan Term (Years)</label></th><td><select id="wpbs_sc_loan_term"><option value="5">5</option><option value="7">7</option><option value="10">10</option><option value="12">12</option><option value="15" selected>15</option><option value="20">20</option><option value="25">25</option><option value="30">30</option></select></td></tr>';
		echo '<tr><th><label for="wpbs_sc_loan_rate">Interest Rate (%)</label></th><td><input id="wpbs_sc_loan_rate" type="text" value="7.5" class="small-text" /><p class="description">Default annual interest rate.</p></td></tr>';
		echo '<tr><th><label for="wpbs_sc_loan_title">Calculator Title</label></th><td><input id="wpbs_sc_loan_title" type="text" value="Loan Payment Calculator" class="regular-text" /></td></tr>';
		echo '</table>';
		echo '</div>';

		echo '</div>'; // .wpbs-sc-options

		// Output
		echo '<h3>Generated Shortcode</h3>';
		echo '<p><textarea id="wpbs_sc_out" class="large-text code" rows="2" readonly style="font-family:monospace;font-size:14px;"></textarea></p>';
		echo '<p><button class="button button-primary" id="wpbs_sc_copy" type="button">📋 Copy to Clipboard</button> <span id="wpbs_sc_copied" style="color:#00a32a;display:none;margin-left:8px;">Copied!</span></p>';

		// Shortcode reference
		echo '<div style="margin-top:32px;padding-top:24px;border-top:1px solid #c3c4c7;">';
		echo '<h2>Shortcode Reference</h2>';
		echo '<table class="widefat striped" style="max-width:900px;">';
		echo '<thead><tr><th>Shortcode</th><th>Description</th><th>Key Attributes</th></tr></thead>';
		echo '<tbody>';
		echo '<tr><td><code>[wpbs_boat_grid]</code></td><td>Display boats in a responsive grid layout</td><td>posts_per_page, columns, filter, filter_position, orderby</td></tr>';
		echo '<tr><td><code>[wpbs_boat_single]</code></td><td>Full single boat display with all sections</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_accordion]</code></td><td>Accordion-style boat details (collapsible sections)</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_tabs]</code></td><td>Tabbed boat details interface</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_slider]</code></td><td>Image slider with navigation arrows</td><td>id, post_id, max</td></tr>';
		echo '<tr><td><code>[wpbs_gallery]</code></td><td>Full image gallery with lightbox</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_quick_specs]</code></td><td>Key specifications in grid format</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_overview]</code></td><td>Boat overview section</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_price]</code></td><td>Display boat price</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_price_card]</code></td><td>Price card with contact seller button</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_location]</code></td><td>Boat location information</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_dealer_card]</code></td><td>Dealer contact card</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_more_boats]</code></td><td>Other boats from the same dealer</td><td>id, post_id, limit</td></tr>';
		echo '<tr><td><code>[wpbs_tab_description]</code></td><td>Description section only</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_tab_measurements]</code></td><td>Measurements section only</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_tab_propulsion]</code></td><td>Propulsion/engine section only</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_tab_features]</code></td><td>Features section only</td><td>id, post_id</td></tr>';
		echo '<tr><td><code>[wpbs_loan_calculator]</code></td><td>Loan payment calculator with amortization formula</td><td>id, post_id, price, down, term, rate, title</td></tr>';
		echo '</tbody></table>';
		echo '</div>';

		echo '<script>
(function(){
	var descriptions = {
		grid: "Display multiple boats in a responsive grid layout. Enable filter bar for AJAX-powered filtering with range sliders for Price, Year, and Length.",
		single: "Full single boat display including gallery, specs, details, and dealer info. Use on dedicated boat pages.",
		accordion: "Modern accordion-style boat details with collapsible sections for Description, Measurements, Propulsion, Features, Location, and Disclaimer.",
		tabs: "Classic tabbed interface for boat specifications. Shows Description, Measurements, Propulsion, and Features tabs.",
		tab_description: "Display only the boat description content.",
		tab_measurements: "Display only the measurements/dimensions section.",
		tab_propulsion: "Display only the propulsion/engine specifications.",
		tab_features: "Display only the boat features and equipment.",
		slider: "Compact image slider with navigation arrows. Great for cards and previews.",
		gallery: "Full image gallery with thumbnail strip and lightbox. Click images to view full-size.",
		quick_specs: "Display key specifications (Year, Length, Engine, Fuel, etc.) in a compact grid.",
		overview: "Boat overview section with main details.",
		price: "Display the boat price.",
		price_card: "Price card with title, location, and Contact Seller button.",
		location: "Display boat location (city, state, country).",
		dealer_card: "Dealer contact card with name, phone, and email.",
		more_boats: "Show other boats from the same dealer. Great for cross-selling.",
		loan_calculator: "Interactive loan payment calculator. Calculates monthly payments using standard amortization formula with configurable down payment, term, and interest rate."
	};

	var panelMap = {
		grid: "grid",
		single: "single",
		accordion: "basic",
		tabs: "basic",
		tab_description: "basic",
		tab_measurements: "basic",
		tab_propulsion: "basic",
		tab_features: "basic",
		slider: "slider",
		gallery: "basic",
		quick_specs: "basic",
		overview: "basic",
		price: "price",
		price_card: "basic",
		location: "basic",
		dealer_card: "basic",
		more_boats: "more",
		loan_calculator: "loan"
	};

	var shortcodeNames = {
		grid: "wpbs_boat_grid",
		single: "wpbs_boat_single",
		accordion: "wpbs_accordion",
		tabs: "wpbs_tabs",
		tab_description: "wpbs_tab_description",
		tab_measurements: "wpbs_tab_measurements",
		tab_propulsion: "wpbs_tab_propulsion",
		tab_features: "wpbs_tab_features",
		slider: "wpbs_slider",
		gallery: "wpbs_gallery",
		quick_specs: "wpbs_quick_specs",
		overview: "wpbs_overview",
		price: "wpbs_price",
		price_card: "wpbs_price_card",
		location: "wpbs_location",
		dealer_card: "wpbs_dealer_card",
		more_boats: "wpbs_more_boats",
		loan_calculator: "wpbs_loan_calculator"
	};

	function hideAllPanels(){
		var panels = document.querySelectorAll(".wpbs-sc-panel");
		panels.forEach(function(p){ p.style.display = "none"; });
	}

	function showPanel(name){
		hideAllPanels();
		var panel = document.getElementById("wpbs_sc_opt_" + name);
		if(panel) panel.style.display = "block";
	}

	function build(){
		var type = document.getElementById("wpbs_sc_type").value;
		var scName = shortcodeNames[type] || "wpbs_boat_grid";
		var attrs = [];
		var panel = panelMap[type] || "basic";

		// Update description
		document.getElementById("wpbs_sc_desc").innerHTML = "<strong>" + scName + "</strong>: " + (descriptions[type] || "");

		// Show correct panel
		showPanel(panel);

		// Build attributes based on panel
		if(panel === "grid"){
			var ppp = document.getElementById("wpbs_sc_ppp").value;
			var cols = document.getElementById("wpbs_sc_cols").value;
			var filter = document.getElementById("wpbs_sc_filter").value;
			var filterPos = document.getElementById("wpbs_sc_filter_pos").value;
			var orderby = document.getElementById("wpbs_sc_orderby").value;
			
			if(ppp && ppp !== "12") attrs.push("posts_per_page=\"" + ppp + "\"");
			if(cols && cols !== "3") attrs.push("columns=\"" + cols + "\"");
			if(filter === "true") {
				attrs.push("filter=\"true\"");
				if(filterPos && filterPos !== "top") attrs.push("filter_position=\"" + filterPos + "\"");
			}
			if(orderby && orderby !== "date") attrs.push("orderby=\"" + orderby + "\"");
			
			// Show/hide filter position row
			document.getElementById("wpbs_sc_filter_pos_row").style.display = filter === "true" ? "" : "none";
		}
		else if(panel === "single"){
			var doc = document.getElementById("wpbs_sc_doc").value.trim();
			var postid = document.getElementById("wpbs_sc_postid").value.trim();
			if(postid) attrs.push("post_id=\"" + postid + "\"");
			else if(doc) attrs.push("id=\"" + doc + "\"");
		}
		else if(panel === "slider"){
			var doc = document.getElementById("wpbs_sc_doc_slider").value.trim();
			var postid = document.getElementById("wpbs_sc_postid_slider").value.trim();
			var max = document.getElementById("wpbs_sc_max").value;
			if(postid) attrs.push("post_id=\"" + postid + "\"");
			else if(doc) attrs.push("id=\"" + doc + "\"");
			if(max && max !== "4") attrs.push("max=\"" + max + "\"");
		}
		else if(panel === "price"){
			var doc = document.getElementById("wpbs_sc_doc_price").value.trim();
			var postid = document.getElementById("wpbs_sc_postid_price").value.trim();
			if(postid) attrs.push("post_id=\"" + postid + "\"");
			else if(doc) attrs.push("id=\"" + doc + "\"");
		}
		else if(panel === "more"){
			var doc = document.getElementById("wpbs_sc_doc_more").value.trim();
			var postid = document.getElementById("wpbs_sc_postid_more").value.trim();
			var limit = document.getElementById("wpbs_sc_limit").value;
			if(postid) attrs.push("post_id=\"" + postid + "\"");
			else if(doc) attrs.push("id=\"" + doc + "\"");
			if(limit && limit !== "4") attrs.push("limit=\"" + limit + "\"");
		}
		else if(panel === "basic"){
			var doc = document.getElementById("wpbs_sc_doc_basic").value.trim();
			var postid = document.getElementById("wpbs_sc_postid_basic").value.trim();
			if(postid) attrs.push("post_id=\"" + postid + "\"");
			else if(doc) attrs.push("id=\"" + doc + "\"");
		}
		else if(panel === "loan"){
			var doc = document.getElementById("wpbs_sc_doc_loan").value.trim();
			var postid = document.getElementById("wpbs_sc_postid_loan").value.trim();
			var price = document.getElementById("wpbs_sc_loan_price").value.trim();
			var down = document.getElementById("wpbs_sc_loan_down").value.trim();
			var term = document.getElementById("wpbs_sc_loan_term").value;
			var rate = document.getElementById("wpbs_sc_loan_rate").value.trim();
			var title = document.getElementById("wpbs_sc_loan_title").value.trim();
			if(postid) attrs.push("post_id=\"" + postid + "\"");
			else if(doc) attrs.push("id=\"" + doc + "\"");
			if(price) attrs.push("price=\"" + price + "\"");
			if(down && down !== "20%") attrs.push("down=\"" + down + "\"");
			if(term && term !== "15") attrs.push("term=\"" + term + "\"");
			if(rate && rate !== "7.5") attrs.push("rate=\"" + rate + "\"");
			if(title && title !== "Loan Payment Calculator") attrs.push("title=\"" + title + "\"");
		}

		var out = "[" + scName + (attrs.length ? " " + attrs.join(" ") : "") + "]";
		document.getElementById("wpbs_sc_out").value = out;
	}

	// Initial build
	build();

	// Event listeners
	document.getElementById("wpbs_sc_type").addEventListener("change", build);

	// All inputs trigger rebuild
	var inputs = document.querySelectorAll(".wpbs-sc-panel input, .wpbs-sc-panel select");
	inputs.forEach(function(el){
		el.addEventListener("input", build);
		el.addEventListener("change", build);
	});

	// Copy button
	document.getElementById("wpbs_sc_copy").addEventListener("click", function(){
		var el = document.getElementById("wpbs_sc_out");
		el.select();
		el.setSelectionRange(0, 99999);
		document.execCommand("copy");
		var copied = document.getElementById("wpbs_sc_copied");
		copied.style.display = "inline";
		setTimeout(function(){ copied.style.display = "none"; }, 2000);
	});
})();
</script>';

		echo '</div>';
	}

	public function handle_save_settings()
	{
		if (!current_user_can('manage_options')) {
			wp_die('Forbidden');
		}
		check_admin_referer('wpbs_save_settings');

		$rows = isset($_POST['rows_per_page']) ? (int)$_POST['rows_per_page'] : 100;
		$rows = max(1, min(100, $rows));

		$settings = array(
			'api_key' => isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '',
			'party_id' => isset($_POST['party_id']) ? sanitize_text_field(wp_unslash($_POST['party_id'])) : '',
			'auto_sync_frequency' => isset($_POST['auto_sync_frequency']) ? sanitize_text_field(wp_unslash($_POST['auto_sync_frequency'])) : 'off',
			'rows_per_page' => $rows,
			'delete_after_days' => isset($_POST['delete_after_days']) ? (int)$_POST['delete_after_days'] : 15,
			'download_images' => !empty($_POST['download_images']) ? 1 : 0,
			'max_images_per_boat' => isset($_POST['max_images_per_boat']) ? (int)$_POST['max_images_per_boat'] : 25,
			'treat_missing_as_sold' => !empty($_POST['treat_missing_as_sold']) ? 1 : 0,
			'processor_batch_size' => isset($_POST['processor_batch_size']) ? (int)$_POST['processor_batch_size'] : 10,
			'processor_reschedule_seconds' => isset($_POST['processor_reschedule_seconds']) ? (int)$_POST['processor_reschedule_seconds'] : 10,
			'use_default_templates' => !empty($_POST['use_default_templates']) ? 1 : 0,
			'style_grid_columns' => isset($_POST['style_grid_columns']) ? (int)$_POST['style_grid_columns'] : 3,
			'style_grid_gap' => isset($_POST['style_grid_gap']) ? (int)$_POST['style_grid_gap'] : 16,
			'style_font_size' => isset($_POST['style_font_size']) ? (int)$_POST['style_font_size'] : 16,
			'style_accent_color' => isset($_POST['style_accent_color']) ? sanitize_text_field(wp_unslash($_POST['style_accent_color'])) : '#0b5fff',
			'loan_down_payment' => isset($_POST['loan_down_payment']) ? (float)$_POST['loan_down_payment'] : 20,
			'loan_interest_rate' => isset($_POST['loan_interest_rate']) ? (float)$_POST['loan_interest_rate'] : 7.5,
			'loan_term_years' => isset($_POST['loan_term_years']) ? (int)$_POST['loan_term_years'] : 1,
		);

		WPBS_Utils::update_settings($settings);
		if (class_exists('WPBS_Plugin')) {
			WPBS_Plugin::reschedule_auto_sync($settings['auto_sync_frequency']);
		}

		wp_safe_redirect(admin_url('admin.php?page=wpbs-settings&updated=1'));
		exit;
	}

	private function get_queue_counts_simple()
	{
		global $wpdb;
		$queue_table = WPBS_DB::table_queue();
		return array(
			'done' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status='done'"),
			'pending' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status='pending'"),
			'processing' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status='processing'"),
			'failed' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status='failed'"),
		);
	}

	public function ajax_queue_worker_status()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Forbidden'), 403);
		}
		check_ajax_referer('wpbs_queue_worker_ajax', 'nonce');

		wp_send_json_success(array(
			'queueCounts' => $this->get_queue_counts_simple(),
		));
	}

	public function ajax_queue_worker_tick()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Forbidden'), 403);
		}
		check_ajax_referer('wpbs_queue_worker_ajax', 'nonce');

		$batch_size = isset($_POST['batchSize']) ? (int)$_POST['batchSize'] : 1;
		$batch_size = max(1, min(5, $batch_size));

		try {
			// Run a small batch to avoid timeouts.
			$processed = $this->sync->process_queue_batch($batch_size);

			wp_send_json_success(array(
				'queueCounts' => $this->get_queue_counts_simple(),
				'processed' => $processed,
			));
		} catch (Exception $e) {
			wp_send_json_error(array(
				'message' => 'Processing error: ' . $e->getMessage(),
				'queueCounts' => $this->get_queue_counts_simple(),
			), 500);
		}
	}

	public function handle_run_queue_now()
	{
		if (!current_user_can('manage_options')) {
			wp_die('Forbidden');
		}
		check_admin_referer('wpbs_run_queue_now');

		// Process a few batches quickly without timing out.
		$start = microtime(true);
		for ($i = 0; $i < 3; $i++) {
			$this->sync->process_queue();
			if ((microtime(true) - $start) > 8) {
				break;
			}
		}

		wp_safe_redirect(admin_url('admin.php?page=wpbs&queue=ran'));
		exit;
	}

	public function handle_manual_sync()
	{
		if (!current_user_can('manage_options')) {
			wp_die('Forbidden');
		}
		check_admin_referer('wpbs_manual_sync');

		$mode = isset($_POST['mode']) ? sanitize_text_field(wp_unslash($_POST['mode'])) : 'single';

		if ($mode === 'full') {
			$this->sync->enqueue_full_sync('manual');
			wp_safe_redirect(admin_url('admin.php?page=wpbs&sync=started'));
			exit;
		}

		$document_id = isset($_POST['document_id']) ? sanitize_text_field(wp_unslash($_POST['document_id'])) : '';
		$force = !empty($_POST['force']);

		$result = $this->sync->enqueue_sync_boat($document_id, $force);
		$ok = !is_wp_error($result);

		wp_safe_redirect(admin_url('admin.php?page=wpbs-manual&single=' . ($ok ? '1' : '0')));
		exit;
	}

	public function handle_sync_marked()
	{
		if (!current_user_can('manage_options')) {
			wp_die('Forbidden');
		}
		check_admin_referer('wpbs_sync_marked');

		$ids = get_posts(array(
			'post_type' => WPBS_POST_TYPE,
			'post_status' => 'any',
			'fields' => 'ids',
			'posts_per_page' => 200,
			'meta_key' => '_wpbs_force_update',
			'meta_value' => '1',
		));

		foreach ($ids as $post_id) {
			$document_id = get_post_meta($post_id, '_wpbs_document_id', true);
			if ($document_id) {
				$this->sync->enqueue_sync_boat($document_id, true);
				delete_post_meta($post_id, '_wpbs_force_update');
			}
		}

		wp_safe_redirect(admin_url('admin.php?page=wpbs-manual&marked=done'));
		exit;
	}
}
