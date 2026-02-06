<?php
/**
 * Elementor Archive Template for Taxonomy Pages
 * This template is used when Elementor is active and assigned to taxonomy archives
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

// Check if Elementor Canvas template
$is_canvas = get_post_meta(get_the_ID(), '_wp_page_template', true) === 'elementor_canvas';

if (!$is_canvas) {
	get_header();
}

// Get the Elementor content
if (class_exists('\Elementor\Plugin')) {
	$elementor = \Elementor\Plugin::instance();
	
	// Check if we're on a taxonomy page
	if (is_tax('brand') || is_tax('boat_status')) {
		$term = get_queried_object();
		
		// Display page title if not canvas
		if (!$is_canvas && $term) {
			echo '<div class="wpbs-elementor-archive-header">';
			echo '<h1 class="wpbs-archive-title">';
			if (is_tax('brand')) {
				echo 'Brand: ' . esc_html($term->name);
			} else {
				echo 'Status: ' . esc_html($term->name);
			}
			echo '</h1>';
			echo '</div>';
		}
	}
	
	// Output Elementor content
	echo $elementor->frontend->get_builder_content_for_display(get_the_ID());
}

if (!$is_canvas) {
	get_footer();
}
