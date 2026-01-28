<?php

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Sync
{
	/** @var WPBS_API */
	private $api;

	public function __construct($api)
	{
		$this->api = $api;
	}

	public function enqueue_full_sync($schedule_key)
	{
		$run_id = $this->start_run($schedule_key);
		$this->enqueue_job('fetch_page', array(
			'run_id' => $run_id,
			'offset' => 0,
			'rows' => (int)WPBS_Utils::get_settings()['rows_per_page'],
		));
		$settings = WPBS_Utils::get_settings();
		$this->ensure_processor_scheduled(isset($settings['processor_initial_delay_seconds']) ? (int)$settings['processor_initial_delay_seconds'] : 5);
		return $run_id;
	}

	public function enqueue_sync_boat($document_id, $force = false)
	{
		$document_id = WPBS_Utils::sanitize_document_id($document_id);
		if ($document_id === '') {
			return new WP_Error('wpbs_invalid_document_id', 'Invalid DocumentID.');
		}

		$this->enqueue_job('sync_boat', array(
			'document_id' => $document_id,
			'force' => (bool)$force,
		));
		$settings = WPBS_Utils::get_settings();
		$this->ensure_processor_scheduled(isset($settings['processor_initial_delay_seconds']) ? (int)$settings['processor_initial_delay_seconds'] : 5);
		return true;
	}

	public function process_queue()
	{
		$settings = WPBS_Utils::get_settings();
		$batch_size = max(1, (int)$settings['processor_batch_size']);
		$this->process_queue_batch($batch_size);
	}

	/**
	 * Process a small queue batch (useful for AJAX-driven worker to avoid timeouts).
	 */
	public function process_queue_batch($batch_size)
	{
		$settings = WPBS_Utils::get_settings();
		$batch_size = max(1, (int)$batch_size);
		$start = microtime(true);

		// If a previous PHP request died mid-job (timeout/fatal), jobs can remain stuck in "processing" forever.
		// Recover those stale locks so the queue can continue.
		$this->recover_stale_processing_jobs(10 * MINUTE_IN_SECONDS);

		$jobs = $this->lock_next_jobs($batch_size);
		if (empty($jobs)) {
			return 0;
		}

		foreach ($jobs as $job) {
			$this->run_job($job);
		}

		$this->record_queue_metrics(count($jobs), microtime(true) - $start);

		// If there are still pending jobs, schedule another processor run.
		if ($this->has_pending_jobs()) {
			$delay = isset($settings['processor_reschedule_seconds']) ? (int)$settings['processor_reschedule_seconds'] : 10;
			$delay = max(0, min(300, $delay));
			$this->ensure_processor_scheduled($delay);
		}

		return count($jobs);
	}

	/**
	 * Recover jobs that have been stuck in processing longer than $stale_seconds.
	 *
	 * This happens when a request is interrupted after marking jobs as processing.
	 */
	public function recover_stale_processing_jobs($stale_seconds)
	{
		$stale_seconds = (int)$stale_seconds;
		global $wpdb;
		$table = WPBS_DB::table_queue();
		// If stale_seconds <= 0, treat all processing jobs as recoverable.
		if ($stale_seconds <= 0) {
			$threshold = gmdate('Y-m-d H:i:s', time() + 1);
		} else {
			$threshold = gmdate('Y-m-d H:i:s', time() - max(1, $stale_seconds));
		}
		$now = WPBS_Utils::now_mysql();

		// Move stale processing jobs back to pending (or to failed if they already exhausted retries).
		$recovered = (int)$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table}
				 SET status = CASE WHEN attempts >= 3 THEN 'failed' ELSE 'pending' END,
				     attempts = CASE WHEN attempts >= 3 THEN attempts ELSE attempts + 1 END,
				     not_before = CASE WHEN attempts >= 3 THEN NULL ELSE DATE_ADD(UTC_TIMESTAMP(), INTERVAL 60 SECOND) END,
				     locked_at = NULL,
				     last_error = CONCAT(IFNULL(last_error,''), CASE WHEN IFNULL(last_error,'') = '' THEN '' ELSE '\n' END, 'Recovered stale processing lock.'),
				     updated_at = %s
				 WHERE status='processing'
				   AND (locked_at IS NULL OR locked_at < %s)",
				$now,
				$threshold
			)
		);

		return $recovered;
	}

	/**
	 * Count how many DocumentIDs currently have duplicate boat posts.
	 */
	public function count_duplicate_boat_groups()
	{
		global $wpdb;
		$post_type = WPBS_POST_TYPE;
		$pm = $wpdb->postmeta;
		$posts = $wpdb->posts;

		$sql = "SELECT COUNT(*) FROM (
			SELECT t.doc
			FROM (
				SELECT DISTINCT pm.post_id, pm.meta_value AS doc
				FROM {$pm} pm
				INNER JOIN {$posts} p ON p.ID = pm.post_id
				WHERE p.post_type = %s
				AND pm.meta_key IN ('_wpbs_document_id','wpbs_document_id')
				AND pm.meta_value IS NOT NULL AND pm.meta_value != ''
			) t
			GROUP BY t.doc
			HAVING COUNT(*) > 1
		) g";

		return (int)$wpdb->get_var($wpdb->prepare($sql, $post_type));
	}

	/**
	 * Deduplicate boat posts in batches.
	 *
	 * @return array{processed_groups:int,deleted_posts:int,reparented_attachments:int,next_offset:int,finished:bool}
	 */
	public function dedupe_boats_batch($offset, $limit)
	{
		$offset = max(0, (int)$offset);
		$limit = max(1, min(200, (int)$limit));

		global $wpdb;
		$post_type = WPBS_POST_TYPE;
		$pm = $wpdb->postmeta;
		$posts = $wpdb->posts;

		$sql = "SELECT t.doc, GROUP_CONCAT(t.post_id ORDER BY t.post_id ASC) AS post_ids
			FROM (
				SELECT DISTINCT pm.post_id, pm.meta_value AS doc
				FROM {$pm} pm
				INNER JOIN {$posts} p ON p.ID = pm.post_id
				WHERE p.post_type = %s
				AND pm.meta_key IN ('_wpbs_document_id','wpbs_document_id')
				AND pm.meta_value IS NOT NULL AND pm.meta_value != ''
			) t
			GROUP BY t.doc
			HAVING COUNT(*) > 1
			ORDER BY t.doc ASC
			LIMIT %d OFFSET %d";

		$rows = $wpdb->get_results($wpdb->prepare($sql, $post_type, $limit, $offset), ARRAY_A);
		if (!is_array($rows) || empty($rows)) {
			return array(
				'processed_groups' => 0,
				'deleted_posts' => 0,
				'reparented_attachments' => 0,
				'next_offset' => $offset,
				'finished' => true,
			);
		}

		$processed_groups = 0;
		$deleted_posts = 0;
		$reparented_attachments = 0;

		foreach ($rows as $row) {
			$doc = isset($row['doc']) ? WPBS_Utils::sanitize_document_id($row['doc']) : '';
			if ($doc === '') {
				continue;
			}
			$ids_str = isset($row['post_ids']) ? (string)$row['post_ids'] : '';
			$ids = $ids_str !== '' ? array_map('intval', explode(',', $ids_str)) : array();
			$ids = array_values(array_unique(array_filter($ids)));
			if (count($ids) < 2) {
				continue;
			}
			$keep_id = (int)$ids[0];
			$res = $this->dedupe_duplicate_boat_posts_with_counts($doc, $ids, $keep_id);
			$processed_groups++;
			$deleted_posts += (int)$res['deleted_posts'];
			$reparented_attachments += (int)$res['reparented_attachments'];
		}

		return array(
			'processed_groups' => $processed_groups,
			'deleted_posts' => $deleted_posts,
			'reparented_attachments' => $reparented_attachments,
			'next_offset' => $offset + count($rows),
			'finished' => false,
		);
	}

	/**
	 * Wrapper around dedupe that returns useful counters.
	 *
	 * @return array{deleted_posts:int,reparented_attachments:int}
	 */
	private function dedupe_duplicate_boat_posts_with_counts($document_id, $post_ids, $keep_post_id)
	{
		$deleted_posts = 0;
		$reparented_attachments = 0;

		$document_id = WPBS_Utils::sanitize_document_id($document_id);
		$keep_post_id = (int)$keep_post_id;
		$post_ids = is_array($post_ids) ? $post_ids : array();
		$post_ids = array_values(array_unique(array_filter(array_map('intval', $post_ids))));

		foreach ($post_ids as $pid) {
			if ($pid <= 0 || $pid === $keep_post_id) {
				continue;
			}
			if (get_post_type($pid) !== WPBS_POST_TYPE) {
				continue;
			}
			$meta_a = (string)get_post_meta($pid, '_wpbs_document_id', true);
			$meta_b = (string)get_post_meta($pid, 'wpbs_document_id', true);
			if ($meta_a !== $document_id && $meta_b !== $document_id) {
				continue;
			}

			$attachments = get_children(array(
				'post_parent' => (int)$pid,
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'fields' => 'ids',
				'numberposts' => -1,
			));
			if (is_array($attachments) && !empty($attachments) && $keep_post_id > 0) {
				foreach ($attachments as $att_id) {
					$att_id = (int)$att_id;
					$att_doc = (string)get_post_meta($att_id, '_wpbs_document_id', true);
					if ($att_doc !== $document_id) {
						continue;
					}
					wp_update_post(array('ID' => $att_id, 'post_parent' => $keep_post_id));
					$reparented_attachments++;
				}
			}

			wp_delete_post((int)$pid, true);
			$deleted_posts++;
		}

		if ($keep_post_id > 0) {
			update_post_meta($keep_post_id, '_wpbs_document_id', $document_id);
			update_post_meta($keep_post_id, 'wpbs_document_id', $document_id);

			// Keep custom table mapping consistent.
			global $wpdb;
			$boats_table = WPBS_DB::table_boats();
			$wpdb->update($boats_table, array('post_id' => $keep_post_id, 'updated_at' => WPBS_Utils::now_mysql()), array('document_id' => $document_id));
		}

		return array('deleted_posts' => $deleted_posts, 'reparented_attachments' => $reparented_attachments);
	}

	/**
	 * Run specific queue jobs immediately (manual admin action).
	 *
	 * @param array<int,int|string> $job_ids
	 * @return array{requested:int,eligible:int,ran:int,done:int,failed:int,pending:int,processing:int}
	 */
	public function run_queue_jobs_by_ids($job_ids)
	{
		$job_ids = is_array($job_ids) ? $job_ids : array();
		$job_ids = array_map('intval', $job_ids);
		$job_ids = array_values(array_filter(array_unique($job_ids)));
		$requested = count($job_ids);
		if ($requested === 0) {
			return array('requested' => 0, 'eligible' => 0, 'ran' => 0, 'done' => 0, 'failed' => 0, 'pending' => 0, 'processing' => 0);
		}

		global $wpdb;
		$table = WPBS_DB::table_queue();
		$now = WPBS_Utils::now_mysql();

		$id_list = implode(',', array_map('intval', $job_ids));
		$eligible_ids = $wpdb->get_col("SELECT id FROM {$table} WHERE id IN ({$id_list}) AND status IN ('pending','failed')");
		$eligible_ids = array_map('intval', is_array($eligible_ids) ? $eligible_ids : array());
		$eligible_ids = array_values(array_filter(array_unique($eligible_ids)));
		$eligible = count($eligible_ids);
		if ($eligible === 0) {
			return array('requested' => $requested, 'eligible' => 0, 'ran' => 0, 'done' => 0, 'failed' => 0, 'pending' => 0, 'processing' => 0);
		}

		$eligible_list = implode(',', array_map('intval', $eligible_ids));
		$wpdb->query("UPDATE {$table} SET status='processing', locked_at='{$now}', not_before=NULL, updated_at='{$now}' WHERE id IN ({$eligible_list}) AND status IN ('pending','failed')");

		$jobs = $wpdb->get_results("SELECT * FROM {$table} WHERE id IN ({$eligible_list}) AND status='processing' ORDER BY id ASC", ARRAY_A);
		if (!is_array($jobs)) {
			$jobs = array();
		}

		foreach ($jobs as $job) {
			$this->run_job($job);
		}

		$after = $wpdb->get_results("SELECT id, status FROM {$table} WHERE id IN ({$eligible_list})", ARRAY_A);
		$counts = array('done' => 0, 'failed' => 0, 'pending' => 0, 'processing' => 0);
		if (is_array($after)) {
			foreach ($after as $row) {
				$st = isset($row['status']) ? (string)$row['status'] : '';
				if (isset($counts[$st])) {
					$counts[$st]++;
				}
			}
		}

		return array(
			'requested' => $requested,
			'eligible' => $eligible,
			'ran' => count($jobs),
			'done' => (int)$counts['done'],
			'failed' => (int)$counts['failed'],
			'pending' => (int)$counts['pending'],
			'processing' => (int)$counts['processing'],
		);
	}

	private function record_queue_metrics($jobs_count, $duration_seconds)
	{
		$jobs_count = (int)$jobs_count;
		$duration_seconds = (float)$duration_seconds;
		if ($jobs_count <= 0 || $duration_seconds <= 0) {
			return;
		}

		$jobs_per_sec = $jobs_count / $duration_seconds;
		$avg_job_sec = $duration_seconds / $jobs_count;

		$existing = get_option('wpbs_queue_metrics', array());
		if (!is_array($existing)) {
			$existing = array();
		}

		$alpha = 0.2;
		$prev_jps = isset($existing['avg_jobs_per_sec']) ? (float)$existing['avg_jobs_per_sec'] : 0.0;
		$prev_ajs = isset($existing['avg_job_seconds']) ? (float)$existing['avg_job_seconds'] : 0.0;

		$new_jps = $prev_jps > 0 ? ($prev_jps * (1 - $alpha) + $jobs_per_sec * $alpha) : $jobs_per_sec;
		$new_ajs = $prev_ajs > 0 ? ($prev_ajs * (1 - $alpha) + $avg_job_sec * $alpha) : $avg_job_sec;

		$existing['avg_jobs_per_sec'] = $new_jps;
		$existing['avg_job_seconds'] = $new_ajs;
		$existing['last_run_at'] = WPBS_Utils::now_mysql();
		$existing['last_run_jobs'] = $jobs_count;
		$existing['last_run_seconds'] = $duration_seconds;

		update_option('wpbs_queue_metrics', $existing);
	}

	public function delete_boat_after_grace($document_id)
	{
		$document_id = WPBS_Utils::sanitize_document_id($document_id);
		if ($document_id === '') {
			return;
		}

		$post_id = $this->get_post_id_by_document_id($document_id);
		if ($post_id) {
			wp_trash_post($post_id);
		}
	}

	private function run_job($job)
	{
		$type = $job['type'];
		$payload = WPBS_Utils::json_decode_assoc($job['payload']);
		if (!is_array($payload)) {
			$payload = array();
		}

		try {
			switch ($type) {
				case 'fetch_page':
					$this->job_fetch_page($payload);
					break;
				case 'sync_boat':
					$this->job_sync_boat($payload);
					break;
				case 'reconcile':
					$this->job_reconcile($payload);
					break;
				default:
					throw new Exception('Unknown job type: ' . $type);
			}

			$this->mark_job_done($job['id']);
		} catch (Throwable $e) {
			$this->mark_job_failed($job['id'], $e->getMessage());
		}
	}

	private function job_fetch_page($payload)
	{
		$run_id = isset($payload['run_id']) ? (string)$payload['run_id'] : '';
		$offset = isset($payload['offset']) ? (int)$payload['offset'] : 0;
		$rows = isset($payload['rows']) ? (int)$payload['rows'] : (int)WPBS_Utils::get_settings()['rows_per_page'];

		$result = $this->api->search(array(
			'offset' => $offset,
			'rows' => $rows,
		));

		if (is_wp_error($result)) {
			throw new Exception($result->get_error_message());
		}

		$num_results = isset($result['numResults']) ? (int)$result['numResults'] : 0;
		$items = isset($result['results']) && is_array($result['results']) ? $result['results'] : array();

		foreach ($items as $item) {
			if (!is_array($item) || !isset($item['DocumentID'])) {
				continue;
			}
			$document_id = WPBS_Utils::sanitize_document_id($item['DocumentID']);
			if ($document_id === '') {
				continue;
			}
			$this->enqueue_job('sync_boat', array(
				'document_id' => $document_id,
				'run_id' => $run_id,
				'partial' => $item,
			));
		}

		$next_offset = $offset + $rows;
		if ($next_offset < $num_results) {
			$this->enqueue_job('fetch_page', array(
				'run_id' => $run_id,
				'offset' => $next_offset,
				'rows' => $rows,
			));
		} else {
			$this->enqueue_job('reconcile', array(
				'run_id' => $run_id,
			));
		}
	}

	private function job_sync_boat($payload)
	{
		$document_id = isset($payload['document_id']) ? WPBS_Utils::sanitize_document_id($payload['document_id']) : '';
		if ($document_id === '') {
			throw new Exception('Missing document_id');
		}

		$run_id = isset($payload['run_id']) ? (string)$payload['run_id'] : null;
		$partial = isset($payload['partial']) && is_array($payload['partial']) ? $payload['partial'] : null;
		$force = !empty($payload['force']);

		$data = $partial;
		if (!$data || $force) {
			$by_id = $this->api->get_by_id($document_id);
			if (is_wp_error($by_id)) {
				throw new Exception($by_id->get_error_message());
			}
			$data = $by_id;
		}

		if (!is_array($data)) {
			throw new Exception('Invalid boat data');
		}

		$sales_status = isset($data['SalesStatus']) ? (string)$data['SalesStatus'] : (isset($data['salesStatus']) ? (string)$data['salesStatus'] : '');
		$last_mod_date = isset($data['LastModificationDate']) ? (string)$data['LastModificationDate'] : null;

		$post_id = $this->upsert_boat_post($document_id, $data);

		$this->upsert_boat_row($document_id, array(
			'post_id' => $post_id,
			'sales_status' => $sales_status,
			'last_modification_date' => $last_mod_date,
			'last_seen_run_id' => $run_id,
			'last_sync_at' => WPBS_Utils::now_mysql(),
			'last_sync_status' => 'ok',
			'last_error' => null,
			'raw_json' => WPBS_Utils::json_encode($data),
		));

		// Also store a post-meta timestamp for admin display / fallback.
		update_post_meta($post_id, '_wpbs_last_sync_at', WPBS_Utils::now_mysql());

		$this->apply_soldout_logic($document_id, $post_id, $sales_status);
		$this->sync_images($document_id, $post_id, $data);
	}

	private function job_reconcile($payload)
	{
		$settings = WPBS_Utils::get_settings();
		if (empty($settings['treat_missing_as_sold'])) {
			return;
		}

		$run_id = isset($payload['run_id']) ? (string)$payload['run_id'] : '';
		if ($run_id === '') {
			return;
		}

		global $wpdb;
		$table = WPBS_DB::table_boats();
		$missing = $wpdb->get_results($wpdb->prepare(
			"SELECT document_id, post_id FROM {$table} WHERE (last_seen_run_id IS NULL OR last_seen_run_id != %s)",
			$run_id
		), ARRAY_A);

		foreach ($missing as $row) {
			$document_id = (string)$row['document_id'];
			$post_id = isset($row['post_id']) ? (int)$row['post_id'] : 0;
			if ($document_id === '') {
				continue;
			}

			$this->apply_soldout_logic($document_id, $post_id, 'Missing');
		}
	}

	private function upsert_boat_post($document_id, $data)
	{
		$post_ids = $this->get_post_ids_by_document_id($document_id);
		$post_id = !empty($post_ids) ? (int)$post_ids[0] : 0;
		if (count($post_ids) > 1 && $post_id > 0) {
			$this->dedupe_duplicate_boat_posts($document_id, $post_ids, $post_id);
		}

		$title_parts = array();
		if (!empty($data['ModelYear'])) {
			$title_parts[] = (string)$data['ModelYear'];
		}
		if (!empty($data['MakeString'])) {
			$title_parts[] = (string)$data['MakeString'];
		}
		if (!empty($data['Model'])) {
			$title_parts[] = (string)$data['Model'];
		}
		$title = trim(implode(' ', $title_parts));
		if ($title === '') {
			$title = 'Boat ' . $document_id;
		}

		$desc = '';
		if (!empty($data['GeneralBoatDescription']) && is_array($data['GeneralBoatDescription'])) {
			$desc = (string)reset($data['GeneralBoatDescription']);
		}

		$postarr = array(
			'post_type' => WPBS_POST_TYPE,
			'post_title' => wp_strip_all_tags($title),
			'post_content' => $desc,
			'post_status' => 'publish',
		);

		if ($post_id) {
			$postarr['ID'] = $post_id;
			$post_id = wp_update_post($postarr, true);
		} else {
			$post_id = wp_insert_post($postarr, true);
		}

		if (is_wp_error($post_id)) {
			throw new Exception($post_id->get_error_message());
		}

		// Store document id in both keys for backward compatibility.
		update_post_meta($post_id, '_wpbs_document_id', $document_id);
		update_post_meta($post_id, 'wpbs_document_id', $document_id);

		$this->update_fields($post_id, $data);

		return (int)$post_id;
	}

	/**
	 * @return int[]
	 */
	private function get_post_ids_by_document_id($document_id)
	{
		$document_id = WPBS_Utils::sanitize_document_id($document_id);
		if ($document_id === '') {
			return array();
		}

		$ids = array();

		// Prefer the canonical mapping from our custom boats table when available.
		global $wpdb;
		$boats_table = WPBS_DB::table_boats();
		$maybe_post_id = (int)$wpdb->get_var($wpdb->prepare(
			"SELECT post_id FROM {$boats_table} WHERE document_id=%s AND post_id IS NOT NULL ORDER BY id DESC LIMIT 1",
			$document_id
		));
		if ($maybe_post_id > 0) {
			$p = get_post($maybe_post_id);
			if ($p && $p->post_type === WPBS_POST_TYPE) {
				$ids[] = $maybe_post_id;
			}
		}

		// Search by both meta keys: older installs used wpbs_document_id without underscore.
		$q = new WP_Query(array(
			'post_type' => WPBS_POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => 10,
			'fields' => 'ids',
			'no_found_rows' => true,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => '_wpbs_document_id',
					'value' => $document_id,
					'compare' => '=',
				),
				array(
					'key' => 'wpbs_document_id',
					'value' => $document_id,
					'compare' => '=',
				),
			),
		));

		if (!empty($q->posts) && is_array($q->posts)) {
			foreach ($q->posts as $pid) {
				$ids[] = (int)$pid;
			}
		}

		$ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
		sort($ids);
		return $ids;
	}

	private function dedupe_duplicate_boat_posts($document_id, $post_ids, $keep_post_id)
	{
		$document_id = WPBS_Utils::sanitize_document_id($document_id);
		$keep_post_id = (int)$keep_post_id;
		$post_ids = is_array($post_ids) ? $post_ids : array();
		$post_ids = array_values(array_unique(array_filter(array_map('intval', $post_ids))));

		foreach ($post_ids as $pid) {
			if ($pid <= 0 || $pid === $keep_post_id) {
				continue;
			}
			if (get_post_type($pid) !== WPBS_POST_TYPE) {
				continue;
			}
			$meta_a = (string)get_post_meta($pid, '_wpbs_document_id', true);
			$meta_b = (string)get_post_meta($pid, 'wpbs_document_id', true);
			if ($meta_a !== $document_id && $meta_b !== $document_id) {
				continue;
			}

			// Move only media that is truly tied to this duplicate post over to the kept post.
			$attachments = get_children(array(
				'post_parent' => (int)$pid,
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'fields' => 'ids',
				'numberposts' => -1,
			));
			if (is_array($attachments) && !empty($attachments)) {
				foreach ($attachments as $att_id) {
					$att_id = (int)$att_id;
					$att_doc = (string)get_post_meta($att_id, '_wpbs_document_id', true);
					if ($att_doc !== $document_id) {
						continue;
					}
					if ($keep_post_id > 0) {
						wp_update_post(array('ID' => $att_id, 'post_parent' => $keep_post_id));
					}
				}
			}

			// Permanently delete the duplicate boat post.
			wp_delete_post((int)$pid, true);
		}

		// Ensure keep post has both meta keys for future lookups.
		if ($keep_post_id > 0) {
			update_post_meta($keep_post_id, '_wpbs_document_id', $document_id);
			update_post_meta($keep_post_id, 'wpbs_document_id', $document_id);
		}
	}

	private function update_fields($post_id, $data)
	{
		$price_parsed = WPBS_Utils::parse_price_string(isset($data['Price']) ? $data['Price'] : '');
		$orig_price_parsed = WPBS_Utils::parse_price_string(isset($data['OriginalPrice']) ? $data['OriginalPrice'] : '');

		$boat_location = (isset($data['BoatLocation']) && is_array($data['BoatLocation'])) ? $data['BoatLocation'] : array();
		$office = (isset($data['Office']) && is_array($data['Office'])) ? $data['Office'] : array();
		$sales_rep = (isset($data['SalesRep']) && is_array($data['SalesRep'])) ? $data['SalesRep'] : array();
		$owner = (isset($data['Owner']) && is_array($data['Owner'])) ? $data['Owner'] : array();

		// Build location string: City, State Country
		$location_parts = array();
		if (!empty($boat_location['BoatCityName'])) {
			$location_parts[] = (string)$boat_location['BoatCityName'];
		} elseif (!empty($data['BoatCityNameNoCaseAlnumOnly'])) {
			$location_parts[] = (string)$data['BoatCityNameNoCaseAlnumOnly'];
		}
		if (!empty($boat_location['BoatStateCode'])) {
			$location_parts[] = (string)$boat_location['BoatStateCode'];
		}
		$location_str = implode(', ', $location_parts);

		// Build engine summary from Engines array
		$engine_summary = $this->build_engine_summary($data);

		// Extract first engine details for quick specs
		$first_engine = array();
		if (!empty($data['Engines']) && is_array($data['Engines']) && isset($data['Engines'][0])) {
			$first_engine = $data['Engines'][0];
		}

		$map = array(
			// Core identifiers
			'wpbs_document_id' => isset($data['DocumentID']) ? (string)$data['DocumentID'] : '',
			'wpbs_source' => isset($data['Source']) ? (string)$data['Source'] : '',
			'wpbs_sales_status' => isset($data['SalesStatus']) ? (string)$data['SalesStatus'] : '',
			'wpbs_co_op_indicator' => isset($data['CoOpIndicator']) ? (string)$data['CoOpIndicator'] : '',

			// Pricing
			'wpbs_price' => isset($data['Price']) ? (string)$data['Price'] : '',
			'wpbs_price_amount' => $price_parsed['amount'],
			'wpbs_price_currency' => $price_parsed['currency'],
			'wpbs_original_price' => isset($data['OriginalPrice']) ? (string)$data['OriginalPrice'] : '',
			'wpbs_original_price_amount' => $orig_price_parsed['amount'],
			'wpbs_original_price_currency' => $orig_price_parsed['currency'],
			'wpbs_price_hide_ind' => isset($data['PriceHideInd']) ? (int)(bool)$data['PriceHideInd'] : 0,
			'wpbs_norm_price' => isset($data['NormPrice']) ? (float)$data['NormPrice'] : 0,
			'wpbs_tax_status' => isset($data['TaxStatusCode']) ? (string)$data['TaxStatusCode'] : '',

			// Make/Model/Year
			'wpbs_make' => isset($data['MakeString']) ? (string)$data['MakeString'] : '',
			'wpbs_make_exact' => isset($data['MakeStringExact']) ? (string)$data['MakeStringExact'] : '',
			'wpbs_model' => isset($data['Model']) ? (string)$data['Model'] : '',
			'wpbs_model_exact' => isset($data['ModelExact']) ? (string)$data['ModelExact'] : '',
			'wpbs_model_year' => isset($data['ModelYear']) ? (int)$data['ModelYear'] : 0,
			'wpbs_sale_class' => isset($data['SaleClassCode']) ? (string)$data['SaleClassCode'] : '',
			'wpbs_condition' => isset($data['SaleClassCode']) ? (string)$data['SaleClassCode'] : '', // New/Used
			'wpbs_boat_category' => isset($data['BoatCategoryCode']) ? (string)$data['BoatCategoryCode'] : '',
			'wpbs_boat_name' => isset($data['BoatName']) ? (string)$data['BoatName'] : '',
			'wpbs_listing_title' => isset($data['ListingTitle']) ? (string)$data['ListingTitle'] : '',

			// Dealer/Company info
			'wpbs_company_name' => isset($data['CompanyName']) ? (string)$data['CompanyName'] : '',
			'wpbs_dealer_name' => isset($data['CompanyName']) ? (string)$data['CompanyName'] : (isset($office['Name']) ? (string)$office['Name'] : ''),
			'wpbs_office_name' => isset($office['Name']) ? (string)$office['Name'] : '',
			'wpbs_office_address' => isset($office['PostalAddress']) ? (string)$office['PostalAddress'] : '',
			'wpbs_office_city' => isset($office['City']) ? (string)$office['City'] : '',
			'wpbs_office_state' => isset($office['State']) ? (string)$office['State'] : '',
			'wpbs_office_postcode' => isset($office['PostCode']) ? (string)$office['PostCode'] : '',
			'wpbs_office_country' => isset($office['Country']) ? (string)$office['Country'] : '',
			'wpbs_office_email' => isset($office['Email']) ? (string)$office['Email'] : '',
			'wpbs_office_phone' => isset($office['Phone']) ? (string)$office['Phone'] : '',
			'wpbs_sales_rep_name' => isset($sales_rep['Name']) ? (string)$sales_rep['Name'] : '',
			'wpbs_sales_rep_party_id' => isset($sales_rep['PartyId']) ? (string)$sales_rep['PartyId'] : '',
			'wpbs_owner_party_id' => isset($owner['PartyId']) ? (string)$owner['PartyId'] : '',

			// Location
			'wpbs_city' => isset($data['BoatCityNameNoCaseAlnumOnly']) ? (string)$data['BoatCityNameNoCaseAlnumOnly'] : '',
			'wpbs_boat_city' => isset($boat_location['BoatCityName']) ? (string)$boat_location['BoatCityName'] : '',
			'wpbs_state' => isset($boat_location['BoatStateCode']) ? (string)$boat_location['BoatStateCode'] : '',
			'wpbs_boat_country' => isset($boat_location['BoatCountryID']) ? (string)$boat_location['BoatCountryID'] : '',
			'wpbs_country_code' => isset($data['RegistrationCountryCode']) ? (string)$data['RegistrationCountryCode'] : '',
			'wpbs_location' => $location_str,

			// Dimensions
			'wpbs_length_overall' => isset($data['LengthOverall']) ? (string)$data['LengthOverall'] : '',
			'wpbs_nominal_length' => isset($data['NominalLength']) ? (string)$data['NominalLength'] : '',
			'wpbs_norm_nominal_length' => isset($data['NormNominalLength']) ? (float)$data['NormNominalLength'] : 0,
			'wpbs_beam' => isset($data['BeamMeasure']) ? (string)$data['BeamMeasure'] : '',
			'wpbs_dry_weight' => isset($data['DryWeightMeasure']) ? (string)$data['DryWeightMeasure'] : '',
			'wpbs_deadrise' => isset($data['DeadriseMeasure']) ? (string)$data['DeadriseMeasure'] : '',
			'wpbs_bridge_clearance' => isset($data['BridgeClearanceMeasure']) ? (string)$data['BridgeClearanceMeasure'] : '',
			'wpbs_freeboard' => isset($data['FreeBoardMeasure']) ? (string)$data['FreeBoardMeasure'] : '',
			'wpbs_cabin_headroom' => isset($data['CabinHeadroomMeasure']) ? (string)$data['CabinHeadroomMeasure'] : '',
			'wpbs_displacement' => isset($data['DisplacementMeasure']) ? (string)$data['DisplacementMeasure'] : '',
			'wpbs_displacement_type' => isset($data['DisplacementTypeCode']) ? (string)$data['DisplacementTypeCode'] : '',
			'wpbs_ballast_weight' => isset($data['BallastWeightMeasure']) ? (string)$data['BallastWeightMeasure'] : '',

			// Speed & Performance
			'wpbs_cruising_speed' => isset($data['CruisingSpeedMeasure']) ? (string)$data['CruisingSpeedMeasure'] : '',
			'wpbs_max_speed' => isset($data['MaximumSpeedMeasure']) ? (string)$data['MaximumSpeedMeasure'] : '',
			'wpbs_range' => isset($data['RangeMeasure']) ? (string)$data['RangeMeasure'] : '',
			'wpbs_propeller_cruising_speed' => isset($data['PropellerCruisingSpeed']) ? (string)$data['PropellerCruisingSpeed'] : '',

			// Engine details
			'wpbs_engine_summary' => $engine_summary,
			'wpbs_total_engine_power' => isset($data['TotalEnginePowerQuantity']) ? (string)$data['TotalEnginePowerQuantity'] : '',
			'wpbs_number_of_engines' => isset($data['NumberOfEngines']) ? (int)$data['NumberOfEngines'] : 0,
			'wpbs_total_engine_hours' => isset($data['TotalEngineHoursNumeric']) ? (int)$data['TotalEngineHoursNumeric'] : 0,
			'wpbs_drive_type' => isset($data['DriveTypeCode']) ? (string)$data['DriveTypeCode'] : '',
			'wpbs_drive_up' => isset($data['DriveUp']) ? (string)$data['DriveUp'] : '',
			'wpbs_engines_json' => (!empty($data['Engines']) && is_array($data['Engines'])) ? WPBS_Utils::json_encode($data['Engines']) : '',

			// First engine quick access
			'wpbs_engine_make' => isset($first_engine['Make']) ? (string)$first_engine['Make'] : '',
			'wpbs_engine_model' => isset($first_engine['Model']) ? (string)$first_engine['Model'] : '',
			'wpbs_engine_year' => isset($first_engine['Year']) ? (int)$first_engine['Year'] : 0,
			'wpbs_engine_power' => isset($first_engine['EnginePower']) ? (string)$first_engine['EnginePower'] : '',
			'wpbs_engine_type' => isset($first_engine['Type']) ? (string)$first_engine['Type'] : '',
			'wpbs_fuel_type' => isset($first_engine['Fuel']) ? (string)$first_engine['Fuel'] : '',
			'wpbs_propeller_type' => isset($first_engine['PropellerType']) ? (string)$first_engine['PropellerType'] : '',

			// Capacity
			'wpbs_fuel_tank_capacity' => isset($data['FuelTankCapacityMeasure']) ? (string)$data['FuelTankCapacityMeasure'] : '',
			'wpbs_water_tank_capacity' => isset($data['WaterTankCapacityMeasure']) ? (string)$data['WaterTankCapacityMeasure'] : '',
			'wpbs_heads_count' => isset($data['HeadsCountNumeric']) ? (int)$data['HeadsCountNumeric'] : 0,
			'wpbs_cabins_count' => isset($data['CabinsCountNumeric']) ? (int)$data['CabinsCountNumeric'] : 0,

			// Hull & Construction
			'wpbs_hull_material' => isset($data['BoatHullMaterialCode']) ? (string)$data['BoatHullMaterialCode'] : '',
			'wpbs_hull_id' => isset($data['BoatHullID']) ? (string)$data['BoatHullID'] : '',
			'wpbs_keel_type' => isset($data['BoatKeelCode']) ? (string)$data['BoatKeelCode'] : '',
			'wpbs_windlass_type' => isset($data['WindlassTypeCode']) ? (string)$data['WindlassTypeCode'] : '',
			'wpbs_electrical_circuit' => isset($data['ElectricalCircuitMeasure']) ? (string)$data['ElectricalCircuitMeasure'] : '',
			'wpbs_trim_tabs' => isset($data['TrimTabsIndicator']) ? (int)(bool)$data['TrimTabsIndicator'] : 0,
			'wpbs_convertible_saloon' => isset($data['ConvertibleSaloonIndicator']) ? (int)(bool)$data['ConvertibleSaloonIndicator'] : 0,

			// IDs & References
			'wpbs_stock_number' => isset($data['StockNumber']) ? (string)$data['StockNumber'] : '',
			'wpbs_yachtworld_id' => isset($data['YachtWorldID']) ? (string)$data['YachtWorldID'] : '',
			'wpbs_btolid' => isset($data['BtolID']) ? (string)$data['BtolID'] : '',
			'wpbs_bcnaid' => isset($data['BcnaID']) ? (string)$data['BcnaID'] : '',
			'wpbs_has_hull_id' => isset($data['HasBoatHullID']) ? (int)(bool)$data['HasBoatHullID'] : 0,

			// Dates & Timestamps
			'wpbs_last_seen_timestamp' => isset($data['IMTTimeStamp']) ? (string)$data['IMTTimeStamp'] : '',
			'wpbs_last_modification_date' => isset($data['LastModificationDate']) ? (string)$data['LastModificationDate'] : '',
			'wpbs_item_received_date' => isset($data['ItemReceivedDate']) ? (string)$data['ItemReceivedDate'] : '',

			// Builder/Designer
			'wpbs_builder_name' => isset($data['BuilderName']) ? (string)$data['BuilderName'] : '',
			'wpbs_designer_name' => isset($data['DesignerName']) ? (string)$data['DesignerName'] : '',

			// Media indicators
			'wpbs_embedded_video_present' => isset($data['EmbeddedVideoPresent']) ? (int)(bool)$data['EmbeddedVideoPresent'] : 0,
			'wpbs_immersive_tour_present' => isset($data['ImmersiveTourPresent']) ? (int)(bool)$data['ImmersiveTourPresent'] : 0,
			'wpbs_image_360_present' => isset($data['Image360PhotoPresent']) ? (int)(bool)$data['Image360PhotoPresent'] : 0,
			'wpbs_is_available_for_pls' => isset($data['IsAvailableForPls']) ? (int)(bool)$data['IsAvailableForPls'] : 0,
			'wpbs_option_active' => isset($data['OptionActiveIndicator']) ? (int)(bool)$data['OptionActiveIndicator'] : 0,

			// Descriptions & Content
			'wpbs_general_description_html' => (!empty($data['GeneralBoatDescription']) && is_array($data['GeneralBoatDescription'])) ? (string)reset($data['GeneralBoatDescription']) : '',
			'wpbs_additional_detail_html' => (!empty($data['AdditionalDetailDescription']) && is_array($data['AdditionalDetailDescription'])) ? (string)reset($data['AdditionalDetailDescription']) : '',
			'wpbs_boat_class_codes' => (!empty($data['BoatClassCode']) && is_array($data['BoatClassCode'])) ? implode(', ', array_map('strval', $data['BoatClassCode'])) : '',
			'wpbs_embedded_video_urls' => (!empty($data['EmbeddedVideo']) && is_array($data['EmbeddedVideo'])) ? implode("\n", array_map('strval', $data['EmbeddedVideo'])) : '',

			// Services & Marketing (JSON for complex arrays)
			'wpbs_services_json' => (!empty($data['Service']) && is_array($data['Service'])) ? WPBS_Utils::json_encode($data['Service']) : '',
			'wpbs_marketing_json' => (!empty($data['Marketing']) && is_array($data['Marketing'])) ? WPBS_Utils::json_encode($data['Marketing']) : '',
		);

		foreach ($map as $key => $value) {
			$this->update_field_or_meta($post_id, $key, $value);
		}
	}

	private function update_field_or_meta($post_id, $field_name, $value)
	{
		if (function_exists('update_field')) {
			// If ACF is installed, update by field name (works if field keys exist).
			@update_field($field_name, $value, $post_id);
		}
		update_post_meta($post_id, $field_name, $value);
	}

	private function sync_images($document_id, $post_id, $data)
	{
		$settings = WPBS_Utils::get_settings();
		if (empty($settings['download_images'])) {
			return;
		}
		$image_urls = $this->extract_image_urls($data);
		if (empty($image_urls)) {
			return;
		}

		$max = isset($settings['max_images_per_boat']) ? (int)$settings['max_images_per_boat'] : 25;
		$max = max(1, min(200, $max));
		$image_urls = array_slice($image_urls, 0, $max);

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_ids = array();

		foreach ($image_urls as $idx => $url) {
			$is_featured = ($idx === 0);
			$this->upsert_image_row($document_id, $url, $is_featured ? 1 : 0);

			$existing_attachment_id = $this->get_attachment_id_by_source_url($url);
			if (!$existing_attachment_id) {
				$existing_attachment_id = $this->download_image_as_attachment($url, $post_id, $document_id);
			}

			if ($existing_attachment_id) {
				// If this attachment belongs to this boat, keep its parent pointing at this boat post.
				$att_doc = (string)get_post_meta((int)$existing_attachment_id, '_wpbs_document_id', true);
				if ($att_doc === $document_id) {
					$parent = (int)get_post_field('post_parent', (int)$existing_attachment_id);
					if ($parent !== (int)$post_id) {
						wp_update_post(array('ID' => (int)$existing_attachment_id, 'post_parent' => (int)$post_id));
					}
				}

				$attachment_ids[] = (int)$existing_attachment_id;
				$this->link_image_attachment($document_id, $url, (int)$existing_attachment_id);
				if ($is_featured && !get_post_thumbnail_id($post_id)) {
					set_post_thumbnail($post_id, (int)$existing_attachment_id);
				}
			}
		}

		$attachment_ids = array_values(array_unique(array_filter(array_map('intval', $attachment_ids))));
		if (!empty($attachment_ids)) {
			// Store a gallery list for theme/shortcodes, and optionally for ACF gallery field if present.
			update_post_meta($post_id, 'wpbs_gallery_attachment_ids', $attachment_ids);
			if (function_exists('update_field')) {
				@update_field('wpbs_gallery', $attachment_ids, $post_id);
			}
		}
	}

	private function build_engine_summary($data)
	{
		if (!isset($data['Engines']) || !is_array($data['Engines']) || empty($data['Engines'])) {
			if (!empty($data['TotalEnginePowerQuantity'])) {
				return (string)$data['TotalEnginePowerQuantity'];
			}
			return '';
		}

		$num_engines = isset($data['NumberOfEngines']) ? (int)$data['NumberOfEngines'] : count($data['Engines']);
		$parts = array();
		$unique_engines = array();

		foreach ($data['Engines'] as $engine) {
			if (!is_array($engine)) {
				continue;
			}
			$make = '';
			$model = '';
			$hp = '';
			$type = '';

			if (!empty($engine['Make'])) {
				$make = (string)$engine['Make'];
			} elseif (!empty($engine['EngineMake'])) {
				$make = (string)$engine['EngineMake'];
			}
			if (!empty($engine['Model'])) {
				$model = (string)$engine['Model'];
			} elseif (!empty($engine['EngineModel'])) {
				$model = (string)$engine['EngineModel'];
			}
			// Parse EnginePower format: "400|horsepower"
			if (!empty($engine['EnginePower'])) {
				$power_str = (string)$engine['EnginePower'];
				if (strpos($power_str, '|') !== false) {
					$power_parts = explode('|', $power_str);
					$hp = trim($power_parts[0]);
				} else {
					$hp = preg_replace('/[^0-9.]/', '', $power_str);
				}
			} elseif (!empty($engine['Horsepower'])) {
				$hp = (string)$engine['Horsepower'];
			} elseif (!empty($engine['HP'])) {
				$hp = (string)$engine['HP'];
			}
			if (!empty($engine['Type'])) {
				$type = (string)$engine['Type'];
			}

			$engine_key = $make . '|' . $model . '|' . $hp;
			if (!isset($unique_engines[$engine_key])) {
				$unique_engines[$engine_key] = array(
					'make' => $make,
					'model' => $model,
					'hp' => $hp,
					'type' => $type,
					'count' => 1,
				);
			} else {
				$unique_engines[$engine_key]['count']++;
			}
		}

		foreach ($unique_engines as $eng) {
			$line_parts = array();
			if ($eng['count'] > 1) {
				$line_parts[] = $eng['count'] . 'x';
			}
			if ($eng['make']) {
				$line_parts[] = $eng['make'];
			}
			if ($eng['model']) {
				$line_parts[] = $eng['model'];
			}
			if ($eng['hp']) {
				$line_parts[] = $eng['hp'] . ' hp';
			}
			if ($eng['type']) {
				$line_parts[] = '(' . $eng['type'] . ')';
			}
			$line = trim(implode(' ', $line_parts));
			if ($line !== '') {
				$parts[] = $line;
			}
		}

		return implode(' | ', $parts);
	}

	private function get_attachment_id_by_source_url($url)
	{
		$url = is_string($url) ? trim($url) : '';
		if ($url === '') {
			return 0;
		}

		$found = get_posts(array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'fields' => 'ids',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key' => '_wpbs_source_url',
					'value' => $url,
					'compare' => '=',
				),
			),
		));

		if (!empty($found)) {
			return (int)$found[0];
		}
		return 0;
	}

	private function download_image_as_attachment($url, $post_id, $document_id)
	{
		$url = is_string($url) ? trim($url) : '';
		if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
			return 0;
		}

		$tmp = download_url($url, 30);
		if (is_wp_error($tmp)) {
			return 0;
		}

		$filename = wp_basename(parse_url($url, PHP_URL_PATH));
		if (!$filename) {
			$filename = 'boat-' . $document_id . '-' . wp_generate_password(6, false, false) . '.jpg';
		}

		$file_array = array(
			'name' => sanitize_file_name($filename),
			'tmp_name' => $tmp,
		);

		$attachment_id = media_handle_sideload($file_array, $post_id);
		if (is_wp_error($attachment_id)) {
			@unlink($tmp);
			return 0;
		}

		update_post_meta((int)$attachment_id, '_wpbs_source_url', $url);
		update_post_meta((int)$attachment_id, '_wpbs_document_id', $document_id);

		return (int)$attachment_id;
	}

	private function extract_image_urls($data)
	{
		$urls = array();
		if (isset($data['Images']) && is_array($data['Images'])) {
			foreach ($data['Images'] as $img) {
				if (is_string($img) && filter_var($img, FILTER_VALIDATE_URL)) {
					$urls[] = $img;
					continue;
				}
				if (is_string($img) && strpos($img, '|') !== false) {
					$maybe = explode('|', $img);
					if (!empty($maybe[0]) && filter_var($maybe[0], FILTER_VALIDATE_URL)) {
						$urls[] = $maybe[0];
						continue;
					}
				}
				if (is_array($img)) {
					foreach (array('Url', 'URL', 'Uri', 'URI', 'Href', 'href', 'src', 'SourceUrl', 'sourceUrl') as $k) {
						if (!empty($img[$k]) && is_string($img[$k]) && filter_var($img[$k], FILTER_VALIDATE_URL)) {
							$urls[] = $img[$k];
							break;
						}
					}
					if (isset($img['Sizes']) && is_array($img['Sizes'])) {
						foreach ($img['Sizes'] as $size) {
							if (is_array($size)) {
								foreach (array('Url', 'URL', 'src', 'href') as $k) {
									if (!empty($size[$k]) && is_string($size[$k]) && filter_var($size[$k], FILTER_VALIDATE_URL)) {
										$urls[] = $size[$k];
										break 2;
									}
								}
							}
						}
					}
				}
			}
		}

		$urls = array_values(array_unique($urls));
		return $urls;
	}

	private function apply_soldout_logic($document_id, $post_id, $sales_status)
	{
		$settings = WPBS_Utils::get_settings();
		$days = max(1, (int)$settings['delete_after_days']);

		// Treat empty/missing status as active (boat is available)
		$status_lower = strtolower(trim((string)$sales_status));
		$is_active = ($status_lower === '' || $status_lower === 'active' || $status_lower === 'available');
		$soldout_at = $post_id ? get_post_meta($post_id, '_wpbs_soldout_at', true) : '';

		if ($is_active) {
			if ($post_id) {
				delete_post_meta($post_id, '_wpbs_soldout_at');
				$this->unschedule_delete($document_id);
				// Ensure the post is published if it was previously drafted
				if (get_post_status($post_id) === 'draft') {
					wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
				}
			}
			return;
		}

		if ($post_id && empty($soldout_at)) {
			update_post_meta($post_id, '_wpbs_soldout_at', WPBS_Utils::now_mysql());
			if (get_post_status($post_id) === 'publish') {
				wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));
			}
			$this->schedule_delete($document_id, $days);
		}
	}

	private function schedule_delete($document_id, $days)
	{
		$timestamp = time() + ($days * DAY_IN_SECONDS);
		if (!wp_next_scheduled(WPBS_CRON_DELETE_BOAT, array($document_id))) {
			wp_schedule_single_event($timestamp, WPBS_CRON_DELETE_BOAT, array($document_id));
		}
	}

	private function unschedule_delete($document_id)
	{
		$timestamp = wp_next_scheduled(WPBS_CRON_DELETE_BOAT, array($document_id));
		if ($timestamp) {
			wp_unschedule_event($timestamp, WPBS_CRON_DELETE_BOAT, array($document_id));
		}
	}

	private function start_run($schedule_key)
	{
		$run_id = $schedule_key . '-' . gmdate('YmdHis') . '-' . wp_generate_password(6, false, false);
		update_option(WPBS_OPTION_RUN_STATE, array(
			'run_id' => $run_id,
			'started_at' => WPBS_Utils::now_mysql(),
			'schedule' => $schedule_key,
		));
		return $run_id;
	}

	private function enqueue_job($type, $payload, $not_before = null)
	{
		global $wpdb;
		$table = WPBS_DB::table_queue();

		$now = WPBS_Utils::now_mysql();
		$wpdb->insert($table, array(
			'type' => $type,
			'payload' => WPBS_Utils::json_encode($payload),
			'status' => 'pending',
			'attempts' => 0,
			'not_before' => $not_before,
			'locked_at' => null,
			'last_error' => null,
			'created_at' => $now,
			'updated_at' => $now,
		), array('%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s'));
	}

	private function ensure_processor_scheduled($delay_seconds = 15)
	{
		$delay_seconds = max(0, (int)$delay_seconds);
		if (!wp_next_scheduled(WPBS_CRON_PROCESS_QUEUE)) {
			wp_schedule_single_event(time() + $delay_seconds, WPBS_CRON_PROCESS_QUEUE);
		}
	}

	private function lock_next_jobs($limit)
	{
		global $wpdb;
		$table = WPBS_DB::table_queue();
		$now = WPBS_Utils::now_mysql();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} 
				 WHERE status='pending'
				 AND (not_before IS NULL OR not_before <= %s)
				 ORDER BY id ASC
				 LIMIT %d",
				$now,
				$limit
			),
			ARRAY_A
		);

		if (empty($rows)) {
			return array();
		}

		$ids = array_map(function ($r) {
			return (int)$r['id'];
		}, $rows);

		$id_list = implode(',', array_map('intval', $ids));
		if ($id_list !== '') {
			$wpdb->query("UPDATE {$table} SET status='processing', locked_at='{$now}', updated_at='{$now}' WHERE id IN ({$id_list}) AND status='pending'");
		}

		return $rows;
	}

	private function mark_job_done($job_id)
	{
		global $wpdb;
		$table = WPBS_DB::table_queue();
		$wpdb->update($table, array(
			'status' => 'done',
			'locked_at' => null,
			'updated_at' => WPBS_Utils::now_mysql(),
		), array('id' => (int)$job_id), array('%s', '%s'), array('%d'));
	}

	private function mark_job_failed($job_id, $message)
	{
		global $wpdb;
		$table = WPBS_DB::table_queue();

		$job = $wpdb->get_row($wpdb->prepare("SELECT attempts FROM {$table} WHERE id=%d", (int)$job_id), ARRAY_A);
		$attempts = isset($job['attempts']) ? (int)$job['attempts'] : 0;
		$attempts++;

		$status = ($attempts >= 3) ? 'failed' : 'pending';
		$not_before = null;
		if ($status === 'pending') {
			$not_before = gmdate('Y-m-d H:i:s', time() + min(900, 30 * $attempts));
		}

		$wpdb->update($table, array(
			'status' => $status,
			'attempts' => $attempts,
			'not_before' => $not_before,
			'locked_at' => null,
			'last_error' => wp_strip_all_tags((string)$message),
			'updated_at' => WPBS_Utils::now_mysql(),
		), array('id' => (int)$job_id));

		$this->ensure_processor_scheduled(60);
	}

	private function has_pending_jobs()
	{
		global $wpdb;
		$table = WPBS_DB::table_queue();
		$count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status='pending'");
		return $count > 0;
	}

	private function get_post_id_by_document_id($document_id)
	{
		$ids = $this->get_post_ids_by_document_id($document_id);
		return !empty($ids) ? (int)$ids[0] : 0;
	}

	private function upsert_boat_row($document_id, $fields)
	{
		global $wpdb;
		$table = WPBS_DB::table_boats();
		$now = WPBS_Utils::now_mysql();

		$existing_id = (int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE document_id=%s", $document_id));

		$row = array(
			'document_id' => $document_id,
			'post_id' => isset($fields['post_id']) ? (int)$fields['post_id'] : null,
			'sales_status' => isset($fields['sales_status']) ? (string)$fields['sales_status'] : null,
			'last_modification_date' => isset($fields['last_modification_date']) ? $fields['last_modification_date'] : null,
			'last_seen_run_id' => isset($fields['last_seen_run_id']) ? $fields['last_seen_run_id'] : null,
			'last_sync_at' => isset($fields['last_sync_at']) ? $fields['last_sync_at'] : null,
			'last_sync_status' => isset($fields['last_sync_status']) ? $fields['last_sync_status'] : null,
			'last_error' => isset($fields['last_error']) ? $fields['last_error'] : null,
			'raw_json' => isset($fields['raw_json']) ? $fields['raw_json'] : null,
			'updated_at' => $now,
		);

		if ($existing_id) {
			$wpdb->update($table, $row, array('id' => $existing_id));
			return;
		}

		$row['created_at'] = $now;
		$wpdb->insert($table, $row);
	}

	private function upsert_image_row($document_id, $image_url, $is_featured)
	{
		global $wpdb;
		$table = WPBS_DB::table_images();
		$now = WPBS_Utils::now_mysql();

		$existing_id = (int)$wpdb->get_var($wpdb->prepare(
			"SELECT id FROM {$table} WHERE document_id=%s AND image_url=%s",
			$document_id,
			$image_url
		));

		$row = array(
			'document_id' => $document_id,
			'image_url' => $image_url,
			'is_featured' => $is_featured ? 1 : 0,
			'updated_at' => $now,
		);

		if ($existing_id) {
			$wpdb->update($table, $row, array('id' => $existing_id));
			return;
		}

		$row['created_at'] = $now;
		$wpdb->insert($table, $row);
	}

	private function link_image_attachment($document_id, $image_url, $attachment_id)
	{
		global $wpdb;
		$table = WPBS_DB::table_images();
		$now = WPBS_Utils::now_mysql();

		$wpdb->update($table, array(
			'attachment_id' => (int)$attachment_id,
			'last_sync_at' => $now,
			'updated_at' => $now,
		), array(
			'document_id' => $document_id,
			'image_url' => $image_url,
		));
	}
}
