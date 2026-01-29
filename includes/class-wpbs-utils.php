<?php

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Utils
{
	public static function now_mysql()
	{
		return current_time('mysql');
	}

	public static function get_settings()
	{
		$defaults = array(
			'api_key' => '',
			'party_id' => '',
			'auto_sync_frequency' => 'off',
			'rows_per_page' => 100,
			'download_images' => 1,
			'max_images_per_boat' => 25,
			'treat_missing_as_sold' => 1,
			'delete_after_days' => 15,
			'processor_batch_size' => 20,
			'processor_initial_delay_seconds' => 2,
			'processor_reschedule_seconds' => 5,
			'use_default_templates' => 1,
			'style_grid_columns' => 3,
			'style_grid_gap' => 16,
			'style_font_size' => 16,
			'style_accent_color' => '#0b5fff',
		);

		$saved = get_option(WPBS_OPTION_SETTINGS, array());
		if (!is_array($saved)) {
			$saved = array();
		}
		$merged = array_merge($defaults, $saved);
		$merged['rows_per_page'] = max(1, min(100, (int)$merged['rows_per_page']));
		return $merged;
	}

	public static function update_settings($settings)
	{
		$settings = is_array($settings) ? $settings : array();
		$merged = array_merge(self::get_settings(), $settings);
		update_option(WPBS_OPTION_SETTINGS, $merged);
		return $merged;
	}

	public static function json_encode($value)
	{
		return wp_json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}

	public static function json_decode_assoc($json)
	{
		$decoded = json_decode($json, true);
		return is_array($decoded) ? $decoded : null;
	}

	public static function sanitize_document_id($id)
	{
		$id = is_scalar($id) ? (string)$id : '';
		return preg_replace('/[^0-9A-Za-z_-]/', '', $id);
	}

	public static function parse_price_string($price)
	{
		$price = is_scalar($price) ? (string)$price : '';
		$price = trim($price);
		if ($price === '') {
			return array('amount' => null, 'currency' => null);
		}

		// Examples: "159900.00 USD", "159900 USD"
		$parts = preg_split('/\s+/', $price);
		$amount = null;
		$currency = null;
		if (is_array($parts) && count($parts) >= 1) {
			$amount_str = preg_replace('/[^0-9.\-]/', '', (string)$parts[0]);
			if ($amount_str !== '' && is_numeric($amount_str)) {
				$amount = (float)$amount_str;
			}
		}
		if (is_array($parts) && count($parts) >= 2) {
			$currency = preg_replace('/[^A-Za-z]/', '', (string)$parts[1]);
			$currency = $currency !== '' ? strtoupper($currency) : null;
		}

		return array('amount' => $amount, 'currency' => $currency);
	}
}
