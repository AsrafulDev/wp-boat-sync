<?php

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Shortcodes
{
	public function init()
	{
		add_shortcode('wpbs_boat_grid', array($this, 'shortcode_grid'));
		add_shortcode('wpbs_boat_single', array($this, 'shortcode_single'));
	}

	public function shortcode_grid($atts)
	{
		$settings = WPBS_Utils::get_settings();
		$atts = shortcode_atts(array(
			'posts_per_page' => 12,
			'columns' => isset($settings['style_grid_columns']) ? (int)$settings['style_grid_columns'] : 3,
		), $atts);

		$ppp = max(1, (int)$atts['posts_per_page']);
		$cols = max(1, min(6, (int)$atts['columns']));

		$q = new WP_Query(array(
			'post_type' => WPBS_POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => $ppp,
		));

		if (!$q->have_posts()) {
			return '<div class="wpbs-grid">No boats found.</div>';
		}

		$style = 'grid-template-columns:repeat(' . $cols . ',minmax(0,1fr));';
		$out = '<div class="wpbs-grid" style="' . esc_attr($style) . '">';

		while ($q->have_posts()) {
			$q->the_post();
			$post_id = get_the_ID();
			$price = get_post_meta($post_id, 'wpbs_price', true);
			$year = get_post_meta($post_id, 'wpbs_model_year', true);
			$make = get_post_meta($post_id, 'wpbs_make', true);
			$model = get_post_meta($post_id, 'wpbs_model', true);

			$out .= '<article class="wpbs-card" style="border:1px solid #e5e5e5;padding:12px;border-radius:8px;">';
			$out .= '<a href="' . esc_url(get_permalink()) . '" style="text-decoration:none;">';
			if (has_post_thumbnail()) {
				$out .= get_the_post_thumbnail($post_id, 'medium', array('style' => 'width:100%;height:auto;border-radius:6px;'));
			}
			$out .= '<h3 style="margin:10px 0 6px;">' . esc_html(get_the_title()) . '</h3>';
			$out .= '<div style="opacity:.8;">' . esc_html(trim($year . ' ' . $make . ' ' . $model)) . '</div>';
			if ($price) {
				$out .= '<div style="margin-top:6px;font-weight:600;">' . esc_html($price) . '</div>';
			}
			$out .= '</a>';
			$out .= '</article>';
		}

		wp_reset_postdata();
		$out .= '</div>';

		return $out;
	}

	public function shortcode_single($atts)
	{
		$atts = shortcode_atts(array(
			'id' => '',
			'post_id' => 0,
		), $atts);

		$post_id = (int)$atts['post_id'];
		$doc_id = isset($atts['id']) ? preg_replace('/[^0-9A-Za-z_-]/', '', (string)$atts['id']) : '';

		if (!$post_id && $doc_id !== '') {
			$q = new WP_Query(array(
				'post_type' => WPBS_POST_TYPE,
				'post_status' => 'any',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key' => '_wpbs_document_id',
						'value' => $doc_id,
						'compare' => '=',
					),
				),
			));
			if (!empty($q->posts)) {
				$post_id = (int)$q->posts[0];
			}
		}

		if (!$post_id) {
			return '<div class="wpbs-single">Boat not found.</div>';
		}

		$post = get_post($post_id);
		if (!$post || $post->post_type !== WPBS_POST_TYPE) {
			return '<div class="wpbs-single">Boat not found.</div>';
		}

		$price = get_post_meta($post_id, 'wpbs_price', true);

		$out = '<div class="wpbs-single" style="border:1px solid #e5e5e5;padding:16px;border-radius:8px;">';
		$out .= '<h2 style="margin-top:0;">' . esc_html(get_the_title($post_id)) . '</h2>';
		if (has_post_thumbnail($post_id)) {
			$out .= get_the_post_thumbnail($post_id, 'large', array('style' => 'width:100%;height:auto;border-radius:6px;margin:10px 0;'));
		}
		if ($price) {
			$out .= '<div style="font-weight:700;margin:6px 0 12px;">' . esc_html($price) . '</div>';
		}
		$out .= apply_filters('the_content', $post->post_content);
		$out .= '</div>';

		return $out;
	}
}
