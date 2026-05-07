<?php

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_DB
{
	public static function table_boats()
	{
		global $wpdb;
		return $wpdb->prefix . 'wpbs_boats';
	}

	public static function table_images()
	{
		global $wpdb;
		return $wpdb->prefix . 'wpbs_images';
	}

	public static function table_queue()
	{
		global $wpdb;
		return $wpdb->prefix . 'wpbs_queue';
	}

	public static function install()
	{
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$boats = self::table_boats();
		$images = self::table_images();
		$queue = self::table_queue();

		$sql_boats = "CREATE TABLE {$boats} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			document_id VARCHAR(64) NOT NULL,
			post_id BIGINT(20) UNSIGNED NULL,
			sales_status VARCHAR(32) NULL,
			last_modification_date DATE NULL,
			last_seen_run_id VARCHAR(64) NULL,
			last_sync_at DATETIME NULL,
			last_sync_status VARCHAR(32) NULL,
			last_error TEXT NULL,
			raw_json LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY document_id (document_id),
			KEY post_id (post_id),
			KEY sales_status (sales_status)
		) {$charset_collate};";

		$sql_images = "CREATE TABLE {$images} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			document_id VARCHAR(64) NOT NULL,
			image_url TEXT NOT NULL,
			attachment_id BIGINT(20) UNSIGNED NULL,
			is_featured TINYINT(1) NOT NULL DEFAULT 0,
			last_sync_at DATETIME NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY document_id (document_id),
			KEY attachment_id (attachment_id)
		) {$charset_collate};";

		$sql_queue = "CREATE TABLE {$queue} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			type VARCHAR(32) NOT NULL,
			payload LONGTEXT NULL,
			status VARCHAR(16) NOT NULL DEFAULT 'pending',
			attempts INT NOT NULL DEFAULT 0,
			not_before DATETIME NULL,
			locked_at DATETIME NULL,
			last_error TEXT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY status (status),
			KEY type (type),
			KEY not_before (not_before)
		) {$charset_collate};";

		dbDelta($sql_boats);
		dbDelta($sql_images);
		dbDelta($sql_queue);

		add_option('wpbs_db_version', WPBS_VERSION);
	}

	/**
	 * Add missing indexes for queue performance.
	 * Safe to call on every init — uses IF NOT EXISTS.
	 */
	public static function add_missing_indexes()
	{
		global $wpdb;
		$queue = self::table_queue();

		// Composite index for the common lock query:
		// WHERE status='pending' AND (not_before IS NULL OR not_before <= NOW()) ORDER BY id ASC
		$index_name = 'status_not_before_id';
		$exists = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM information_schema.statistics
			 WHERE table_schema = DATABASE()
			   AND table_name = %s
			   AND index_name = %s",
			$queue,
			$index_name
		));

		if (!$exists) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query("ALTER TABLE {$queue} ADD INDEX `{$index_name}` (`status`, `not_before`, `id`)");
		}
	}
}
