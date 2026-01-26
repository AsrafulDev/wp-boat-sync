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
	}

	public function enqueue_admin_assets($hook)
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		if ($page !== 'wpbs') {
			return;
		}

		// Chart.js (local vendored) + dashboard renderer.
		wp_register_script('wpbs-chartjs', WPBS_PLUGIN_URL . 'assets/vendor/chartjs/chart.umd.min.js', array(), '4.4.1', true);
		wp_register_script('wpbs-admin-dashboard', WPBS_PLUGIN_URL . 'assets/js/wpbs-admin-dashboard.js', array('wpbs-chartjs'), WPBS_VERSION, true);

		$dash = $this->get_dashboard_data();
		wp_localize_script('wpbs-admin-dashboard', 'WPBS_DASH', $dash);
		wp_enqueue_script('wpbs-admin-dashboard');
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
		add_submenu_page('wpbs', __('Settings', 'wpbs'), __('Settings', 'wpbs'), 'manage_options', 'wpbs-settings', array($this, 'render_settings'));
		add_submenu_page('wpbs', __('Manual Sync', 'wpbs'), __('Manual Sync', 'wpbs'), 'manage_options', 'wpbs-manual', array($this, 'render_manual'));
		add_submenu_page('wpbs', __('Shortcode Builder', 'wpbs'), __('Shortcode Builder', 'wpbs'), 'manage_options', 'wpbs-shortcodes', array($this, 'render_shortcode_builder'));
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
		echo '<p><strong>Pending:</strong> ' . esc_html((string)$dash['queueCounts']['pending']) . '</p>';
		echo '<p><strong>Processing:</strong> ' . esc_html((string)$dash['queueCounts']['processing']) . '</p>';
		echo '<p><strong>Failed:</strong> ' . esc_html((string)$dash['queueCounts']['failed']) . '</p>';
		echo '<p><strong>Speed:</strong> ' . esc_html($speed_label) . '</p>';
		echo '<p><strong>ETA (estimate):</strong> ' . esc_html($eta_label) . '</p>';
		echo '</div>';
		echo '</div>';

		echo '<div style="display:grid;grid-template-columns: 2fr 1fr; gap:12px; align-items:stretch;">';
		echo '<div class="card" style="max-width:100%;">';
		echo '<h2 style="margin-top:0;">Sync activity (last 30 days)</h2>';
		echo '<div style="height:260px;"><canvas id="wpbsChartSync" aria-label="Sync activity chart" role="img"></canvas></div>';
		echo '</div>';
		echo '<div class="card">';
		echo '<h2 style="margin-top:0;">Status</h2>';
		echo '<div style="height:260px;"><canvas id="wpbsChartStatus" aria-label="Status chart" role="img"></canvas></div>';
		echo '</div>';
		echo '</div>';

		echo '<div style="display:grid;grid-template-columns: 1fr 1fr; gap:12px; align-items:stretch; margin-top:12px;">';
		echo '<div class="card">';
		echo '<h2 style="margin-top:0;">Queue overview</h2>';
		echo '<div style="height:260px;"><canvas id="wpbsChartQueue" aria-label="Queue chart" role="img"></canvas></div>';
		echo '</div>';
		echo '<div class="card">';
		echo '<h2 style="margin-top:0;">Actions</h2>';
		echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
		echo '<input type="hidden" name="action" value="wpbs_manual_sync" />';
		wp_nonce_field('wpbs_manual_sync');
		echo '<input type="hidden" name="mode" value="full" />';
		echo '<p><button class="button button-primary">Run Full Sync Now</button></p>';
		echo '</form>';
		echo '<p><a class="button" href="' . esc_url(admin_url('admin.php?page=wpbs-boats')) . '">Open Boats Table</a></p>';
		echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:10px;">';
		echo '<input type="hidden" name="action" value="wpbs_run_queue_now" />';
		wp_nonce_field('wpbs_run_queue_now');
		echo '<p><button class="button">Run Queue Worker Now</button> <span style="opacity:.75;">(processes a few jobs immediately)</span></p>';
		echo '</form>';
		echo '<p style="opacity:.85;max-width:520px;">If sync feels slow on localhost, it is usually because WP-Cron only runs when the site gets visits. The button above helps, or you can set up a real server cron to call wp-cron.php.</p>';
		echo '</div>';
		echo '</div>';

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
			'failed' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status='failed'"),
		);

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

		$days = 30;
		$today = gmdate('Y-m-d');
		$labels = array();
		$values = array();
		$map = array();

		$since = gmdate('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
		$series_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(last_sync_at) AS d, COUNT(*) AS c FROM {$boats_table} WHERE last_sync_at IS NOT NULL AND last_sync_at >= %s GROUP BY DATE(last_sync_at) ORDER BY d ASC",
				$since
			),
			ARRAY_A
		);
		foreach ($series_rows as $sr) {
			$map[(string)$sr['d']] = (int)$sr['c'];
		}
		for ($i = $days - 1; $i >= 0; $i--) {
			$d = gmdate('Y-m-d', strtotime($today . ' -' . $i . ' days'));
			$labels[] = $d;
			$values[] = isset($map[$d]) ? (int)$map[$d] : 0;
		}

		return array(
			'postCounts' => $post_counts,
			'pendingUpdateCount' => $pending_update,
			'queueCounts' => $queue_counts,
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
		echo '<tr><th scope="row"><label for="rows_per_page">Rows per page</label></th><td><input name="rows_per_page" id="rows_per_page" type="number" min="1" max="500" value="' . esc_attr($settings['rows_per_page']) . '" /></td></tr>';
		echo '<tr><th scope="row"><label for="delete_after_days">Delete after (days)</label></th><td><input name="delete_after_days" id="delete_after_days" type="number" min="1" max="365" value="' . esc_attr($settings['delete_after_days']) . '" /></td></tr>';
		echo '<tr><th scope="row">Download images</th><td><label><input type="checkbox" name="download_images" value="1" ' . checked(!empty($settings['download_images']), true, false) . ' /> Enable</label></td></tr>';
		echo '<tr><th scope="row"><label for="max_images_per_boat">Max images per boat</label></th><td><input name="max_images_per_boat" id="max_images_per_boat" type="number" min="1" max="200" value="' . esc_attr($settings['max_images_per_boat']) . '" /></td></tr>';
		echo '<tr><th scope="row">Treat missing as sold</th><td><label><input type="checkbox" name="treat_missing_as_sold" value="1" ' . checked(!empty($settings['treat_missing_as_sold']), true, false) . ' /> Mark missing listings as sold and schedule delete</label></td></tr>';
		echo '<tr><th scope="row"><label for="processor_batch_size">Queue batch size</label></th><td><input name="processor_batch_size" id="processor_batch_size" type="number" min="1" max="100" value="' . esc_attr($settings['processor_batch_size']) . '" /> <p class="description">How many jobs to process per cron run (higher = faster, but heavier).</p></td></tr>';
		echo '<tr><th scope="row"><label for="processor_reschedule_seconds">Queue reschedule (seconds)</label></th><td><input name="processor_reschedule_seconds" id="processor_reschedule_seconds" type="number" min="0" max="300" value="' . esc_attr($settings['processor_reschedule_seconds']) . '" /> <p class="description">Delay before the next worker run while jobs are pending.</p></td></tr>';
		echo '<tr><th scope="row"><label for="style_grid_columns">Grid columns</label></th><td><input name="style_grid_columns" id="style_grid_columns" type="number" min="1" max="6" value="' . esc_attr($settings['style_grid_columns']) . '" /></td></tr>';
		echo '<tr><th scope="row"><label for="style_grid_gap">Grid spacing (px)</label></th><td><input name="style_grid_gap" id="style_grid_gap" type="number" min="0" max="80" value="' . esc_attr($settings['style_grid_gap']) . '" /></td></tr>';
		echo '<tr><th scope="row"><label for="style_font_size">Font size (px)</label></th><td><input name="style_font_size" id="style_font_size" type="number" min="12" max="22" value="' . esc_attr($settings['style_font_size']) . '" /></td></tr>';
		echo '<tr><th scope="row"><label for="style_accent_color">Accent color</label></th><td><input name="style_accent_color" id="style_accent_color" type="text" class="regular-text" value="' . esc_attr($settings['style_accent_color']) . '" placeholder="#0b5fff" /></td></tr>';
		echo '</table>';

		submit_button('Save Settings');
		echo '</form>';
		echo '</div>';
	}

	public function render_manual()
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>Manual Sync</h1>';

		echo '<h2>Sync one by DocumentID</h2>';
		echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
		echo '<input type="hidden" name="action" value="wpbs_manual_sync" />';
		wp_nonce_field('wpbs_manual_sync');
		echo '<input type="hidden" name="mode" value="single" />';
		echo '<p><input name="document_id" type="text" class="regular-text" placeholder="e.g. 9963690" /> ';
		echo '<label style="margin-left:8px;"><input type="checkbox" name="force" value="1" /> Force by-id refresh</label></p>';
		echo '<p><button class="button button-primary">Sync Now</button></p>';
		echo '</form>';

		echo '<h2>Sync all marked for update</h2>';
		echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
		echo '<input type="hidden" name="action" value="wpbs_sync_marked" />';
		wp_nonce_field('wpbs_sync_marked');
		echo '<p><button class="button">Enqueue Marked Updates</button></p>';
		echo '</form>';

		echo '<p>Tip: you can mark a Boat post by setting post meta <code>_wpbs_force_update</code> = 1 (bulk tools can be added next).</p>';
		echo '</div>';
	}

	public function render_shortcode_builder()
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>Shortcode Builder</h1>';

		echo '<p><label><strong>Type</strong> '; 
		echo '<select id="wpbs_sc_type"><option value="grid">Grid</option><option value="single">Single</option></select>';
		echo '</label></p>';

		echo '<div id="wpbs_sc_grid">';
		echo '<p><label>Posts per page <input id="wpbs_sc_ppp" type="number" value="12" min="1" /></label></p>';
		echo '<p><label>Columns <input id="wpbs_sc_cols" type="number" value="3" min="1" max="6" /></label></p>';
		echo '</div>';

		echo '<div id="wpbs_sc_single" style="display:none;">';
		echo '<p><label>DocumentID <input id="wpbs_sc_doc" type="text" placeholder="9963690" /></label></p>';
		echo '</div>';

		echo '<p><textarea id="wpbs_sc_out" class="large-text" rows="2" readonly></textarea></p>';
		echo '<p><button class="button" id="wpbs_sc_copy" type="button">Copy</button></p>';

		echo '<script>
