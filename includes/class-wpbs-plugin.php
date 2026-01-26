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

		add_action('init', array($this, 'register_cpt'));
		add_filter('template_include', array($this, 'template_include'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

		add_filter('cron_schedules', array($this, 'add_cron_schedules'));

		add_action(WPBS_CRON_AUTO_SYNC, function () {
			$this->sync->enqueue_full_sync('auto');
		});

		add_action(WPBS_CRON_PROCESS_QUEUE, array($this->sync, 'process_queue'));
		add_action(WPBS_CRON_DELETE_BOAT, array($this->sync, 'delete_boat_after_grace'), 10, 1);

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
	}

	public static function deactivate()
	{
		wp_clear_scheduled_hook(WPBS_CRON_AUTO_SYNC);
		wp_clear_scheduled_hook(WPBS_CRON_PROCESS_QUEUE);
		wp_clear_scheduled_hook(WPBS_CRON_DELETE_BOAT);
	}

	public static function schedule_cron_events()
	{
		$settings = WPBS_Utils::get_settings();
		$freq = isset($settings['auto_sync_frequency']) ? (string)$settings['auto_sync_frequency'] : 'off';
		self::reschedule_auto_sync($freq);
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
	}

	public function template_include($template)
	{
		if (is_post_type_archive(WPBS_POST_TYPE)) {
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
		$should_enqueue = is_post_type_archive(WPBS_POST_TYPE) || is_singular(WPBS_POST_TYPE);
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

		wp_register_style('wpbs-frontend', WPBS_PLUGIN_URL . 'assets/css/wpbs-frontend.css', array(), WPBS_VERSION);
		wp_enqueue_style('wpbs-frontend');

		wp_register_script('wpbs-gallery', WPBS_PLUGIN_URL . 'assets/js/wpbs-gallery.js', array(), WPBS_VERSION, true);
		if (is_singular(WPBS_POST_TYPE) || (isset($post) && $post && isset($post->post_content) && has_shortcode($post->post_content, 'wpbs_boat_single'))) {
			wp_enqueue_script('wpbs-gallery');
		}

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
	}
}
