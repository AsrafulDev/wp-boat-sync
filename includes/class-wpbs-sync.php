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
		$start = microtime(true);

		$jobs = $this->lock_next_jobs($batch_size);
		if (empty($jobs)) {
			return;
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
		$post_id = $this->get_post_id_by_document_id($document_id);

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

		update_post_meta($post_id, '_wpbs_document_id', $document_id);

		$this->update_fields($post_id, $data);

		return (int)$post_id;
	}

	private function update_fields($post_id, $data)
	{
		$price_parsed = WPBS_Utils::parse_price_string(isset($data['Price']) ? $data['Price'] : '');
		$orig_price_parsed = WPBS_Utils::parse_price_string(isset($data['OriginalPrice']) ? $data['OriginalPrice'] : '');

		$boat_location = (isset($data['BoatLocation']) && is_array($data['BoatLocation'])) ? $data['BoatLocation'] : array();
		$office = (isset($data['Office']) && is_array($data['Office'])) ? $data['Office'] : array();
		$sales_rep = (isset($data['SalesRep']) && is_array($data['SalesRep'])) ? $data['SalesRep'] : array();
		$owner = (isset($data['Owner']) && is_array($data['Owner'])) ? $data['Owner'] : array();

		$map = array(
			'wpbs_document_id' => isset($data['DocumentID']) ? (string)$data['DocumentID'] : '',
			'wpbs_sales_status' => isset($data['SalesStatus']) ? (string)$data['SalesStatus'] : '',
			'wpbs_price' => isset($data['Price']) ? (string)$data['Price'] : '',
			'wpbs_price_amount' => $price_parsed['amount'],
			'wpbs_price_currency' => $price_parsed['currency'],
			'wpbs_original_price' => isset($data['OriginalPrice']) ? (string)$data['OriginalPrice'] : '',
			'wpbs_original_price_amount' => $orig_price_parsed['amount'],
			'wpbs_original_price_currency' => $orig_price_parsed['currency'],
			'wpbs_make' => isset($data['MakeString']) ? (string)$data['MakeString'] : '',
			'wpbs_make_exact' => isset($data['MakeStringExact']) ? (string)$data['MakeStringExact'] : '',
			'wpbs_model' => isset($data['Model']) ? (string)$data['Model'] : '',
			'wpbs_model_exact' => isset($data['ModelExact']) ? (string)$data['ModelExact'] : '',
			'wpbs_model_year' => isset($data['ModelYear']) ? (int)$data['ModelYear'] : 0,
			'wpbs_sale_class' => isset($data['SaleClassCode']) ? (string)$data['SaleClassCode'] : '',
			'wpbs_boat_category' => isset($data['BoatCategoryCode']) ? (string)$data['BoatCategoryCode'] : '',
			'wpbs_listing_title' => isset($data['ListingTitle']) ? (string)$data['ListingTitle'] : '',
			'wpbs_company_name' => isset($data['CompanyName']) ? (string)$data['CompanyName'] : '',
			'wpbs_office_name' => isset($office['Name']) ? (string)$office['Name'] : '',
			'wpbs_sales_rep_name' => isset($sales_rep['Name']) ? (string)$sales_rep['Name'] : '',
			'wpbs_owner_party_id' => isset($owner['PartyId']) ? (string)$owner['PartyId'] : '',
			'wpbs_city' => isset($data['BoatCityNameNoCaseAlnumOnly']) ? (string)$data['BoatCityNameNoCaseAlnumOnly'] : '',
			'wpbs_state' => isset($boat_location['BoatStateCode']) ? (string)$boat_location['BoatStateCode'] : '',
			'wpbs_country_code' => isset($data['RegistrationCountryCode']) ? (string)$data['RegistrationCountryCode'] : '',
			'wpbs_length_overall' => isset($data['LengthOverall']) ? (string)$data['LengthOverall'] : '',
			'wpbs_nominal_length' => isset($data['NominalLength']) ? (string)$data['NominalLength'] : '',
			'wpbs_beam' => isset($data['BeamMeasure']) ? (string)$data['BeamMeasure'] : '',
			'wpbs_dry_weight' => isset($data['DryWeightMeasure']) ? (string)$data['DryWeightMeasure'] : '',
			'wpbs_deadrise' => isset($data['DeadriseMeasure']) ? (string)$data['DeadriseMeasure'] : '',
			'wpbs_drive_up' => isset($data['DriveUp']) ? (string)$data['DriveUp'] : '',
			'wpbs_total_engine_power' => isset($data['TotalEnginePowerQuantity']) ? (string)$data['TotalEnginePowerQuantity'] : '',
			'wpbs_number_of_engines' => isset($data['NumberOfEngines']) ? (int)$data['NumberOfEngines'] : 0,
			'wpbs_total_engine_hours' => isset($data['TotalEngineHoursNumeric']) ? (int)$data['TotalEngineHoursNumeric'] : 0,
			'wpbs_fuel_tank_capacity' => isset($data['FuelTankCapacityMeasure']) ? (string)$data['FuelTankCapacityMeasure'] : '',
			'wpbs_water_tank_capacity' => isset($data['WaterTankCapacityMeasure']) ? (string)$data['WaterTankCapacityMeasure'] : '',
			'wpbs_heads_count' => isset($data['HeadsCountNumeric']) ? (int)$data['HeadsCountNumeric'] : 0,
			'wpbs_hull_material' => isset($data['BoatHullMaterialCode']) ? (string)$data['BoatHullMaterialCode'] : '',
			'wpbs_hull_id' => isset($data['BoatHullID']) ? (string)$data['BoatHullID'] : '',
			'wpbs_stock_number' => isset($data['StockNumber']) ? (string)$data['StockNumber'] : '',
			'wpbs_yachtworld_id' => isset($data['YachtWorldID']) ? (string)$data['YachtWorldID'] : '',
			'wpbs_btolid' => isset($data['BtolID']) ? (string)$data['BtolID'] : '',
			'wpbs_bcnaid' => isset($data['BcnaID']) ? (string)$data['BcnaID'] : '',
			'wpbs_last_seen_timestamp' => isset($data['IMTTimeStamp']) ? (string)$data['IMTTimeStamp'] : '',
			'wpbs_last_modification_date' => isset($data['LastModificationDate']) ? (string)$data['LastModificationDate'] : '',
			'wpbs_item_received_date' => isset($data['ItemReceivedDate']) ? (string)$data['ItemReceivedDate'] : '',
			'wpbs_builder_name' => isset($data['BuilderName']) ? (string)$data['BuilderName'] : '',
			'wpbs_designer_name' => isset($data['DesignerName']) ? (string)$data['DesignerName'] : '',
			'wpbs_embedded_video_present' => isset($data['EmbeddedVideoPresent']) ? (int)(bool)$data['EmbeddedVideoPresent'] : 0,
			'wpbs_immersive_tour_present' => isset($data['ImmersiveTourPresent']) ? (int)(bool)$data['ImmersiveTourPresent'] : 0,
			'wpbs_image_360_present' => isset($data['Image360PhotoPresent']) ? (int)(bool)$data['Image360PhotoPresent'] : 0,
			'wpbs_general_description_html' => (!empty($data['GeneralBoatDescription']) && is_array($data['GeneralBoatDescription'])) ? (string)reset($data['GeneralBoatDescription']) : '',
			'wpbs_additional_detail_html' => (!empty($data['AdditionalDetailDescription']) && is_array($data['AdditionalDetailDescription'])) ? (string)reset($data['AdditionalDetailDescription']) : '',
			'wpbs_boat_class_codes' => (!empty($data['BoatClassCode']) && is_array($data['BoatClassCode'])) ? implode(', ', array_map('strval', $data['BoatClassCode'])) : '',
			'wpbs_embedded_video_urls' => (!empty($data['EmbeddedVideo']) && is_array($data['EmbeddedVideo'])) ? implode("\n", array_map('strval', $data['EmbeddedVideo'])) : '',
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

		$parts = array();
		foreach ($data['Engines'] as $engine) {
			if (!is_array($engine)) {
				continue;
			}
			$make = '';
			$model = '';
			$hp = '';
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
			if (!empty($engine['Horsepower'])) {
				$hp = (string)$engine['Horsepower'];
			} elseif (!empty($engine['HP'])) {
				$hp = (string)$engine['HP'];
			}

			$line = trim(implode(' ', array_filter(array($make, $model, $hp ? ($hp . ' hp') : ''))));
			if ($line !== '') {
				$parts[] = $line;
			}
		}

		return implode(' | ', array_values(array_unique($parts)));
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

		$is_active = strtolower((string)$sales_status) === 'active';
		$soldout_at = $post_id ? get_post_meta($post_id, '_wpbs_soldout_at', true) : '';

		if ($is_active) {
			if ($post_id) {
				delete_post_meta($post_id, '_wpbs_soldout_at');
				$this->unschedule_delete($document_id);
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
		$query = new WP_Query(array(
			'post_type' => WPBS_POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => '_wpbs_document_id',
					'value' => $document_id,
					'compare' => '=',
				),
			),
		));

		if (!empty($query->posts)) {
			return (int)$query->posts[0];
		}
		return 0;
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