(function(){
	function build(){
		var type=document.getElementById("wpbs_sc_type").value;
		var out="";
		if(type==="grid"){
			var ppp=document.getElementById("wpbs_sc_ppp").value||"12";
			var cols=document.getElementById("wpbs_sc_cols").value||"3";
			out="[wpbs_boat_grid posts_per_page=\""+ppp+"\" columns=\""+cols+"\"]";
			document.getElementById("wpbs_sc_grid").style.display="block";
			document.getElementById("wpbs_sc_single").style.display="none";
		}else{
			var doc=document.getElementById("wpbs_sc_doc").value||"";
			out="[wpbs_boat_single id=\""+doc+"\"]";
			document.getElementById("wpbs_sc_grid").style.display="none";
			document.getElementById("wpbs_sc_single").style.display="block";
		}
		document.getElementById("wpbs_sc_out").value=out;
	}
	build();
	document.getElementById("wpbs_sc_type").addEventListener("change", build);
	["wpbs_sc_ppp","wpbs_sc_cols","wpbs_sc_doc"].forEach(function(id){
		var el=document.getElementById(id);
		if(el){ el.addEventListener("input", build); }
	});
	document.getElementById("wpbs_sc_copy").addEventListener("click", function(){
		var el=document.getElementById("wpbs_sc_out");
		el.select();
		document.execCommand("copy");
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

		$settings = array(
			'api_key' => isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '',
			'party_id' => isset($_POST['party_id']) ? sanitize_text_field(wp_unslash($_POST['party_id'])) : '',
			'auto_sync_frequency' => isset($_POST['auto_sync_frequency']) ? sanitize_text_field(wp_unslash($_POST['auto_sync_frequency'])) : 'off',
			'rows_per_page' => isset($_POST['rows_per_page']) ? (int)$_POST['rows_per_page'] : 100,
			'delete_after_days' => isset($_POST['delete_after_days']) ? (int)$_POST['delete_after_days'] : 15,
			'download_images' => !empty($_POST['download_images']) ? 1 : 0,
			'max_images_per_boat' => isset($_POST['max_images_per_boat']) ? (int)$_POST['max_images_per_boat'] : 25,
			'treat_missing_as_sold' => !empty($_POST['treat_missing_as_sold']) ? 1 : 0,
			'processor_batch_size' => isset($_POST['processor_batch_size']) ? (int)$_POST['processor_batch_size'] : 10,
			'processor_reschedule_seconds' => isset($_POST['processor_reschedule_seconds']) ? (int)$_POST['processor_reschedule_seconds'] : 10,
			'style_grid_columns' => isset($_POST['style_grid_columns']) ? (int)$_POST['style_grid_columns'] : 3,
			'style_grid_gap' => isset($_POST['style_grid_gap']) ? (int)$_POST['style_grid_gap'] : 16,
			'style_font_size' => isset($_POST['style_font_size']) ? (int)$_POST['style_font_size'] : 16,
			'style_accent_color' => isset($_POST['style_accent_color']) ? sanitize_text_field(wp_unslash($_POST['style_accent_color'])) : '#0b5fff',
		);

		WPBS_Utils::update_settings($settings);
		if (class_exists('WPBS_Plugin')) {
			WPBS_Plugin::reschedule_auto_sync($settings['auto_sync_frequency']);
		}

		wp_safe_redirect(admin_url('admin.php?page=wpbs-settings&updated=1'));
		exit;
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
