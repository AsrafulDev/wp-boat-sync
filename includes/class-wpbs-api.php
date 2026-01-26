<?php

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_API
{
	private $base_url = 'https://api.boats.com/inventory';

	public function search($args)
	{
		$settings = WPBS_Utils::get_settings();
		$api_key = isset($args['api_key']) ? $args['api_key'] : $settings['api_key'];
		$party_id = isset($args['party_id']) ? $args['party_id'] : $settings['party_id'];
		$rows = isset($args['rows']) ? (int)$args['rows'] : (int)$settings['rows_per_page'];
		$offset = isset($args['offset']) ? (int)$args['offset'] : 0;

		if ($api_key === '' || $party_id === '') {
			return new WP_Error('wpbs_missing_settings', 'Missing API key or PartyId.');
		}

		$url = add_query_arg(array(
			'key' => $api_key,
			'rows' => max(1, min(500, $rows)),
			'offset' => max(0, $offset),
			'PartyId' => $party_id,
		), $this->base_url . '/search');

		return $this->get_json($url);
	}

	public function get_by_id($document_id)
	{
		$settings = WPBS_Utils::get_settings();
		$api_key = $settings['api_key'];
		$document_id = WPBS_Utils::sanitize_document_id($document_id);

		if ($api_key === '' || $document_id === '') {
			return new WP_Error('wpbs_missing_settings', 'Missing API key or DocumentID.');
		}

		$url = add_query_arg(array(
			'key' => $api_key,
		), $this->base_url . '/' . rawurlencode($document_id));

		return $this->get_json($url);
	}

	private function get_json($url)
	{
		$response = wp_remote_get($url, array(
			'timeout' => 30,
			'headers' => array(
				'Accept' => 'application/json',
			),
		));

		if (is_wp_error($response)) {
			return $response;
		}

		$code = (int)wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);

		if ($code < 200 || $code >= 300) {
			return new WP_Error('wpbs_http_error', 'HTTP error from Boats.com API.', array(
				'code' => $code,
				'body' => $body,
				'url' => $url,
			));
		}

		$data = json_decode($body, true);
		if (!is_array($data)) {
			return new WP_Error('wpbs_invalid_json', 'Invalid JSON from Boats.com API.', array(
				'body' => $body,
			));
		}

		return $data;
	}
}
