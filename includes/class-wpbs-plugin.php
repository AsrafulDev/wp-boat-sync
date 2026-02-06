<?php

if (!defined('ABSPATH')) {
	exit;
}

require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-db.php';
require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-utils.php';
require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-api.php';
require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-sync.php';
require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-admin.php';
require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-shortcodes.php';

class WPBS_Plugin
{
	private static $instance = null;

	/** @var WPBS_Sync */
	public $sync;

	/** @var WPBS_Admin */
	public $admin;

	/** @var WPBS_Shortcodes */
	public $shortcodes;

	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init()
	{
		$this->sync = new WPBS_Sync(new WPBS_API());
		$this->shortcodes = new WPBS_Shortcodes();
		$this->shortcodes->init();

		add_action('init', array($this, 'register_cpt'));
		add_action('init', array($this, 'add_rewrite_rules'));
		add_filter('query_vars', array($this, 'add_query_vars'));
		add_filter('template_include', array($this, 'template_include'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

		// Elementor integration
		add_action('elementor/widgets/register', array($this, 'register_elementor_widgets'));

		// AJAX filter for boats (public - both logged in and not)
		add_action('wp_ajax_wpbs_filter_boats', array($this, 'ajax_filter_boats'));
		add_action('wp_ajax_nopriv_wpbs_filter_boats', array($this, 'ajax_filter_boats'));

		add_filter('cron_schedules', array($this, 'add_cron_schedules'));

		add_action(WPBS_CRON_AUTO_SYNC, function () {
			$this->sync->enqueue_full_sync('auto');
		});

		add_action(WPBS_CRON_PROCESS_QUEUE, array($this->sync, 'process_queue'));
		add_action(WPBS_CRON_DELETE_BOAT, array($this->sync, 'delete_boat_after_grace'), 10, 1);
		add_action(WPBS_CRON_CLEANUP_QUEUE, array($this->sync, 'cleanup_old_queue_jobs'));

		// Ensure cleanup cron is scheduled (for existing installations)
		self::schedule_cleanup_cron();

		if (is_admin()) {
			$this->admin = new WPBS_Admin($this->sync);
			$this->admin->init();
		}

		$this->shortcodes->init();
	}

	public static function activate()
	{
		WPBS_DB::install();
		self::schedule_cron_events();
		// Ensure rewrite rules include our /brand/ index
		flush_rewrite_rules();
		self::schedule_cleanup_cron();
	}

	public static function deactivate()
	{
		wp_clear_scheduled_hook(WPBS_CRON_AUTO_SYNC);
		wp_clear_scheduled_hook(WPBS_CRON_PROCESS_QUEUE);
		wp_clear_scheduled_hook(WPBS_CRON_DELETE_BOAT);
		wp_clear_scheduled_hook(WPBS_CRON_CLEANUP_QUEUE);
		// Flush rewrite rules to remove custom rules
		flush_rewrite_rules();
	}

	/**
	 * Add custom rewrite rules for plugin endpoints.
	 */
	public function add_rewrite_rules()
	{
		add_rewrite_rule('^brand/?$', 'index.php?wpbs_brand_index=1', 'top');
	}

	/**
	 * Allow custom query vars
	 */
	public function add_query_vars($vars)
	{
		$vars[] = 'wpbs_brand_index';
		return $vars;
	}

	public static function schedule_cron_events()
	{
		$settings = WPBS_Utils::get_settings();
		$freq = isset($settings['auto_sync_frequency']) ? (string)$settings['auto_sync_frequency'] : 'off';
		self::reschedule_auto_sync($freq);
	}

	public static function schedule_cleanup_cron()
	{
		if (!wp_next_scheduled(WPBS_CRON_CLEANUP_QUEUE)) {
			wp_schedule_event(time() + 3600, 'daily', WPBS_CRON_CLEANUP_QUEUE);
		}
	}

	public static function reschedule_auto_sync($frequency)
	{
		$frequency = is_string($frequency) ? strtolower($frequency) : 'off';
		$frequency = in_array($frequency, array('off', 'hourly', 'daily'), true) ? $frequency : 'off';

		wp_clear_scheduled_hook(WPBS_CRON_AUTO_SYNC);
		if ($frequency === 'off') {
			return;
		}

		if (!wp_next_scheduled(WPBS_CRON_AUTO_SYNC)) {
			wp_schedule_event(time() + 60, $frequency, WPBS_CRON_AUTO_SYNC);
		}
	}

	public function add_cron_schedules($schedules)
	{
		return $schedules;
	}

	public function register_cpt()
	{
		$labels = array(
			'name' => __('Boats', 'wpbs'),
			'singular_name' => __('Boat', 'wpbs'),
		);

		register_post_type(WPBS_POST_TYPE, array(
			'labels' => $labels,
			'public' => true,
			'has_archive' => true,
			'show_in_rest' => true,
			'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
			'menu_icon' => 'dashicons-admin-site-alt3',
			'rewrite' => array('slug' => 'boats'),
		));

		// Register boat_status taxonomy for Sold/Available labels
		register_taxonomy('boat_status', WPBS_POST_TYPE, array(
			'labels' => array(
				'name' => __('Boat Status', 'wpbs'),
				'singular_name' => __('Status', 'wpbs'),
				'search_items' => __('Search Statuses', 'wpbs'),
				'all_items' => __('All Statuses', 'wpbs'),
				'edit_item' => __('Edit Status', 'wpbs'),
				'update_item' => __('Update Status', 'wpbs'),
				'add_new_item' => __('Add New Status', 'wpbs'),
				'new_item_name' => __('New Status Name', 'wpbs'),
				'menu_name' => __('Status', 'wpbs'),
			),
			'public' => true,
			'hierarchical' => false,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_rest' => true,
			'rewrite' => array('slug' => 'boat-status'),
		));

		// Ensure default terms exist
		if (!term_exists('sold', 'boat_status')) {
			wp_insert_term('Sold', 'boat_status', array('slug' => 'sold'));
		}
		if (!term_exists('available', 'boat_status')) {
			wp_insert_term('Available', 'boat_status', array('slug' => 'available'));
		}

		// Register Brand taxonomy (boat manufacturer / make)
		register_taxonomy('brand', WPBS_POST_TYPE, array(
			'labels' => array(
				'name' => __('Brands', 'wpbs'),
				'singular_name' => __('Brand', 'wpbs'),
				'show_ui' => __('Show Brands', 'wpbs'),
			),
			'public' => true,
			'hierarchical' => false,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_rest' => true,
			'rewrite' => array('slug' => 'brand'),
		));
	}

	public function template_include($template)
	{
		$settings = WPBS_Utils::get_settings();
		
		// If default templates are disabled, let theme handle it
		if (empty($settings['use_default_templates'])) {
			return $template;
		}

		// Brand index (/brand/) handled by plugin template when query var is set
		if (get_query_var('wpbs_brand_index')) {
			$brands = WPBS_PLUGIN_DIR . 'templates/brands-index.php';
			if (file_exists($brands)) {
				return $brands;
			}
		}
		
		if (is_post_type_archive(WPBS_POST_TYPE)) {
			$archive = WPBS_PLUGIN_DIR . 'templates/archive-boats.php';
			if (file_exists($archive)) {
				return $archive;
			}
		}
		// Brand or boat_status taxonomy archives
		if (is_tax('brand') || is_tax('boat_status')) {
			// Check if Elementor has a template assigned for this taxonomy
			if (class_exists('\Elementor\Plugin')) {
				$term = get_queried_object();
				if ($term) {
					// Check for Elementor template assigned to this taxonomy
					$document_id = \Elementor\Plugin::instance()->modules_manager->get_modules('theme-builder')->get_conditions_manager()->get_documents_for_location('archive');
					if (!empty($document_id)) {
						// Let Elementor handle it
						return $template;
					}
				}
			}
			
			// Use default plugin template
			$archive = WPBS_PLUGIN_DIR . 'templates/archive-boats.php';
			if (file_exists($archive)) {
				return $archive;
			}
		}
		if (is_singular(WPBS_POST_TYPE)) {
			$single = WPBS_PLUGIN_DIR . 'templates/single-boats.php';
			if (file_exists($single)) {
				return $single;
			}
		}
		return $template;
	}

	public function enqueue_frontend_assets()
	{
		// Always register assets so Elementor can load them
		wp_register_style('wpbs-frontend', WPBS_PLUGIN_URL . 'assets/css/wpbs-frontend.css', array(), WPBS_VERSION);
		wp_register_script('wpbs-gallery', WPBS_PLUGIN_URL . 'assets/js/wpbs-gallery.js', array(), WPBS_VERSION, true);

		// Add inline CSS variables
		$settings = WPBS_Utils::get_settings();
		$vars = array(
			'--wpbs-accent' => isset($settings['style_accent_color']) ? (string)$settings['style_accent_color'] : '#0b5fff',
			'--wpbs-font-size' => isset($settings['style_font_size']) ? (int)$settings['style_font_size'] . 'px' : '16px',
			'--wpbs-grid-gap' => isset($settings['style_grid_gap']) ? (int)$settings['style_grid_gap'] . 'px' : '16px',
			'--wpbs-grid-columns' => isset($settings['style_grid_columns']) ? (int)$settings['style_grid_columns'] : 3,
		);

		$css = ':root{';
		foreach ($vars as $k => $v) {
			$css .= $k . ':' . $v . ';';
		}
		$css .= '}';
		wp_add_inline_style('wpbs-frontend', $css);

		// Localize script with AJAX URL and nonce
		wp_localize_script('wpbs-gallery', 'wpbsFilter', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('wpbs_filter_nonce'),
		));

		// Enqueue on appropriate pages
		$should_enqueue = is_post_type_archive(WPBS_POST_TYPE) || is_singular(WPBS_POST_TYPE) || is_tax('brand') || is_tax('boat_status') || get_query_var('wpbs_brand_index');
		if (!$should_enqueue && !is_admin()) {
			// Also enqueue when shortcodes exist on the current post.
			global $post;
			if ($post && isset($post->post_content) && (has_shortcode($post->post_content, 'wpbs_boat_grid') || has_shortcode($post->post_content, 'wpbs_boat_single'))) {
				$should_enqueue = true;
			}
		}

		if (!$should_enqueue) {
			return;
		}

		wp_enqueue_style('wpbs-frontend');
		wp_enqueue_script('wpbs-gallery');
	}

	/**
	 * AJAX handler for filtering boats
	 */
	public function ajax_filter_boats()
	{
		// Verify nonce
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpbs_filter_nonce')) {
			wp_send_json_error(array('message' => 'Invalid nonce'), 403);
		}

		$paged = isset($_POST['paged']) ? max(1, (int)$_POST['paged']) : 1;
		$posts_per_page = isset($_POST['posts_per_page']) ? max(1, min(100, (int)$_POST['posts_per_page'])) : 12;
		$columns = isset($_POST['columns']) ? max(1, min(6, (int)$_POST['columns'])) : 3;
		$orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';

		// Filter parameters
		$category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
		$builder = isset($_POST['builder']) ? sanitize_text_field($_POST['builder']) : '';
		$location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
		$length_min = isset($_POST['length_min']) ? (float)$_POST['length_min'] : '';
		$length_max = isset($_POST['length_max']) ? (float)$_POST['length_max'] : '';
		$year_min = isset($_POST['year_min']) ? (int)$_POST['year_min'] : '';
		$year_max = isset($_POST['year_max']) ? (int)$_POST['year_max'] : '';
		$price_min = isset($_POST['price_min']) ? (float)$_POST['price_min'] : '';
		$price_max = isset($_POST['price_max']) ? (float)$_POST['price_max'] : '';
		$condition_new = isset($_POST['condition_new']) && $_POST['condition_new'] === '1';
		$condition_used = isset($_POST['condition_used']) && $_POST['condition_used'] === '1';
		$featured = isset($_POST['featured']) && $_POST['featured'] === '1';

		// Build query args
		$args = array(
			'post_type' => WPBS_POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => $posts_per_page,
			'paged' => $paged,
		);

		// Orderby
		switch ($orderby) {
			case 'price_low':
				$args['meta_key'] = 'wpbs_price';
				$args['orderby'] = 'meta_value_num';
				$args['order'] = 'ASC';
				break;
			case 'price_high':
				$args['meta_key'] = 'wpbs_price';
				$args['orderby'] = 'meta_value_num';
				$args['order'] = 'DESC';
				break;
			case 'year':
				$args['meta_key'] = 'wpbs_model_year';
				$args['orderby'] = 'meta_value_num';
				$args['order'] = 'DESC';
				break;
			default:
				$args['orderby'] = 'date';
				$args['order'] = 'DESC';
		}

		// Meta query
		$meta_query = array('relation' => 'AND');

		if ($category) {
			$meta_query[] = array('key' => 'wpbs_boat_category', 'value' => $category, 'compare' => '=');
		}
		if ($builder) {
			$meta_query[] = array('key' => 'wpbs_make', 'value' => $builder, 'compare' => '=');
		}
		if ($location) {
			$meta_query[] = array('key' => 'wpbs_location', 'value' => $location, 'compare' => 'LIKE');
		}
		if ($length_min !== '') {
			$meta_query[] = array('key' => 'wpbs_length_overall', 'value' => $length_min, 'compare' => '>=', 'type' => 'NUMERIC');
		}
		if ($length_max !== '') {
			$meta_query[] = array('key' => 'wpbs_length_overall', 'value' => $length_max, 'compare' => '<=', 'type' => 'NUMERIC');
		}
		if ($year_min !== '') {
			$meta_query[] = array('key' => 'wpbs_model_year', 'value' => $year_min, 'compare' => '>=', 'type' => 'NUMERIC');
		}
		if ($year_max !== '') {
			$meta_query[] = array('key' => 'wpbs_model_year', 'value' => $year_max, 'compare' => '<=', 'type' => 'NUMERIC');
		}
		if ($price_min !== '') {
			$meta_query[] = array('key' => 'wpbs_price', 'value' => $price_min, 'compare' => '>=', 'type' => 'NUMERIC');
		}
		if ($price_max !== '') {
			$meta_query[] = array('key' => 'wpbs_price', 'value' => $price_max, 'compare' => '<=', 'type' => 'NUMERIC');
		}

		// Condition filter (New/Used)
		if ($condition_new && !$condition_used) {
			$meta_query[] = array('key' => 'wpbs_condition', 'value' => 'new', 'compare' => '=');
		} elseif ($condition_used && !$condition_new) {
			$meta_query[] = array('key' => 'wpbs_condition', 'value' => 'used', 'compare' => '=');
		}

		if ($featured) {
			$meta_query[] = array('key' => 'wpbs_featured', 'value' => '1', 'compare' => '=');
		}

		if (count($meta_query) > 1) {
			$args['meta_query'] = $meta_query;
		}

		$query = new WP_Query($args);
		$total = $query->found_posts;
		$max_pages = $query->max_num_pages;

		// Render cards
		ob_start();
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$this->render_boat_card(get_the_ID());
			}
			wp_reset_postdata();
		} else {
			echo '<div class="wpbs-no-results" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background:#fff; border-radius:8px;">';
			echo '<svg width="48" height="48" viewBox="0 0 24 24" fill="#ccc" style="margin-bottom:12px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>';
			echo '<p style="font-size: 16px; color: #666; margin:0;">No boats found matching your criteria.</p>';
			echo '</div>';
		}
		$html = ob_get_clean();

		wp_send_json_success(array(
			'html' => $html,
			'total' => $total,
			'maxPages' => $max_pages,
			'currentPage' => $paged,
		));
	}

	/**
	 * Render a single boat card (used by AJAX)
	 */
	private function render_boat_card($post_id)
	{
		$price = get_post_meta($post_id, 'wpbs_price', true);
		$location = get_post_meta($post_id, 'wpbs_location', true);
		$status = get_post_meta($post_id, 'wpbs_sales_status', true);
		$condition = get_post_meta($post_id, 'wpbs_condition', true);
		$is_sold = $status && strtolower((string)$status) !== 'active';

		$gallery_ids = get_post_meta($post_id, 'wpbs_gallery_attachment_ids', true);
		if (!is_array($gallery_ids)) $gallery_ids = array();
		$featured_id = (int)get_post_thumbnail_id($post_id);
		if ($featured_id) array_unshift($gallery_ids, $featured_id);
		$gallery_ids = array_values(array_unique(array_filter(array_map('intval', $gallery_ids))));
		$slider_images = array_slice($gallery_ids, 0, 4);
		$total_images = count($gallery_ids);

		$price_display = '';
		if ($price) {
			$price_num = (float)preg_replace('/[^0-9.]/', '', $price);
			$price_display = '$' . number_format($price_num);
		}
		?>
		<article class="wpbs-card">
			<div class="wpbs-card__media wpbs-card-slider" data-wpbs-card-slider>
				<a href="<?php the_permalink($post_id); ?>" class="wpbs-card-slider__link">
					<?php if (!empty($slider_images)) : ?>
						<?php foreach ($slider_images as $idx => $img_id) :
							$img_url = wp_get_attachment_image_url($img_id, 'medium_large');
							if (!$img_url) continue;
						?>
						<div class="wpbs-card-slider__slide<?php echo $idx === 0 ? ' is-active' : ''; ?>">
							<img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" loading="lazy">
						</div>
						<?php endforeach; ?>
					<?php elseif (has_post_thumbnail($post_id)) : ?>
						<div class="wpbs-card-slider__slide is-active"><?php echo get_the_post_thumbnail($post_id, 'medium_large'); ?></div>
					<?php endif; ?>
				</a>
				<?php if (count($slider_images) > 1) : ?>
				<button type="button" class="wpbs-card-slider__nav wpbs-card-slider__nav--prev" aria-label="Previous">
					<svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
				</button>
				<button type="button" class="wpbs-card-slider__nav wpbs-card-slider__nav--next" aria-label="Next">
					<svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
				</button>
				<div class="wpbs-card-slider__dots">
					<?php foreach ($slider_images as $idx => $img_id) : ?>
					<span class="wpbs-card-slider__dot<?php echo $idx === 0 ? ' is-active' : ''; ?>"></span>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
				<?php if ($is_sold) : ?>
				<div class="wpbs-card__badge"><span class="wpbs-badge wpbs-badge--sold">Sold</span></div>
				<?php elseif ($condition && strtolower($condition) === 'new') : ?>
				<div class="wpbs-card__badge"><span class="wpbs-badge wpbs-badge--new">New</span></div>
				<?php endif; ?>
				<?php if ($total_images > 0) : ?>
				<div class="wpbs-card__photo-count">
					<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
					<?php echo $total_images; ?>
				</div>
				<?php endif; ?>
			</div>
			<a href="<?php the_permalink($post_id); ?>" class="wpbs-card__body-link">
				<div class="wpbs-card__body">
					<?php if ($price_display) : ?>
					<div class="wpbs-card__price"><?php echo esc_html($price_display); ?></div>
					<?php else : ?>
					<div class="wpbs-card__price">Contact for Price</div>
					<?php endif; ?>
					<h2 class="wpbs-card__title"><?php echo esc_html(get_the_title($post_id)); ?></h2>
					<?php if ($location) : ?>
					<div class="wpbs-card__location">
						<svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
						<?php echo esc_html($location); ?>
					</div>
					<?php endif; ?>
				</div>
			</a>
			<div class="wpbs-card__actions">
				<a href="<?php the_permalink($post_id); ?>#contact" class="wpbs-card__btn wpbs-card__btn--primary">Contact Seller</a>
			</div>
		</article>
		<?php
	}

	/**
	 * Get unique filter options from database
	 */
	public static function get_filter_options()
	{
		global $wpdb;

		$cache_key = 'wpbs_filter_options';
		$cached = get_transient($cache_key);
		if ($cached !== false) {
			return $cached;
		}

		$options = array(
			'categories' => array(),
			'builders' => array(),
			'locations' => array(),
			'years' => array('min' => 0, 'max' => 0),
			'prices' => array('min' => 0, 'max' => 0),
			'lengths' => array('min' => 0, 'max' => 0),
		);

		// Categories
		$categories = $wpdb->get_col($wpdb->prepare(
			"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm 
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
			 WHERE p.post_type = %s AND p.post_status = 'publish' 
			 AND pm.meta_key = 'wpbs_boat_category' AND pm.meta_value != '' 
			 ORDER BY meta_value ASC",
			WPBS_POST_TYPE
		));
		$options['categories'] = array_filter($categories);

		// Builders (make)
		$builders = $wpdb->get_col($wpdb->prepare(
			"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm 
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
			 WHERE p.post_type = %s AND p.post_status = 'publish' 
			 AND pm.meta_key = 'wpbs_make' AND pm.meta_value != '' 
			 ORDER BY meta_value ASC",
			WPBS_POST_TYPE
		));
		$options['builders'] = array_filter($builders);

		// Locations
		$locations = $wpdb->get_col($wpdb->prepare(
			"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm 
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
			 WHERE p.post_type = %s AND p.post_status = 'publish' 
			 AND pm.meta_key = 'wpbs_location' AND pm.meta_value != '' 
			 ORDER BY meta_value ASC",
			WPBS_POST_TYPE
		));
		$options['locations'] = array_filter($locations);

		// Year range
		$year_range = $wpdb->get_row($wpdb->prepare(
			"SELECT MIN(CAST(meta_value AS UNSIGNED)) as min_year, MAX(CAST(meta_value AS UNSIGNED)) as max_year 
			 FROM {$wpdb->postmeta} pm 
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
			 WHERE p.post_type = %s AND p.post_status = 'publish' 
			 AND pm.meta_key = 'wpbs_model_year' AND pm.meta_value != '' AND pm.meta_value REGEXP '^[0-9]+$'",
			WPBS_POST_TYPE
		));
		if ($year_range) {
			$options['years'] = array('min' => (int)$year_range->min_year, 'max' => (int)$year_range->max_year);
		}

		// Price range
		$price_range = $wpdb->get_row($wpdb->prepare(
			"SELECT MIN(CAST(meta_value AS DECIMAL(15,2))) as min_price, MAX(CAST(meta_value AS DECIMAL(15,2))) as max_price 
			 FROM {$wpdb->postmeta} pm 
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
			 WHERE p.post_type = %s AND p.post_status = 'publish' 
			 AND pm.meta_key = 'wpbs_price' AND pm.meta_value != '' AND pm.meta_value > 0",
			WPBS_POST_TYPE
		));
		if ($price_range) {
			$options['prices'] = array('min' => (float)$price_range->min_price, 'max' => (float)$price_range->max_price);
		}

		// Length range
		$length_range = $wpdb->get_row($wpdb->prepare(
			"SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) as min_length, MAX(CAST(meta_value AS DECIMAL(10,2))) as max_length 
			 FROM {$wpdb->postmeta} pm 
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
			 WHERE p.post_type = %s AND p.post_status = 'publish' 
			 AND pm.meta_key = 'wpbs_length_overall' AND pm.meta_value != '' AND pm.meta_value > 0",
			WPBS_POST_TYPE
		));
		if ($length_range) {
			$options['lengths'] = array('min' => (float)$length_range->min_length, 'max' => (float)$length_range->max_length);
		}

		set_transient($cache_key, $options, HOUR_IN_SECONDS);

		return $options;
	}

	/**
	 * Register Elementor widgets
	 */
	public function register_elementor_widgets($widgets_manager)
	{
		// Check if Elementor is active
		if (!did_action('elementor/loaded')) {
			return;
		}

		// Load widget class
		require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-elementor-widget.php';

		// Register widget
		$widgets_manager->register(new WPBS_Elementor_Widget());
	}
}
