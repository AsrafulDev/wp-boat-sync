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
		if (!$price) return array('display' => '', 'monthly' => '', 'num' => 0);
		$num = (float)preg_replace('/[^0-9.]/', '', $price);
		$display = '$' . number_format($num);
		$monthly = ($num > 5000) ? '$' . number_format(round($num * 0.009), 0) . '/mo*' : '';
		return array('display' => $display, 'monthly' => $monthly, 'num' => $num);
	}

	/**
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
	 * [wpbs_quick_specs] - Quick specs row with icons
	 */
	public function shortcode_quick_specs($atts)
	{
		$post_id = $this->get_boat_id($atts);
		if (!$post_id) return '';

		$meta = $this->get_boat_meta($post_id);

		$out = '<div class="wpbs-quick-specs">';

		$engine_label = $meta['num_engines'] ? $meta['num_engines'] . 'x ' . ($meta['engine_make'] ?: '') : ($meta['engine'] ?: '—');
		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 24 24"><path d="M20.57 14.86L22 13.43 20.57 12 17 15.57 8.43 7 12 3.43 10.57 2 9.14 3.43 7.71 2 5.57 4.14 4.14 2.71 2.71 4.14l1.43 1.43L2 7.71l1.43 1.43L2 10.57 3.43 12 7 8.43 15.57 17 12 20.57 13.43 22l1.43-1.43L16.29 22l2.14-2.14 1.43 1.43 1.43-1.43-1.43-1.43L22 16.29z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Engines</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($engine_label) . '</div>';
		$out .= '</div>';

		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Total Power</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($meta['total_power'] ?: '—') . '</div>';
		$out .= '</div>';

		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Engine Hours</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html($meta['engine_hours'] ?: '—') . '</div>';
		$out .= '</div>';

		$out .= '<div class="wpbs-quick-spec">';
		$out .= '<svg class="wpbs-quick-spec__icon" viewBox="0 0 24 24"><path d="M19.77 7.23l.01-.01-3.72-3.72L15 4.56l2.11 2.11c-.94.36-1.61 1.26-1.61 2.33 0 1.38 1.12 2.5 2.5 2.5.36 0 .69-.08 1-.21v7.21c0 .55-.45 1-1 1s-1-.45-1-1V14c0-1.1-.9-2-2-2h-1V5c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v16h10v-7.5h1.5v5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V9c0-.69-.28-1.32-.73-1.77zM12 10H6V5h6v5z"/></svg>';
		$out .= '<div class="wpbs-quick-spec__label">Fuel</div>';
		$out .= '<div class="wpbs-quick-spec__value">' . esc_html(ucfirst($meta['fuel_type'] ?: '—')) . '</div>';
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
