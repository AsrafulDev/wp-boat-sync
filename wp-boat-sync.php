<?php
/**
 * Plugin Name: WP Boat Sync
 * Description: Sync Boats.com inventory into WordPress (CPT + ACF/meta + custom tables) with scheduled async sync and manual tools.
 * Version: 0.1.0
 * Author: WP Boat Sync
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
	exit;
}

define('WPBS_VERSION', '0.1.0');
define('WPBS_PLUGIN_FILE', __FILE__);
define('WPBS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPBS_PLUGIN_URL', plugin_dir_url(__FILE__));

define('WPBS_OPTION_SETTINGS', 'wpbs_settings');
define('WPBS_OPTION_RUN_STATE', 'wpbs_run_state');

define('WPBS_POST_TYPE', 'boats');

define('WPBS_CRON_AUTO_SYNC', 'wpbs_cron_auto_sync');
define('WPBS_CRON_PROCESS_QUEUE', 'wpbs_process_queue');
define('WPBS_CRON_DELETE_BOAT', 'wpbs_delete_boat');

require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-plugin.php';

register_activation_hook(__FILE__, array('WPBS_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('WPBS_Plugin', 'deactivate'));

add_action('plugins_loaded', function () {
	WPBS_Plugin::instance()->init();
});
