<?php
/**
 * Plugin Name: WP Boat Sync
 * Description: Sync Boats.com inventory into WordPress (Custom Post Type, post meta, and custom DB tables).
 *              Provides scheduled asynchronous imports, manual sync tools, and queue processing for large
 *              inventories. Features include API credentials management, field mapping, image import,
 *              delta updates to minimize API usage, deletion handling, detailed logging, WP-CLI commands,
 *              and admin UI for monitoring sync status and run history.
 * Version: 1.1.4
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Plugin URI: https://github.com/AsrafulDev/wp-boat-sync
 * Author: Md Asraful Islam
 * Author URI: https://asraful.com.bd/
 * Copyright: 2026 Md Asraful Islam
 * Copyright Year: 2026
 * Text Domain: wpbs
 * Company : SoftMit Technology
 * Company URI: https://softmit.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
	exit;
}

define('WPBS_VERSION', '1.1.4');
define('WPBS_PLUGIN_FILE', __FILE__);
define('WPBS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPBS_PLUGIN_URL', plugin_dir_url(__FILE__));

define('WPBS_OPTION_SETTINGS', 'wpbs_settings');
define('WPBS_OPTION_RUN_STATE', 'wpbs_run_state');

define('WPBS_POST_TYPE', 'boats');

define('WPBS_CRON_AUTO_SYNC', 'wpbs_cron_auto_sync');
define('WPBS_CRON_PROCESS_QUEUE', 'wpbs_process_queue');
define('WPBS_CRON_DELETE_BOAT', 'wpbs_delete_boat');
define('WPBS_CRON_CLEANUP_QUEUE', 'wpbs_cleanup_queue');

require_once WPBS_PLUGIN_DIR . 'includes/class-wpbs-plugin.php';

register_activation_hook(__FILE__, array('WPBS_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('WPBS_Plugin', 'deactivate'));

add_action('plugins_loaded', function () {
	WPBS_Plugin::instance()->init();
});
