<?php
/**
 * WPBS Shortcodes – BoatTrader.com exact match styling
 *
 * @package WP_Boat_Sync
 */

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

	/**
	 * Grid shortcode [wpbs_boat_grid posts_per_page="12" columns="3"]
	 */
	public function shortcode_grid($atts)
	{
		$settings = WPBS_Utils::get_settings();
		$atts = shortcode_atts(array(
			'posts_per_page' => 12,
			'columns'        => isset($settings['style_grid_columns']) ? (int)$settings['style_grid_columns'] : 3,
		), $atts);

		$ppp  = max(1, (int)$atts['posts_per_page']);
		$cols = max(1, min(6, (int)$atts['columns']));

		$q = new WP_Query(array(
			'post_type'      => WPBS_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $ppp,
		));

		if (!$q->have_posts()) {
			return '<div class="wpbs-wrap"><p style="text-align:center;padding:40px;color:#666;">No boats found.</p></div>';
		}

		$out = '<div class="wpbs-wrap">';
		$out .= '<div class="wpbs-grid" style="grid-template-columns:repeat(' . esc_attr($cols) . ',1fr);">';

		while ($q->have_posts()) {
			$q->the_post();
			$post_id   = get_the_ID();
			$price     = get_post_meta($post_id, 'wpbs_price', true);
			$location  = get_post_meta($post_id, 'wpbs_location', true);
			$status    = get_post_meta($post_id, 'wpbs_sales_status', true);
			$condition = get_post_meta($post_id, 'wpbs_condition', true);
			$is_sold   = $status && strtolower((string)$status) !== 'active';

			// Count images
			$gallery_ids = get_post_meta($post_id, 'wpbs_gallery_attachment_ids', true);
			$photo_count = is_array($gallery_ids) ? count($gallery_ids) : 0;
			if (has_post_thumbnail()) $photo_count = max(1, $photo_count);

			// Price formatting
			$price_display = '';
			$monthly = '';
			if ($price) {
				$price_num = (float)preg_replace('/[^0-9.]/', '', $price);
				$price_display = '$' . number_format($price_num);
				if ($price_num > 5000) {
					$monthly = '$' . number_format(round($price_num * 0.009), 0) . '/mo*';
				}
			}

			$out .= '<article class="wpbs-card">';
			$out .= '<a href="' . esc_url(get_permalink()) . '">';
			$out .= '<div class="wpbs-card__media">';
			if (has_post_thumbnail()) {
				$out .= get_the_post_thumbnail($post_id, 'medium_large');
			}
			// Badge
			if ($is_sold) {
				$out .= '<div class="wpbs-card__badge"><span class="wpbs-badge wpbs-badge--sold">Sold</span></div>';
			} elseif ($condition && strtolower($condition) === 'new') {
				$out .= '<div class="wpbs-card__badge"><span class="wpbs-badge wpbs-badge--new">New</span></div>';
			}
			// Photo count
			if ($photo_count > 0) {
				$out .= '<div class="wpbs-card__photo-count"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>' . $photo_count . '</div>';
			}
			$out .= '</div>';

			$out .= '<div class="wpbs-card__body">';
			if ($price_display) {
				$out .= '<div class="wpbs-card__price">' . esc_html($price_display) . '</div>';
				if ($monthly) {
					$out .= '<div class="wpbs-card__monthly">' . esc_html($monthly) . '</div>';
				}
			} else {
				$out .= '<div class="wpbs-card__price">Contact for Price</div>';
			}
			$out .= '<h3 class="wpbs-card__title">' . esc_html(get_the_title()) . '</h3>';
			if ($location) {
				$out .= '<div class="wpbs-card__location"><svg viewBox="0 0 24 24" fill="#999"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>' . esc_html($location) . '</div>';
			}
			$out .= '</div></a>';
			$out .= '<div class="wpbs-card__actions" style="padding:0 14px 14px;"><a href="' . esc_url(get_permalink()) . '#contact" class="wpbs-card__btn wpbs-card__btn--primary">Contact Seller</a></div>';
			$out .= '</article>';
		}

		wp_reset_postdata();
		$out .= '</div></div>';

		return $out;
	}

	/**
	 * Single shortcode [wpbs_boat_single id="xxx" post_id="123"]
	 */
	public function shortcode_single($atts)
	{
		$atts = shortcode_atts(array(
			'id'      => '',
			'post_id' => 0,
		), $atts);

		$post_id = (int)$atts['post_id'];
		$doc_id  = isset($atts['id']) ? preg_replace('/[^0-9A-Za-z_-]/', '', (string)$atts['id']) : '';

		if (!$post_id && $doc_id !== '') {
			$q = new WP_Query(array(
				'post_type'      => WPBS_POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => '_wpbs_document_id',
						'value'   => $doc_id,
						'compare' => '=',
					),
				),
			));
			if (!empty($q->posts)) {
				$post_id = (int)$q->posts[0];
			}
		}

		if (!$post_id) {
			return '<div class="wpbs-wrap"><p style="text-align:center;padding:40px;color:#666;">Boat not found.</p></div>';
		}

		$post = get_post($post_id);
		if (!$post || $post->post_type !== WPBS_POST_TYPE) {
			return '<div class="wpbs-wrap"><p style="text-align:center;padding:40px;color:#666;">Boat not found.</p></div>';
		}

		$price     = get_post_meta($post_id, 'wpbs_price', true);
		$year      = get_post_meta($post_id, 'wpbs_model_year', true);
		$make      = get_post_meta($post_id, 'wpbs_make', true);
		$model     = get_post_meta($post_id, 'wpbs_model', true);
		$length    = get_post_meta($post_id, 'wpbs_length_overall', true);
		$engine    = get_post_meta($post_id, 'wpbs_engine_summary', true);
		$location  = get_post_meta($post_id, 'wpbs_location', true);
		$status    = get_post_meta($post_id, 'wpbs_sales_status', true);
		$is_sold   = $status && strtolower((string)$status) !== 'active';

		// Price formatting
		$price_display = '';
		$monthly = '';
		if ($price) {
			$price_num = (float)preg_replace('/[^0-9.]/', '', $price);
			$price_display = '$' . number_format($price_num);
			if ($price_num > 5000) {
				$monthly = '$' . number_format(round($price_num * 0.009), 0) . '/mo*';
			}
		}

		$out = '<div class="wpbs-wrap">';

		// Gallery
		$out .= '<div class="wpbs-gallery" style="margin-bottom:16px;"><div class="wpbs-gallery__main" style="aspect-ratio:16/10;">';
		if (has_post_thumbnail($post_id)) {
			$out .= '<a href="' . esc_url(get_the_post_thumbnail_url($post_id, 'full')) . '" target="_blank" class="wpbs-gallery__main-link">';
			$out .= get_the_post_thumbnail($post_id, 'large', array('class' => 'wpbs-gallery__main-img'));
			$out .= '</a>';
		}
		$out .= '</div></div>';

		// Price Card
		$out .= '<div class="wpbs-price-card" style="margin-bottom:16px;">';
		$out .= '<div class="wpbs-price-card__header">';
		$out .= '<h2 style="font-size:18px;font-weight:600;margin:0 0 4px;">' . esc_html(get_the_title($post_id)) . '</h2>';
		$out .= '<div style="font-size:13px;color:#666;">' . esc_html(trim(($year ?: '') . ' ' . ($make ?: '') . ' ' . ($model ?: ''))) . '</div>';
		if ($location) {
			$out .= '<div style="font-size:12px;color:#999;margin-top:4px;">📍 ' . esc_html($location) . '</div>';
		}
		$out .= '</div>';
		$out .= '<div class="wpbs-price-card__body">';
		if ($price_display) {
			$out .= '<div class="wpbs-price-card__price">' . esc_html($price_display) . '</div>';
			if ($monthly) {
				$out .= '<div class="wpbs-price-card__monthly">' . esc_html($monthly) . '</div>';
			}
		} else {
			$out .= '<div class="wpbs-price-card__price">Contact for Price</div>';
		}
		if ($is_sold) {
			$out .= '<span class="wpbs-badge wpbs-badge--sold" style="margin-top:8px;display:inline-block;">Sold</span>';
		}
		$out .= '<div style="margin-top:16px;"><a href="' . esc_url(get_permalink($post_id)) . '#contact" class="wpbs-btn wpbs-btn--primary">Contact Seller</a></div>';
		$out .= '</div></div>';

		// Specs
		$out .= '<div class="wpbs-tabs"><div class="wpbs-tabs__content">';
		$out .= '<div class="wpbs-specs-table">';
		if ($year) $out .= '<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Year</span><span class="wpbs-specs-row__value">' . esc_html($year) . '</span></div>';
		if ($make) $out .= '<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Make</span><span class="wpbs-specs-row__value">' . esc_html($make) . '</span></div>';
		if ($model) $out .= '<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Model</span><span class="wpbs-specs-row__value">' . esc_html($model) . '</span></div>';
		if ($length) $out .= '<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Length</span><span class="wpbs-specs-row__value">' . esc_html($length) . '</span></div>';
		if ($engine) $out .= '<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Engine</span><span class="wpbs-specs-row__value">' . esc_html($engine) . '</span></div>';
		$out .= '<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Status</span><span class="wpbs-specs-row__value">' . ($is_sold ? 'Sold' : 'Available') . '</span></div>';
		$out .= '</div></div></div>';

		// Description
		if ($post->post_content) {
			$out .= '<div class="wpbs-overview" style="margin-top:16px;"><h3 class="wpbs-overview__title">Description</h3><div class="wpbs-overview__text">' . apply_filters('the_content', $post->post_content) . '</div></div>';
		}

		$out .= '</div>';

		return $out;
	}
}
