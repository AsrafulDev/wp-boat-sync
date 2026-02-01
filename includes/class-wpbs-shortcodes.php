<?php
/**
 * WPBS Shortcodes – All boat elements as individual shortcodes
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
		// Grid & Single
		add_shortcode('wpbs_boat_grid', array($this, 'shortcode_grid'));
		add_shortcode('wpbs_boat_single', array($this, 'shortcode_single'));

		// Individual element shortcodes
		add_shortcode('wpbs_slider', array($this, 'shortcode_slider'));
		add_shortcode('wpbs_gallery', array($this, 'shortcode_gallery'));
		add_shortcode('wpbs_quick_specs', array($this, 'shortcode_quick_specs'));
		add_shortcode('wpbs_overview', array($this, 'shortcode_overview'));
		add_shortcode('wpbs_tabs', array($this, 'shortcode_tabs'));
		add_shortcode('wpbs_accordion', array($this, 'shortcode_accordion'));
		add_shortcode('wpbs_tab_description', array($this, 'shortcode_tab_description'));
		add_shortcode('wpbs_tab_measurements', array($this, 'shortcode_tab_measurements'));
		add_shortcode('wpbs_tab_propulsion', array($this, 'shortcode_tab_propulsion'));
		add_shortcode('wpbs_tab_features', array($this, 'shortcode_tab_features'));
		add_shortcode('wpbs_price_card', array($this, 'shortcode_price_card'));
		add_shortcode('wpbs_price', array($this, 'shortcode_price'));
		add_shortcode('wpbs_location', array($this, 'shortcode_location'));
		add_shortcode('wpbs_dealer_card', array($this, 'shortcode_dealer_card'));
		add_shortcode('wpbs_more_boats', array($this, 'shortcode_more_boats'));
	}

	/**
	 * Helper: Get boat post ID from attributes or current post
	 */
	private function get_boat_id($atts)
	{
		$atts = shortcode_atts(array(
			'id' => '',
			'post_id' => 0,
		), $atts);

		$post_id = (int)$atts['post_id'];
		$doc_id = preg_replace('/[^0-9A-Za-z_-]/', '', (string)$atts['id']);

		// 1. Try post_id attribute
		if ($post_id > 0) {
			$post = get_post($post_id);
			if ($post && $post->post_type === WPBS_POST_TYPE) {
				return $post_id;
			}
		}

		// 2. Try id as document ID lookup
		if ($doc_id !== '') {
			$q = new WP_Query(array(
				'post_type' => WPBS_POST_TYPE,
				'post_status' => 'any',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'meta_query' => array(
					array('key' => '_wpbs_document_id', 'value' => $doc_id, 'compare' => '='),
				),
			));
			if (!empty($q->posts)) {
				return (int)$q->posts[0];
			}
		}

		// 3. Fallback to current post ID (works in loops and single pages)
		$current_id = get_the_ID();
		if ($current_id) {
			$current_post = get_post($current_id);
			if ($current_post && $current_post->post_type === WPBS_POST_TYPE) {
				return $current_id;
			}
		}

		// 4. Try queried object for single boat pages
		$queried_id = get_queried_object_id();
		if ($queried_id && $queried_id !== $current_id) {
			$queried_post = get_post($queried_id);
			if ($queried_post && $queried_post->post_type === WPBS_POST_TYPE) {
				return $queried_id;
			}
		}

		return 0;
	}

	/**
	 * Helper: Get all boat meta
	 */
	private function get_boat_meta($post_id)
	{
		return array(
			'price' => get_post_meta($post_id, 'wpbs_price', true),
			'length' => get_post_meta($post_id, 'wpbs_length_overall', true),
			'year' => get_post_meta($post_id, 'wpbs_model_year', true),
			'make' => get_post_meta($post_id, 'wpbs_make', true),
			'model' => get_post_meta($post_id, 'wpbs_model', true),
			'engine' => get_post_meta($post_id, 'wpbs_engine_summary', true),
			'location' => get_post_meta($post_id, 'wpbs_location', true),
			'status' => get_post_meta($post_id, 'wpbs_sales_status', true),
			'hull_material' => get_post_meta($post_id, 'wpbs_hull_material', true),
			'fuel_type' => get_post_meta($post_id, 'wpbs_fuel_type', true),
			'condition' => get_post_meta($post_id, 'wpbs_condition', true),
			'total_power' => get_post_meta($post_id, 'wpbs_total_engine_power', true),
			'engine_hours' => get_post_meta($post_id, 'wpbs_total_engine_hours', true),
			'num_engines' => get_post_meta($post_id, 'wpbs_number_of_engines', true),
			'dealer' => get_post_meta($post_id, 'wpbs_dealer_name', true),
			'beam' => get_post_meta($post_id, 'wpbs_beam', true),
			'dry_weight' => get_post_meta($post_id, 'wpbs_dry_weight', true),
			'cabins' => get_post_meta($post_id, 'wpbs_cabins_count', true),
			'heads' => get_post_meta($post_id, 'wpbs_heads_count', true),
			'engine_type' => get_post_meta($post_id, 'wpbs_engine_type', true),
			'engine_make' => get_post_meta($post_id, 'wpbs_engine_make', true),
			'engine_model' => get_post_meta($post_id, 'wpbs_engine_model', true),
			'propeller' => get_post_meta($post_id, 'wpbs_propeller_type', true),
			'drive_type' => get_post_meta($post_id, 'wpbs_drive_type', true),
			'boat_category' => get_post_meta($post_id, 'wpbs_boat_category', true),
			'boat_class' => get_post_meta($post_id, 'wpbs_boat_class_codes', true),
			'hull_id' => get_post_meta($post_id, 'wpbs_hull_id', true),
			'cruising_speed' => get_post_meta($post_id, 'wpbs_cruising_speed', true),
			'max_speed' => get_post_meta($post_id, 'wpbs_max_speed', true),
			'fuel_capacity' => get_post_meta($post_id, 'wpbs_fuel_tank_capacity', true),
			'water_capacity' => get_post_meta($post_id, 'wpbs_water_tank_capacity', true),
			'office_phone' => get_post_meta($post_id, 'wpbs_office_phone', true),
			'office_email' => get_post_meta($post_id, 'wpbs_office_email', true),
			'draft' => get_post_meta($post_id, 'wpbs_draft', true),
			'displacement' => get_post_meta($post_id, 'wpbs_displacement', true),
			'deadrise' => get_post_meta($post_id, 'wpbs_deadrise', true),
			'bridge_clearance' => get_post_meta($post_id, 'wpbs_bridge_clearance', true),
			'range' => get_post_meta($post_id, 'wpbs_range', true),
		);
	}

	/**
	 * Helper: Get gallery images
	 */
	private function get_gallery_ids($post_id)
	{
		$gallery_ids = get_post_meta($post_id, 'wpbs_gallery_attachment_ids', true);
		if (!is_array($gallery_ids)) {
			$gallery_ids = array();
		}
		$featured_id = (int)get_post_thumbnail_id($post_id);
		if ($featured_id) {
			array_unshift($gallery_ids, $featured_id);
		}
		return array_values(array_unique(array_filter(array_map('intval', $gallery_ids))));
	}

	/**
	 * Helper: Format price
	 */
	private function format_price($price)
	{
		if (!$price) return array('display' => '', 'num' => 0);
		$num = (float)preg_replace('/[^0-9.]/', '', $price);
		$display = '$' . number_format($num);
		return array('display' => $display, 'num' => $num);
	}

	/*
	 * [wpbs_slider] - 4-image slider like archive cards
	 */
	public function shortcode_slider($atts)
	{
		$atts = shortcode_atts(array('id' => '', 'post_id' => 0, 'max' => 4), $atts);
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$gallery_ids = $this->get_gallery_ids($post_id);
		$slider_images = array_slice($gallery_ids, 0, max(1, (int)$atts['max']));
		$total = count($slider_images);

		if ($total === 0) return '';

		$out = '<div class="wpbs-card-slider" data-wpbs-card-slider style="position:relative;aspect-ratio:4/3;background:#e9ecef;border-radius:8px;overflow:hidden;">';

		foreach ($slider_images as $idx => $img_id) {
			$url = wp_get_attachment_image_url($img_id, 'medium_large');
			if (!$url) continue;
			$active = $idx === 0 ? ' is-active' : '';
			$out .= '<div class="wpbs-card-slider__slide' . $active . '"><img src="' . esc_url($url) . '" alt="' . esc_attr(get_the_title($post_id)) . '" loading="lazy" style="width:100%;height:100%;object-fit:cover;"></div>';
		}

		if ($total > 1) {
			$out .= '<button type="button" class="wpbs-card-slider__nav wpbs-card-slider__nav--prev" aria-label="Previous"><svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg></button>';
			$out .= '<button type="button" class="wpbs-card-slider__nav wpbs-card-slider__nav--next" aria-label="Next"><svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg></button>';
			$out .= '<div class="wpbs-card-slider__dots">';
			foreach ($slider_images as $idx => $img_id) {
				$active = $idx === 0 ? ' is-active' : '';
				$out .= '<span class="wpbs-card-slider__dot' . $active . '"></span>';
			}
			$out .= '</div>';
		}

		$out .= '</div>';
		return $out;
	}

	/**
	 * [wpbs_gallery] - Full gallery with thumbnails (like single page)
	 */
	public function shortcode_gallery($atts)
	{
		$atts = shortcode_atts(array('id' => '', 'post_id' => 0, 'lightbox' => 'yes'), $atts);
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$gallery_ids = $this->get_gallery_ids($post_id);
		$total = count($gallery_ids);
		if ($total === 0) return '';

		$main_id = (int)$gallery_ids[0];
		$main_large = wp_get_attachment_image_url($main_id, 'large');

		$gallery_items = array();
		foreach ($gallery_ids as $aid) {
			$gallery_items[] = array(
				'type' => 'image',
				'thumb' => wp_get_attachment_image_url($aid, 'thumbnail'),
				'large' => wp_get_attachment_image_url($aid, 'large'),
				'full' => wp_get_attachment_image_url($aid, 'full'),
			);
		}

		$out = '<div class="wpbs-gallery" data-wpbs-gallery>';
		$out .= '<div class="wpbs-gallery__main" data-wpbs-lightbox-trigger>';
		$out .= '<div class="wpbs-gallery__main-link" id="wpbs-main-link" data-index="0">';
		$out .= '<img id="wpbs-main-img" class="wpbs-gallery__main-img" src="' . esc_url($main_large) . '" alt="' . esc_attr(get_the_title($post_id)) . '">';
		$out .= '</div>';

		if ($total > 1) {
			$out .= '<button type="button" class="wpbs-gallery__nav wpbs-gallery__nav--prev" aria-label="Previous" data-wpbs-nav="prev"><svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg></button>';
			$out .= '<button type="button" class="wpbs-gallery__nav wpbs-gallery__nav--next" aria-label="Next" data-wpbs-nav="next"><svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg></button>';
		}

		$out .= '<button type="button" class="wpbs-gallery__view-btn" data-wpbs-open-lightbox><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>View ' . $total . ' Photos</button>';
		$out .= '</div>';

		if ($total > 1) {
			$out .= '<div class="wpbs-gallery__thumbs" role="list">';
			foreach (array_slice($gallery_ids, 0, 20) as $i => $aid) {
				$thumb = wp_get_attachment_image_url($aid, 'thumbnail');
				$large = wp_get_attachment_image_url($aid, 'large');
				$full = wp_get_attachment_image_url($aid, 'full');
				$active = $i === 0 ? ' is-active' : '';
				$out .= '<button type="button" class="wpbs-gallery__thumb' . $active . '" data-index="' . $i . '" data-type="image" data-large="' . esc_url($large) . '" data-full="' . esc_url($full) . '"><img src="' . esc_url($thumb) . '" alt="" loading="lazy"></button>';
			}
			$out .= '</div>';
		}
		$out .= '</div>';

		if ($atts['lightbox'] === 'yes') {
			$out .= '<div class="wpbs-lightbox" id="wpbs-lightbox" style="display:none;">';
			$out .= '<div class="wpbs-lightbox__overlay"></div>';
			$out .= '<div class="wpbs-lightbox__container">';
			$out .= '<button type="button" class="wpbs-lightbox__close" aria-label="Close"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>';
			$out .= '<button type="button" class="wpbs-lightbox__nav wpbs-lightbox__nav--prev" aria-label="Previous"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg></button>';
			$out .= '<button type="button" class="wpbs-lightbox__nav wpbs-lightbox__nav--next" aria-label="Next"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg></button>';
			$out .= '<div class="wpbs-lightbox__content"><img class="wpbs-lightbox__img" id="wpbs-lightbox-img" src="" alt=""><div class="wpbs-lightbox__video" id="wpbs-lightbox-video" style="display:none;"></div></div>';
			$out .= '<div class="wpbs-lightbox__counter"><span id="wpbs-lightbox-current">1</span> / <span id="wpbs-lightbox-total">' . $total . '</span></div>';
			$out .= '</div></div>';
			$out .= '<script>var wpbsGalleryItems = ' . json_encode($gallery_items) . ';</script>';
		}

		return $out;
	}

	/**
	 * [wpbs_quick_specs] - Quick specs row with 8 icons
	 */
	public function shortcode_quick_specs($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$meta = $this->get_boat_meta($post_id);
		$capacity = get_post_meta($post_id, 'wpbs_passenger_capacity', true);

		$out = '<div class="wpbs-quick-specs">';

		// 1. Engine
		$engine_label = $meta['engine'] ?: ($meta['engine_make'] && $meta['engine_model'] ? $meta['engine_make'] . ' ' . $meta['engine_model'] : '—');
		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m347.35,178.61c-.15,0-.3,0-.45-.02h-165.87c-2.76,0-5-2.24-5-5v-47.05c0-15.55,12.66-28.21,28.22-28.21h119.89c15.56,0,28.21,12.66,28.21,28.21v47.07c0,1.43-.61,2.79-1.68,3.74-.92.82-2.1,1.26-3.32,1.26Zm-161.32-10.02h156.32v-42.05c0-10.04-8.17-18.21-18.21-18.21h-119.89c-10.05,0-18.22,8.17-18.22,18.21v42.05Z"/><path fill="#104f79" d="m318.65,244.53h-108.92c-13.29,0-24.87-9-28.17-21.88l-5.37-20.94c-.1-.41-.16-.82-.16-1.24v-.38c0-2.76,2.24-5,5-5h165.81c.08,0,.15,0,.23,0,1.37-.05,2.72.44,3.71,1.38.99.94,1.57,2.25,1.57,3.61v.39c0,.42-.05.83-.16,1.24l-5.36,20.94c-3.3,12.88-14.89,21.88-28.18,21.88Z"/><path fill="#104f79" d="m233.76,413.24c-14.88,0-26.98-12.1-26.98-26.98v-145.31c0-2.76,2.24-5,5-5h86.82c1.57,0,3.05.74,4,2,.94,1.26,1.24,2.88.81,4.39l-43.73,151.41c-3.31,11.48-13.97,19.49-25.92,19.49Z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Engine</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($engine_label) . '</div>';
		$out .= '</div>';

		// 2. Total Power
		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m258,306.66c-27.11,0-49.17-22.06-49.17-49.17s22.06-49.17,49.17-49.17,49.17,22.06,49.17,49.17-22.06,49.17-49.17,49.17Zm0-88.33c-21.6,0-39.17,17.57-39.17,39.17s17.57,39.17,39.17,39.17,39.17-17.57,39.17-39.17-17.57-39.17-39.17-39.17Z"/><path fill="#104f79" d="m220.41,239.31c-1.49,0-2.95-.66-3.94-1.92-34.8-44.45-28.12-75.46-21.05-89.99,10.22-20.98,33.39-34.02,60.47-34.02,20.55,0,38,5.08,50.48,14.69,11.42,8.8,17.97,21.17,17.97,33.94,0,20.28-11.74,26.97-21.16,32.35-9.41,5.37-16.84,9.61-16.12,25.88.12,2.76-2.01,5.09-4.77,5.22-2.73.09-5.09-2.01-5.22-4.77-.99-22.38,11.29-29.38,21.16-35.01,9.01-5.14,16.12-9.19,16.12-23.66,0-25.36-29.41-38.63-58.46-38.63-23.22,0-42.95,10.88-51.48,28.4-6.05,12.43-11.52,39.28,19.93,79.45,1.7,2.17,1.32,5.32-.85,7.02-.91.72-2,1.06-3.08,1.06Z"/><path fill="#104f79" d="m258,277.2c-10.87,0-19.71-8.84-19.71-19.71s8.84-19.71,19.71-19.71,19.71,8.84,19.71,19.71-8.84,19.71-19.71,19.71Zm0-29.41c-5.35,0-9.71,4.35-9.71,9.71s4.35,9.71,9.71,9.71,9.71-4.35,9.71-9.71-4.35-9.71-9.71-9.71Z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Total Power</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($meta['total_power'] ?: '—') . '</div>';
		$out .= '</div>';

		// 3. Engine Hours
		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="19.5" fill="none" stroke="#e0e0e0"/><path fill="#104f79" d="M31.72 15.33c-.3-.71-.68-1.4-1.11-2.04-.43-.64-.92-1.23-1.47-1.78-.54-.54-1.14-1.03-1.78-1.47-.64-.43-1.35-.8-2.04-1.11C23.86 8.32 22.28 8 20.67 8s-3.19.32-4.67.94c-.71.3-1.4.68-2.04 1.11-.64.43-1.23.92-1.78 1.47-.55.54-1.04 1.14-1.47 1.78-.43.64-.8 1.35-1.11 2.04C8.99 16.81 8.67 18.38 8.67 20s.32 3.19.94 4.67c.3.71.68 1.4 1.11 2.04.43.64.92 1.23 1.47 1.78.54.54 1.14 1.03 1.78 1.47.64.43 1.35.8 2.04 1.11 1.48.62 3.05.94 4.67.94s3.19-.32 4.67-.94c.71-.3 1.4-.68 2.04-1.11.64-.43 1.23-.92 1.78-1.47.54-.54 1.03-1.14 1.47-1.78.43-.64.8-1.35 1.11-2.04.62-1.48.94-3.05.94-4.67s-.32-3.19-.94-4.67Zm-.71 8.96c-.57 1.34-1.38 2.54-2.41 3.58s-2.24 1.86-3.58 2.41c-1.38.58-2.86.88-4.37.88s-2.99-.3-4.37-.88c-1.34-.57-2.54-1.38-3.58-2.41s-1.86-2.24-2.41-3.58c-.58-1.38-.88-2.86-.88-4.37s.3-2.99.88-4.37c.57-1.34 1.38-2.54 2.41-3.58s2.24-1.86 3.58-2.41c1.38-.58 2.86-.88 4.37-.88s2.99.3 4.37.88c1.34.57 2.54 1.38 3.58 2.41s1.86 2.24 2.41 3.58c.58 1.38.88 2.86.88 4.37s-.3 2.99-.88 4.37Z"/><path fill="#104f79" d="M23.29 14.08c-.94 1.16-2.98 3.75-3.75 5.38-.3.64-.03 1.41.64 1.71.64.3 1.4.02 1.71-.64.75-1.61 1.47-4.85 1.78-6.31.04-.2-.21-.32-.34-.16l-.04.02Z"/><rect fill="#104f79" x="20.17" y="11.12" width=".99" height="1.19"/><rect fill="#104f79" x="14.04" y="13.37" width=".99" height="1.19" transform="rotate(-45 14.54 13.97)"/><rect fill="#104f79" x="11.79" y="19.51" width="1.19" height=".99"/><rect fill="#104f79" x="14.04" y="25.09" width=".99" height="1.19" transform="rotate(-45 14.54 25.69)"/><rect fill="#104f79" x="25.74" y="25.09" width=".99" height="1.19" transform="rotate(45 26.24 25.69)"/><rect fill="#104f79" x="28.35" y="19.51" width="1.19" height=".99"/><rect fill="#104f79" x="25.74" y="13.37" width=".99" height="1.19" transform="rotate(45 26.24 13.97)"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Engine Hours</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($meta['engine_hours'] ?: '—') . '</div>';
		$out .= '</div>';

		// 4. Class
		$class_label = $meta['boat_class'] ?: ($meta['boat_category'] ?: '—');
		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m163.73,294.58c-4.25,1.36-8.44,1.48-12.52.34-5.4-1.51-8.73-4.75-8.74-4.76-.94-.95-2.22-1.49-3.56-1.49h-.23c-1.07,0-2.1.34-2.96.97-7.45,5.47-14.8,7.25-21.84,5.28-5.4-1.51-8.74-4.75-8.75-4.76-.94-.95-2.22-1.49-3.56-1.49h-.22c-1.06,0-2.1.34-2.96.97-7.45,5.47-14.8,7.25-21.84,5.28-5.33-1.49-8.65-4.67-8.77-4.78-1.94-1.95-5.09-1.96-7.05-.03-1.96,1.94-1.98,5.1-.05,7.07,6.1,6.18,22.02,14.14,40.47,2.66,7.5,5.37,21.48,9.86,37.34,0,4.26,3.06,10.63,5.83,18.25,5.83,3.64,0,7.57-.63,11.68-2.17l-4.69-8.92Z"/><path fill="#104f79" d="m372.08,262.35c-.07-.34-.18-.68-.32-1.01-.6-1.39-1.8-2.43-3.26-2.83l-31.79-8.73-6.94-39.29c-.35-1.98-.9-3.89-1.65-5.68-4.17-10.23-14.17-17.18-25.6-17.18h-17.56v-4.44c0-7.43-6.04-13.47-13.47-13.47h-22.83c-7.43,0-13.47,6.04-13.47,13.47v4.44h-17.56c-13.45,0-24.91,9.61-27.25,22.86l-6.69,37.88-.25,1.41-22.31,6.13h-.01l-9.47,2.6c-1.46.4-2.66,1.44-3.26,2.83s-.54,2.98.16,4.32l46.84,89.21c.86,1.64,2.56,2.67,4.42,2.67h120.53c1.86,0,3.56-1.03,4.43-2.67l46.83-89.21c.54-1.02.71-2.19.48-3.31Z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Class</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($class_label) . '</div>';
		$out .= '</div>';

		// 5. Length
		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m359.98,396.11h-207.96c-16.54,0-30-13.46-30-30v-110.84c0-16.54,13.46-30,30-30h207.96c16.54,0,30,13.46,30,30v110.84c0,16.54-13.46,30-30,30Zm-207.96-160.84c-11.03,0-20,8.97-20,20v110.84c0,11.03,8.97,20,20,20h207.96c11.03,0,20-8.97,20-20v-110.84c0-11.03-8.97-20-20-20h-207.96Z"/><path fill="#104f79" d="m384.98,164h-257.96c-2.76,0-5-2.24-5-5s2.24-5,5-5h257.96c2.76,0,5,2.24,5,5s-2.24,5-5,5Z"/><path fill="#104f79" d="m165.13,202.12c-1.28,0-2.56-.49-3.54-1.46l-38.11-38.11c-.94-.94-1.46-2.21-1.46-3.54s.53-2.6,1.46-3.54l38.11-38.11c1.95-1.95,5.12-1.95,7.07,0,1.95,1.95,1.95,5.12,0,7.07l-34.58,34.58,34.58,34.58c1.95,1.95,1.95,5.12,0,7.07-.98.98-2.26,1.46-3.54,1.46Z"/><path fill="#104f79" d="m346.87,202.12c-1.28,0-2.56-.49-3.54-1.46-1.95-1.95-1.95-5.12,0-7.07l34.58-34.58-34.58-34.58c-1.95-1.95-1.95-5.12,0-7.07,1.95-1.95,5.12-1.95,7.07,0l38.11,38.11c.94.94,1.46,2.21,1.46,3.54s-.53,2.6-1.46,3.54l-38.11,38.11c-.98.98-2.26,1.46-3.54,1.46Z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Length</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($meta['length'] ?: '—') . '</div>';
		$out .= '</div>';

		// 6. Year
		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m365.67,387.07h-221.18c-12.68,0-23-10.32-23-23v-203.13c0-12.68,10.32-23,23-23h221.18c12.68,0,23,10.32,23,23v203.13c0,12.68-10.32,23-23,23Zm-221.18-239.13c-7.17,0-13,5.83-13,13v203.13c0,7.17,5.83,13,13,13h221.18c7.17,0,13-5.83,13-13v-203.13c0-7.17-5.83-13-13-13h-221.18Z"/><path fill="#104f79" d="m383.67,210.53h-257.18c-2.76,0-5-2.24-5-5s2.24-5,5-5h257.18c2.76,0,5,2.24,5,5s-2.24,5-5,5Z"/><path fill="#104f79" d="m152.96,259.33c-1.06-1.99-.13-4.64,1.99-5.71,1.99-.93,4.64-.13,5.71,1.99l14.73,29.46,14.73-29.46c1.06-2.12,3.58-2.92,5.71-1.99,2.12,1.06,3.05,3.72,1.99,5.71l-18.18,36.36v38.75c0,2.52-1.73,4.25-4.25,4.25s-4.25-1.73-4.25-4.25v-38.75l-18.18-36.36Z"/><path fill="#104f79" d="m314.47,174.57c-2.76,0-5-2.24-5-5v-53.01c0-2.76,2.24-5,5-5s5,2.24,5,5v53.01c0,2.76-2.24,5-5,5Zm-118.78,0c-2.76,0-5-2.24-5-5v-53.01c0-2.76,2.24-5,5-5s5,2.24,5,5v53.01c0,2.76-2.24,5-5,5Z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Year</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($meta['year'] ?: '—') . '</div>';
		$out .= '</div>';

		// 7. Model
		$model_label = $meta['model'] ?: ($meta['make'] ? $meta['make'] : '—');
		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m430.37,331.25c-23.1,0-34.69,6.55-46.03,12.85-10.58,5.96-20.66,11.51-41.16,11.51-8.65,0-16.21-1.01-22.93-3.11-7.06-2.18-12.51-5.21-18.39-8.48-4.2-2.35-8.48-4.79-13.44-6.8-9.74-4.03-19.99-5.96-32.42-5.96s-22.76,1.93-32.42,5.96c-5.12,2.1-9.41,4.54-13.61,6.89-10.67,5.96-20.66,11.51-41.16,11.51s-30.57-5.54-41.24-11.51c-4.2-2.35-8.57-4.79-13.61-6.89-7.73-3.28-15.62-5.04-24.86-5.71-2.35-.17-4.96-.25-7.48-.25-2.77,0-5.04,2.27-5.04,4.96s2.27,4.96,5.04,4.96c2.35,0,4.62.08,6.8.25,8.06.59,15.03,2.1,21.75,4.96,4.45,1.85,8.31,4.03,12.6,6.38,11.25,6.3,22.93,12.85,46.03,12.85s34.69-6.55,45.94-12.85c4.2-2.35,8.06-4.54,12.6-6.38,8.57-3.61,17.39-5.21,28.64-5.21s19.99,1.6,28.56,5.21c4.62,1.93,8.65,4.2,12.6,6.38,5.96,3.36,12.09,6.8,20.16,9.32,7.73,2.35,16.21,3.53,25.87,3.53,23.1,0,34.77-6.55,46.03-12.85,10.67-5.96,20.66-11.59,41.16-11.59,2.77,0,4.96-2.18,4.96-4.96s-2.18-4.96-4.96-4.96Z"/><path fill="#104f79" d="m372.08,262.35c-.07-.34-.18-.68-.32-1.01-.6-1.39-1.8-2.43-3.26-2.83l-31.79-8.73-6.94-39.29c-.35-1.98-.9-3.89-1.65-5.68-4.17-10.23-14.17-17.18-25.6-17.18h-17.56v-4.44c0-7.43-6.04-13.47-13.47-13.47h-22.83c-7.43,0-13.47,6.04-13.47,13.47v4.44h-17.56c-13.45,0-24.91,9.61-27.25,22.86l-6.69,37.88-.25,1.41-22.31,6.13h-.01l-9.47,2.6c-1.46.4-2.66,1.44-3.26,2.83s-.54,2.98.16,4.32l46.84,89.21c.86,1.64,2.56,2.67,4.42,2.67h120.53c1.86,0,3.56-1.03,4.43-2.67l46.83-89.21c.54-1.02.71-2.19.48-3.31Z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Model</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($model_label) . '</div>';
		$out .= '</div>';

		// 8. Capacity
		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="19.5" fill="none" stroke="#e0e0e0"/><path fill="#104f79" d="M18.85 11.93c-1.42.39-2.53 1.56-2.9 3.05-.32 1.3.07 2.77.99 3.77l.39.42-.44.16-.44.15-.34-.34c-.21-.2-.55-.44-.86-.6l-.52-.26.22-.27c1.04-1.26 1-3.04-.09-4.27-.74-.83-1.94-1.21-2.97-.92-1.06.29-2 1.29-2.24 2.36-.08.36-.08 1.12 0 1.48.09.41.38.98.67 1.33l.26.31-.34.14c-1.08.46-1.93 1.5-2.16 2.68-.11.51-.13 2.48-.02 2.67.18.34 2.55 1.21 3.32 1.21.26 0 .45-.21.45-.5 0-.31-.13-.4-.77-.54-.69-.15-1.27-.34-1.76-.57l-.37-.18.02-.96c.02-.82.05-1.03.16-1.32.29-.77.88-1.37 1.63-1.66.32-.12.5-.14 1.67-.16.84-.01 1.45.01 1.71.06.45.08 1 .35 1.29.62l.19.18-.44.45c-.81.83-1.28 1.84-1.4 3.02-.04.33-.06 1.11-.05 1.72.02 1.32-.04 1.21.84 1.63 3.35 1.61 7.07 1.61 10.39 0 .91-.44.85-.32.87-1.63.01-.61-.01-1.39-.05-1.72-.12-1.18-.59-2.19-1.4-3.02l-.44-.45.2-.18c.31-.28.86-.55 1.3-.62.25-.05.87-.06 1.71-.06 1.17.01 1.35.03 1.67.16.75.29 1.34.89 1.63 1.66.11.29.14.5.16 1.33l.02.96-.22.12c-.4.23-1.21.48-1.85.62-.69.15-.82.23-.82.54 0 .29.19.5.48.5.28 0 1.11-.2 1.68-.4.67-.23 1.53-.67 1.61-.81.1-.19.08-2.16-.02-2.67-.24-1.16-1.09-2.22-2.16-2.68l-.34-.14.26-.31c.48-.58.74-1.28.74-2.07-.01-.92-.27-1.57-.9-2.23-.64-.67-1.32-.86-2.21-.86-1.64.01-3.03 1.48-3.03 3.2 0 .74.28 1.5.76 2.09l.22.27-.52.26c-.32.16-.65.4-.86.6l-.34.34-.45-.16-.45-.16.21-.17c.52-.45 1.08-1.45 1.24-2.23.36-1.8-.53-3.67-2.17-4.5-.82-.42-1.8-.53-2.65-.32Zm1.53.93c1.11.24 2.11 1.28 2.34 2.44.21 1.04-.08 2.03-.86 2.81-1.22 1.27-2.98 1.27-4.2 0s-1.22-2.54 0-3.81c.76-.79 1.67-1.07 2.72-.85ZM13.5 13.88c.38.18.89.69 1.08 1.11.15.3.16.42.16.93 0 .52-.02.65-.17.98-.21.42-.7.92-1.08 1.11-.23.11-.39.13-.84.13-.49 0-.64-.02-.94-.15-1.24-.63-1.61-2.24-.84-3.38.21-.29.61-.58 1-.73.34-.13 1.15-.13 1.5 0Zm14.3.03c.45.23.84.64 1.03 1.07.14.3.16.43.16.93 0 .52-.02.65-.17.93-.22.45-.61.86-1.03 1.11-.28.15-.39.17-.88.17-.45 0-.61-.03-.84-.13-.39-.19-.87-.62-1.08-1.04-.15-.3-.17-.43-.17-.93 0-.7.11-1.01.52-1.47.47-.57.85-.72 1.52-.69.37.01.56.04.81.15Zm-5.87 6.21c1.34.29 2.49 1.44 2.83 2.82.13.54.18 2.74.07 2.83-.17.14-1.29.59-1.94.78-1.11.33-1.82.43-3.09.43s-1.98-.1-3.09-.43c-.65-.2-1.77-.65-1.94-.78-.11-.09-.06-2.29.07-2.83.34-1.37 1.48-2.51 2.86-2.82.56-.13 2.69-.13 3.23 0Z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Capacity</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($capacity ?: '—') . '</div>';
		$out .= '</div>';

		$out .= '</div>';
		return $out;
	}

	/**
	 * [wpbs_overview] - Boat overview/description
	 */
	public function shortcode_overview($atts)
	{
		$atts = shortcode_atts(array('id' => '', 'post_id' => 0, 'words' => 0), $atts);
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$post = get_post($post_id);
		$meta = $this->get_boat_meta($post_id);
		$is_sold = $meta['status'] && strtolower($meta['status']) !== 'active';

		$content = $post->post_content;
		if ((int)$atts['words'] > 0) {
			$content = wp_trim_words($content, (int)$atts['words'], '...');
		} else {
			$content = apply_filters('the_content', $content);
		}

		$out = '<div class="wpbs-overview">';
		$out .= '<h2 class="wpbs-overview__title">Boat Overview';
		if ($is_sold) {
			$out .= ' <span class="wpbs-badge wpbs-badge--sold">Sold</span>';
		}
		$out .= '</h2>';
		$out .= '<div class="wpbs-overview__text">' . $content . '</div>';
		$out .= '</div>';
		return $out;
	}

	/**
	 * [wpbs_tabs] - Full tabbed interface with all tabs
	 */
	public function shortcode_tabs($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$out = '<div class="wpbs-tabs">';
		$out .= '<div class="wpbs-tabs__nav">';
		$out .= '<button type="button" class="wpbs-tabs__btn is-active" data-tab="description">Description</button>';
		$out .= '<button type="button" class="wpbs-tabs__btn" data-tab="measurements">Measurements</button>';
		$out .= '<button type="button" class="wpbs-tabs__btn" data-tab="propulsion">Propulsion</button>';
		$out .= '<button type="button" class="wpbs-tabs__btn" data-tab="features">Features</button>';
		$out .= '</div>';
		$out .= '<div class="wpbs-tabs__content" id="wpbs-tab-content">';
		$out .= $this->render_tab_description($post_id);
		$out .= $this->render_tab_measurements($post_id, 'none');
		$out .= $this->render_tab_propulsion($post_id, 'none');
		$out .= $this->render_tab_features($post_id, 'none');
		$out .= '</div></div>';

		$out .= '<script>document.addEventListener("DOMContentLoaded",function(){var btns=document.querySelectorAll(".wpbs-tabs__btn"),panes=document.querySelectorAll(".wpbs-tab-pane");btns.forEach(function(b){b.addEventListener("click",function(){var t=b.getAttribute("data-tab");btns.forEach(function(x){x.classList.remove("is-active")});b.classList.add("is-active");panes.forEach(function(p){p.style.display=p.getAttribute("data-pane")===t?"block":"none"})})})});</script>';

		return $out;
	}

	/**
	 * [wpbs_accordion] - Accordion-style boat details (matches single template)
	 */
	public function shortcode_accordion($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$post = get_post($post_id);
		$meta = $this->get_boat_meta($post_id);

		// Additional meta fields for accordion
		$nominal_length = get_post_meta($post_id, 'wpbs_nominal_length', true);
		$min_draft = get_post_meta($post_id, 'wpbs_min_draft', true);
		$cabin_headroom = get_post_meta($post_id, 'wpbs_cabin_headroom', true);
		$engines_json = get_post_meta($post_id, 'wpbs_engines_json', true);
		$keel_type = get_post_meta($post_id, 'wpbs_keel_type', true);
		$trim_tabs = get_post_meta($post_id, 'wpbs_trim_tabs', true);
		$windlass = get_post_meta($post_id, 'wpbs_windlass_type', true);
		$electrical = get_post_meta($post_id, 'wpbs_electrical_circuit', true);
		$builder = get_post_meta($post_id, 'wpbs_builder_name', true);
		$designer = get_post_meta($post_id, 'wpbs_designer_name', true);
		$additional_detail = get_post_meta($post_id, 'wpbs_additional_detail_html', true);
		$boat_city = get_post_meta($post_id, 'wpbs_boat_city', true);
		$state = get_post_meta($post_id, 'wpbs_state', true);
		$country = get_post_meta($post_id, 'wpbs_boat_country', true);

		$out = '<div class="wpbs-accordion-details">';
		$out .= '<h2 class="wpbs-accordion-details__title">Boat Details</h2>';

		// Description
		$out .= '<details class="wpbs-accordion-item" open>';
		$out .= '<summary class="wpbs-accordion-item__header"><h3>Description</h3></summary>';
		$out .= '<div class="wpbs-accordion-item__content">';
		if ($post->post_content) {
			$out .= '<div class="wpbs-description-text" data-wpbs-expandable>';
			$out .= apply_filters('the_content', $post->post_content);
			$out .= '</div>';
			$out .= '<button type="button" class="wpbs-show-more-btn" data-wpbs-toggle-expand>Show More</button>';
		} else {
			$out .= '<p style="color:#666;">No description available.</p>';
		}
		$out .= '</div></details>';

		// Measurements
		$out .= '<details class="wpbs-accordion-item">';
		$out .= '<summary class="wpbs-accordion-item__header"><h3>Measurements</h3></summary>';
		$out .= '<div class="wpbs-accordion-item__content"><div class="wpbs-details-grid">';

		// Dimensions Cell
		$dimensions = array();
		if ($meta['length']) $dimensions['Length Overall'] = $meta['length'];
		if ($nominal_length) $dimensions['Nominal Length'] = $nominal_length;
		if ($min_draft) $dimensions['Min Draft'] = $min_draft;
		if ($meta['beam']) $dimensions['Beam'] = $meta['beam'];
		if ($meta['bridge_clearance']) $dimensions['Bridge Clearance'] = $meta['bridge_clearance'];
		if ($cabin_headroom) $dimensions['Cabin Headroom'] = $cabin_headroom;
		if ($meta['dry_weight']) $dimensions['Dry Weight'] = $meta['dry_weight'];
		if ($meta['displacement']) $dimensions['Displacement'] = $meta['displacement'];

		if (!empty($dimensions)) {
			$out .= '<div class="wpbs-details-cell"><h4>Dimensions</h4><div class="wpbs-details-cell__content">';
			foreach ($dimensions as $label => $value) {
				$out .= '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
			}
			$out .= '</div></div>';
		}

		// Tanks Cell
		$tanks = array();
		if ($meta['water_capacity']) $tanks['Fresh Water Tanks'] = $meta['water_capacity'];
		if ($meta['fuel_capacity']) $tanks['Fuel Tanks'] = $meta['fuel_capacity'];

		if (!empty($tanks)) {
			$out .= '<div class="wpbs-details-cell"><h4>Tanks</h4><div class="wpbs-details-cell__content">';
			foreach ($tanks as $label => $value) {
				$out .= '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
			}
			$out .= '</div></div>';
		}

		// Miscellaneous Cell
		$misc = array();
		if ($meta['cabins']) $misc['Cabins'] = $meta['cabins'];
		if ($meta['deadrise']) $misc['Deadrise At Transom'] = $meta['deadrise'];
		if ($meta['heads']) $misc['Heads'] = $meta['heads'];
		if ($meta['hull_id']) $misc['Hull ID'] = $meta['hull_id'];

		if (!empty($misc)) {
			$out .= '<div class="wpbs-details-cell"><h4>Miscellaneous</h4><div class="wpbs-details-cell__content">';
			foreach ($misc as $label => $value) {
				$out .= '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
			}
			$out .= '</div></div>';
		}

		$out .= '</div></div></details>';

		// Propulsion
		$out .= '<details class="wpbs-accordion-item">';
		$out .= '<summary class="wpbs-accordion-item__header"><h3>Propulsion</h3></summary>';
		$out .= '<div class="wpbs-accordion-item__content"><div class="wpbs-details-grid">';

		// Parse engines JSON
		$engines = array();
		if ($engines_json) {
			$engines = json_decode($engines_json, true);
		}

		if (!empty($engines) && is_array($engines)) {
			$engine_num = 1;
			foreach ($engines as $eng) {
				$out .= '<div class="wpbs-details-cell"><h4>Engine ' . $engine_num . '</h4><div class="wpbs-details-cell__content">';
				if (!empty($eng['Make'])) $out .= '<p><span class="wpbs-details-label">Engine Make:</span><span class="wpbs-details-value">' . esc_html($eng['Make']) . '</span></p>';
				if (!empty($eng['Model'])) $out .= '<p><span class="wpbs-details-label">Engine Model:</span><span class="wpbs-details-value">' . esc_html($eng['Model']) . '</span></p>';
				if (!empty($eng['Year'])) $out .= '<p><span class="wpbs-details-label">Engine Year:</span><span class="wpbs-details-value">' . esc_html($eng['Year']) . '</span></p>';
				if (!empty($eng['EnginePower'])) $out .= '<p><span class="wpbs-details-label">Total Power:</span><span class="wpbs-details-value">' . esc_html($eng['EnginePower']) . '</span></p>';
				if (!empty($eng['Type'])) $out .= '<p><span class="wpbs-details-label">Engine Type:</span><span class="wpbs-details-value">' . esc_html($eng['Type']) . '</span></p>';
				if (!empty($eng['DriveType'])) $out .= '<p><span class="wpbs-details-label">Drive Type:</span><span class="wpbs-details-value">' . esc_html($eng['DriveType']) . '</span></p>';
				if (!empty($eng['Fuel'])) $out .= '<p><span class="wpbs-details-label">Fuel Type:</span><span class="wpbs-details-value">' . esc_html($eng['Fuel']) . '</span></p>';
				if (!empty($eng['PropellerType'])) $out .= '<p><span class="wpbs-details-label">Propeller Type:</span><span class="wpbs-details-value">' . esc_html($eng['PropellerType']) . '</span></p>';
				if (!empty($eng['PropellerMaterial'])) $out .= '<p><span class="wpbs-details-label">Propeller Material:</span><span class="wpbs-details-value">' . esc_html($eng['PropellerMaterial']) . '</span></p>';
				$out .= '</div></div>';
				$engine_num++;
			}
		} else {
			// Fallback to single engine meta
			$out .= '<div class="wpbs-details-cell"><h4>Engine</h4><div class="wpbs-details-cell__content">';
			if ($meta['num_engines']) $out .= '<p><span class="wpbs-details-label">Number of Engines:</span><span class="wpbs-details-value">' . esc_html($meta['num_engines']) . '</span></p>';
			if ($meta['engine_make']) $out .= '<p><span class="wpbs-details-label">Engine Make:</span><span class="wpbs-details-value">' . esc_html($meta['engine_make']) . '</span></p>';
			if ($meta['engine_model']) $out .= '<p><span class="wpbs-details-label">Engine Model:</span><span class="wpbs-details-value">' . esc_html($meta['engine_model']) . '</span></p>';
			if ($meta['engine_type']) $out .= '<p><span class="wpbs-details-label">Engine Type:</span><span class="wpbs-details-value">' . esc_html($meta['engine_type']) . '</span></p>';
			if ($meta['total_power']) $out .= '<p><span class="wpbs-details-label">Total Power:</span><span class="wpbs-details-value">' . esc_html($meta['total_power']) . '</span></p>';
			if ($meta['engine_hours']) $out .= '<p><span class="wpbs-details-label">Engine Hours:</span><span class="wpbs-details-value">' . esc_html($meta['engine_hours']) . '</span></p>';
			if ($meta['fuel_type']) $out .= '<p><span class="wpbs-details-label">Fuel Type:</span><span class="wpbs-details-value">' . esc_html(ucfirst($meta['fuel_type'])) . '</span></p>';
			if ($meta['drive_type']) $out .= '<p><span class="wpbs-details-label">Drive Type:</span><span class="wpbs-details-value">' . esc_html($meta['drive_type']) . '</span></p>';
			if ($meta['propeller']) $out .= '<p><span class="wpbs-details-label">Propeller:</span><span class="wpbs-details-value">' . esc_html($meta['propeller']) . '</span></p>';
			$out .= '</div></div>';
		}

		// Performance Cell
		$performance = array();
		if ($meta['cruising_speed']) $performance['Cruising Speed'] = $meta['cruising_speed'];
		if ($meta['max_speed']) $performance['Max Speed'] = $meta['max_speed'];
		if ($meta['range']) $performance['Range'] = $meta['range'];

		if (!empty($performance)) {
			$out .= '<div class="wpbs-details-cell"><h4>Performance</h4><div class="wpbs-details-cell__content">';
			foreach ($performance as $label => $value) {
				$out .= '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
			}
			$out .= '</div></div>';
		}

		$out .= '</div></div></details>';

		// Features
		$out .= '<details class="wpbs-accordion-item">';
		$out .= '<summary class="wpbs-accordion-item__header"><h3>Features</h3></summary>';
		$out .= '<div class="wpbs-accordion-item__content"><div class="wpbs-details-grid">';

		// General Features Cell
		$features = array();
		if ($meta['condition']) $features['Condition'] = $meta['condition'];
		if ($meta['boat_category']) $features['Category'] = $meta['boat_category'];
		if ($meta['boat_class']) $features['Class'] = $meta['boat_class'];
		if ($meta['hull_material']) $features['Hull Material'] = $meta['hull_material'];
		if ($keel_type) $features['Keel Type'] = $keel_type;

		if (!empty($features)) {
			$out .= '<div class="wpbs-details-cell"><h4>General</h4><div class="wpbs-details-cell__content">';
			foreach ($features as $label => $value) {
				$out .= '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
			}
			$out .= '</div></div>';
		}

		// Electronics Cell
		$electronics = array();
		if ($trim_tabs) $electronics['Trim Tabs'] = '✓';
		if ($windlass) $electronics['Windlass'] = $windlass;
		if ($electrical) $electronics['Electrical Circuit'] = $electrical;

		if (!empty($electronics)) {
			$out .= '<div class="wpbs-details-cell"><h4>Electronics & Equipment</h4><div class="wpbs-details-cell__content">';
			foreach ($electronics as $label => $value) {
				$out .= '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
			}
			$out .= '</div></div>';
		}

		// Builder & Designer Cell
		$builder_info = array();
		if ($builder) $builder_info['Builder'] = $builder;
		if ($designer) $builder_info['Designer'] = $designer;

		if (!empty($builder_info)) {
			$out .= '<div class="wpbs-details-cell"><h4>Builder & Designer</h4><div class="wpbs-details-cell__content">';
			foreach ($builder_info as $label => $value) {
				$out .= '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
			}
			$out .= '</div></div>';
		}

		$out .= '</div></div></details>';

		// More Details (Additional Description)
		if ($additional_detail) {
			$out .= '<details class="wpbs-accordion-item">';
			$out .= '<summary class="wpbs-accordion-item__header"><h4>More Details</h4></summary>';
			$out .= '<div class="wpbs-accordion-item__content">';
			$out .= '<div class="wpbs-additional-details">' . wp_kses_post($additional_detail) . '</div>';
			$out .= '</div></details>';
		}

		// Location
		if ($meta['location']) {
			$out .= '<details class="wpbs-accordion-item" open>';
			$out .= '<summary class="wpbs-accordion-item__header"><h4>Location</h4></summary>';
			$out .= '<div class="wpbs-accordion-item__content">';
			$out .= '<div class="wpbs-location-info">';
			$out .= '<p><strong>' . esc_html($meta['location']) . '</strong></p>';
			if ($boat_city || $state || $country) {
				$out .= '<p>' . esc_html(implode(', ', array_filter(array($boat_city, $state, $country)))) . '</p>';
			}
			$out .= '</div></div></details>';
		}

		// Disclaimer
		$out .= '<details class="wpbs-accordion-item">';
		$out .= '<summary class="wpbs-accordion-item__header"><h4>Disclaimer</h4></summary>';
		$out .= '<div class="wpbs-accordion-item__content">';
		$out .= '<p class="wpbs-disclaimer">The Company offers the details of this vessel in good faith but cannot guarantee or warrant the accuracy of this information nor warrant the condition of the vessel. A buyer should instruct his agents, or his surveyors, to investigate such details as the buyer desires validated. This vessel is offered subject to prior sale, price change, or withdrawal without notice.</p>';
		$out .= '</div></details>';

		$out .= '</div>';

		return $out;
	}

	/**
	 * [wpbs_tab_description] - Description tab only
	 */
	public function shortcode_tab_description($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';
		return $this->render_tab_description($post_id, 'block');
	}

	private function render_tab_description($post_id, $display = 'block')
	{
		$post = get_post($post_id);
		$out = '<div class="wpbs-tab-pane" data-pane="description" style="display:' . $display . ';">';
		if ($post->post_content) {
			$out .= apply_filters('the_content', $post->post_content);
		} else {
			$out .= '<p style="color:#666;">No description available.</p>';
		}
		$out .= '</div>';
		return $out;
	}

	/**
	 * [wpbs_tab_measurements] - Measurements tab only
	 */
	public function shortcode_tab_measurements($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';
		return $this->render_tab_measurements($post_id, 'block');
	}

	private function render_tab_measurements($post_id, $display = 'block')
	{
		$meta = $this->get_boat_meta($post_id);
		$out = '<div class="wpbs-tab-pane" data-pane="measurements" style="display:' . $display . ';">';
		$out .= '<div class="wpbs-specs-table">';

		$specs = array(
			'Length Overall' => $meta['length'],
			'Beam' => $meta['beam'],
			'Draft' => $meta['draft'],
			'Displacement' => $meta['displacement'],
			'Dry Weight' => $meta['dry_weight'],
			'Bridge Clearance' => $meta['bridge_clearance'],
			'Deadrise' => $meta['deadrise'],
			'Cabins' => $meta['cabins'],
			'Heads' => $meta['heads'],
		);

		foreach ($specs as $label => $value) {
			if ($value) {
				$out .= '<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">' . esc_html($label) . '</span><span class="wpbs-specs-row__value">' . esc_html($value) . '</span></div>';
			}
		}

		$out .= '</div></div>';
		return $out;
	}

	/**
	 * [wpbs_tab_propulsion] - Propulsion tab only
	 */
	public function shortcode_tab_propulsion($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';
		return $this->render_tab_propulsion($post_id, 'block');
	}

	private function render_tab_propulsion($post_id, $display = 'block')
	{
		$meta = $this->get_boat_meta($post_id);
		$out = '<div class="wpbs-tab-pane" data-pane="propulsion" style="display:' . $display . ';">';
		$out .= '<div class="wpbs-specs-table">';

		$specs = array(
			'Engine Summary' => $meta['engine'],
			'Number of Engines' => $meta['num_engines'],
			'Total Power' => $meta['total_power'],
			'Engine Hours' => $meta['engine_hours'],
			'Engine Type' => $meta['engine_type'],
			'Engine Make' => $meta['engine_make'],
			'Engine Model' => $meta['engine_model'],
			'Fuel Type' => $meta['fuel_type'],
			'Drive Type' => $meta['drive_type'],
			'Propeller' => $meta['propeller'],
			'Cruising Speed' => $meta['cruising_speed'],
			'Max Speed' => $meta['max_speed'],
			'Fuel Capacity' => $meta['fuel_capacity'],
			'Range' => $meta['range'],
		);

		foreach ($specs as $label => $value) {
			if ($value) {
				$out .= '<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">' . esc_html($label) . '</span><span class="wpbs-specs-row__value">' . esc_html($value) . '</span></div>';
			}
		}

		$out .= '</div></div>';
		return $out;
	}

	/**
	 * [wpbs_tab_features] - Features tab only
	 */
	public function shortcode_tab_features($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';
		return $this->render_tab_features($post_id, 'block');
	}

	private function render_tab_features($post_id, $display = 'block')
	{
		$meta = $this->get_boat_meta($post_id);
		$out = '<div class="wpbs-tab-pane" data-pane="features" style="display:' . $display . ';">';
		$out .= '<div class="wpbs-specs-table">';

		$specs = array(
			'Hull Material' => $meta['hull_material'],
			'Hull ID' => $meta['hull_id'],
			'Boat Category' => $meta['boat_category'],
			'Boat Class' => $meta['boat_class'],
			'Condition' => $meta['condition'],
			'Water Capacity' => $meta['water_capacity'],
		);

		foreach ($specs as $label => $value) {
			if ($value) {
				$out .= '<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">' . esc_html($label) . '</span><span class="wpbs-specs-row__value">' . esc_html($value) . '</span></div>';
			}
		}

		$out .= '</div></div>';
		return $out;
	}

	/**
	 * [wpbs_price_card] - Price card with contact button
	 */
	public function shortcode_price_card($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$meta = $this->get_boat_meta($post_id);
		$price = $this->format_price($meta['price']);
		$is_sold = $meta['status'] && strtolower($meta['status']) !== 'active';
		$title = get_the_title($post_id);

		$out = '<div class="wpbs-price-card">';
		$out .= '<div class="wpbs-price-card__header">';
		if ($price['display']) {
			$out .= '<div class="wpbs-price-card__price">' . esc_html($price['display']) . '</div>';
			if ($price['monthly']) {
				$out .= '<div class="wpbs-price-card__monthly">' . esc_html($price['monthly']) . '</div>';
			}
		} else {
			$out .= '<div class="wpbs-price-card__price">Contact for Price</div>';
		}
		if ($is_sold) {
			$out .= '<span class="wpbs-badge wpbs-badge--sold" style="margin-top:8px;display:inline-block;">Sold</span>';
		}
		$out .= '</div>';
		$out .= '<div class="wpbs-price-card__body">';
		$out .= '<h3 style="margin:0 0 8px;font-size:16px;">' . esc_html($title) . '</h3>';
		if ($meta['location']) {
			$out .= '<p style="margin:0 0 16px;color:#666;font-size:13px;">📍 ' . esc_html($meta['location']) . '</p>';
		}
		$out .= '<a href="' . esc_url(get_permalink($post_id)) . '#contact" class="wpbs-btn wpbs-btn--primary" style="width:100%;">Contact Seller</a>';
		$out .= '</div></div>';
		return $out;
	}

	/**
	 * [wpbs_price] - Just the price
	 */
	public function shortcode_price($atts)
	{
		$atts = shortcode_atts(array('id' => '', 'post_id' => 0, 'monthly' => 'yes'), $atts);
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$meta = $this->get_boat_meta($post_id);
		$price = $this->format_price($meta['price']);

		if (!$price['display']) {
			return '<span class="wpbs-price">Contact for Price</span>';
		}

		$out = '<span class="wpbs-price">' . esc_html($price['display']) . '</span>';
		if ($atts['monthly'] === 'yes' && $price['monthly']) {
			$out .= ' <span class="wpbs-price-monthly" style="font-size:12px;color:#0066cc;">' . esc_html($price['monthly']) . '</span>';
		}
		return $out;
	}

	/**
	 * [wpbs_location] - Just the location
	 */
	public function shortcode_location($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$location = get_post_meta($post_id, 'wpbs_location', true);
		if (!$location) return '';

		return '<span class="wpbs-location"><svg viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>' . esc_html($location) . '</span>';
	}

	/**
	 * [wpbs_dealer_card] - Dealer info card
	 */
	public function shortcode_dealer_card($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$meta = $this->get_boat_meta($post_id);

		$out = '<div class="wpbs-dealer-card">';
		$out .= '<div class="wpbs-dealer-card__header"><h3 style="margin:0;font-size:16px;">Dealer Information</h3></div>';
		$out .= '<div class="wpbs-dealer-card__body">';

		if ($meta['dealer']) {
			$out .= '<div class="wpbs-dealer-card__row"><strong>Dealer:</strong> ' . esc_html($meta['dealer']) . '</div>';
		}
		if ($meta['office_phone']) {
			$out .= '<div class="wpbs-dealer-card__row"><strong>Phone:</strong> <a href="tel:' . esc_attr($meta['office_phone']) . '">' . esc_html($meta['office_phone']) . '</a></div>';
		}
		if ($meta['office_email']) {
			$out .= '<div class="wpbs-dealer-card__row"><strong>Email:</strong> <a href="mailto:' . esc_attr($meta['office_email']) . '">' . esc_html($meta['office_email']) . '</a></div>';
		}
		if ($meta['location']) {
			$out .= '<div class="wpbs-dealer-card__row"><strong>Location:</strong> ' . esc_html($meta['location']) . '</div>';
		}

		$out .= '</div>';
		$out .= '<div class="wpbs-dealer-card__actions">';
		$out .= '<a href="' . esc_url(get_post_type_archive_link('boats')) . '" class="wpbs-btn wpbs-btn--outline">View All Boats</a>';
		$out .= '</div>';
		$out .= '</div>';
		return $out;
	}

	/**
	 * [wpbs_more_boats] - More boats grid or slider
	 */
	public function shortcode_more_boats($atts)
	{
		$atts = shortcode_atts(array(
			'id' => '',
			'post_id' => 0,
			'count' => 4,
			'type' => 'grid',
			'columns' => 4,
			'title' => 'More Boats',
		), $atts);

		$exclude_id = $this->get_boat_id($atts);
		$count = max(1, min(12, (int)$atts['count']));
		$cols = max(1, min(6, (int)$atts['columns']));
		$is_slider = $atts['type'] === 'slider';

		$q = new WP_Query(array(
			'post_type' => WPBS_POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => $count,
			'post__not_in' => $exclude_id ? array($exclude_id) : array(),
			'orderby' => 'rand',
		));

		if (!$q->have_posts()) return '';

		$out = '<div class="wpbs-more-boats">';
		$out .= '<div class="wpbs-more-boats__header">';
		$out .= '<h2 class="wpbs-more-boats__title">' . esc_html($atts['title']) . '</h2>';
		$out .= '<a href="' . esc_url(get_post_type_archive_link('boats')) . '" class="wpbs-more-boats__link">View All Boats</a>';
		$out .= '</div>';

		if ($is_slider) {
			$out .= '<div class="wpbs-more-boats__slider" data-wpbs-boats-slider>';
		} else {
			$out .= '<div class="wpbs-more-boats__grid" style="display:grid;grid-template-columns:repeat(' . $cols . ',1fr);gap:16px;">';
		}

		while ($q->have_posts()) {
			$q->the_post();
			$r_price = get_post_meta(get_the_ID(), 'wpbs_price', true);
			$r_price_fmt = $this->format_price($r_price);

			$out .= '<a href="' . esc_url(get_permalink()) . '" class="wpbs-more-boats__item">';
			$out .= '<div class="wpbs-more-boats__img">';
			if (has_post_thumbnail()) {
				$out .= get_the_post_thumbnail(get_the_ID(), 'medium');
			}
			$out .= '</div>';
			$out .= '<div class="wpbs-more-boats__name">' . esc_html(get_the_title()) . '</div>';
			$out .= '<div class="wpbs-more-boats__price">' . ($r_price_fmt['display'] ?: 'Contact') . '</div>';
			$out .= '</a>';
		}
		wp_reset_postdata();

		$out .= '</div></div>';

		return $out;
	}

	/**
	 * Grid shortcode [wpbs_boat_grid posts_per_page="12" columns="3"]
	 */
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
			return '<div class="wpbs-wrap"><p style="text-align:center;padding:40px;color:#666;">No boats found.</p></div>';
		}

		$out = '<div class="wpbs-wrap">';
		$out .= '<div class="wpbs-grid" style="grid-template-columns:repeat(' . esc_attr($cols) . ',1fr);">';

		while ($q->have_posts()) {
			$q->the_post();
			$post_id = get_the_ID();
			$gallery_ids = $this->get_gallery_ids($post_id);
			$slider_images = array_slice($gallery_ids, 0, 4);
			$total_images = count($gallery_ids);
			$meta = $this->get_boat_meta($post_id);
			$price = $this->format_price($meta['price']);
			$is_sold = $meta['status'] && strtolower($meta['status']) !== 'active';

			$out .= '<article class="wpbs-card">';
			$out .= '<div class="wpbs-card__media wpbs-card-slider" data-wpbs-card-slider>';
			$out .= '<a href="' . esc_url(get_permalink()) . '" class="wpbs-card-slider__link">';

			if (!empty($slider_images)) {
				foreach ($slider_images as $idx => $img_id) {
					$img_url = wp_get_attachment_image_url($img_id, 'medium_large');
					if (!$img_url) continue;
					$active = $idx === 0 ? ' is-active' : '';
					$out .= '<div class="wpbs-card-slider__slide' . $active . '"><img src="' . esc_url($img_url) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy"></div>';
				}
			} elseif (has_post_thumbnail()) {
				$out .= '<div class="wpbs-card-slider__slide is-active">' . get_the_post_thumbnail($post_id, 'medium_large') . '</div>';
			}

			$out .= '</a>';

			if (count($slider_images) > 1) {
				$out .= '<button type="button" class="wpbs-card-slider__nav wpbs-card-slider__nav--prev" aria-label="Previous"><svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg></button>';
				$out .= '<button type="button" class="wpbs-card-slider__nav wpbs-card-slider__nav--next" aria-label="Next"><svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg></button>';
				$out .= '<div class="wpbs-card-slider__dots">';
				foreach ($slider_images as $idx => $img_id) {
					$active = $idx === 0 ? ' is-active' : '';
					$out .= '<span class="wpbs-card-slider__dot' . $active . '"></span>';
				}
				$out .= '</div>';
			}

			if ($is_sold) {
				$out .= '<div class="wpbs-card__badge"><span class="wpbs-badge wpbs-badge--sold">Sold</span></div>';
			} elseif ($meta['condition'] && strtolower($meta['condition']) === 'new') {
				$out .= '<div class="wpbs-card__badge"><span class="wpbs-badge wpbs-badge--new">New</span></div>';
			}

			if ($total_images > 0) {
				$out .= '<div class="wpbs-card__photo-count"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>' . $total_images . '</div>';
			}

			$out .= '</div>';
			$out .= '<a href="' . esc_url(get_permalink()) . '" class="wpbs-card__body-link"><div class="wpbs-card__body">';

			if ($price['display']) {
				$out .= '<div class="wpbs-card__price">' . esc_html($price['display']) . '</div>';
				if ($price['monthly']) {
					$out .= '<div class="wpbs-card__monthly">' . esc_html($price['monthly']) . '</div>';
				}
			} else {
				$out .= '<div class="wpbs-card__price">Contact for Price</div>';
			}

			$out .= '<h3 class="wpbs-card__title">' . esc_html(get_the_title()) . '</h3>';

			if ($meta['location']) {
				$out .= '<div class="wpbs-card__location"><svg viewBox="0 0 24 24" fill="#999"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>' . esc_html($meta['location']) . '</div>';
			}

			$out .= '</div></a>';
			$out .= '<div class="wpbs-card__actions"><a href="' . esc_url(get_permalink()) . '#contact" class="wpbs-card__btn wpbs-card__btn--primary">Contact Seller</a></div>';
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
		$atts = shortcode_atts(array('id' => '', 'post_id' => 0), $atts);
		$post_id = $this->get_boat_id($atts);

		if (!$post_id) {
			return '<div class="wpbs-wrap"><p style="text-align:center;padding:40px;color:#666;">Boat not found.</p></div>';
		}

		$out = '<div class="wpbs-wrap">';
		$out .= $this->shortcode_gallery(array('post_id' => $post_id));
		$out .= $this->shortcode_quick_specs(array('post_id' => $post_id));
		$out .= $this->shortcode_overview(array('post_id' => $post_id, 'words' => 50));
		$out .= '<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;margin-top:20px;">';
		$out .= '<div>' . $this->shortcode_tabs(array('post_id' => $post_id)) . '</div>';
		$out .= '<div>' . $this->shortcode_price_card(array('post_id' => $post_id)) . $this->shortcode_dealer_card(array('post_id' => $post_id)) . '</div>';
		$out .= '</div>';
		$out .= $this->shortcode_more_boats(array('post_id' => $post_id));
		$out .= '</div>';

		return $out;
	}
}
