<?php
/**
 * WPBS Elementor Widget - Price Card
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Elementor_Price_Card_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'wpbs_price_card';
	}

	public function get_title()
	{
		return esc_html__('Price Card', 'wp-boat-sync');
	}

	public function get_icon()
	{
		return 'eicon-price-table';
	}

	public function get_categories()
	{
		return ['wpbs-boat'];
	}

	public function get_style_depends()
	{
		return ['wpbs-frontend'];
	}

	protected function register_controls()
	{
		// Content Section
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Price Card Settings', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'boat_id',
			[
				'label' => esc_html__('Boat ID', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__('Leave empty to use current boat', 'wp-boat-sync'),
			]
		);

		$this->add_control(
			'post_id',
			[
				'label' => esc_html__('Post ID', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::NUMBER,
			]
		);

		$this->end_controls_section();

		// Header Style
		$this->start_controls_section(
			'header_style',
			[
				'label' => esc_html__('Header', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'header_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__header' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'header_border',
				'selector' => '{{WRAPPER}} .wpbs-price-card__header',
			]
		);

		$this->add_responsive_control(
			'header_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Card Container Style
		$this->start_controls_section(
			'container_style',
			[
				'label' => esc_html__('Container', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'container_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .wpbs-price-card',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_shadow',
				'selector' => '{{WRAPPER}} .wpbs-price-card',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Title Style
		$this->start_controls_section(
			'title_style',
			[
				'label' => esc_html__('Title (Header)', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__header h1' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .wpbs-price-card__header h1',
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__header h1' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Year/Make/Model Style
		$this->start_controls_section(
			'year_make_model_style',
			[
				'label' => esc_html__('Year/Make/Model', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'year_make_model_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__header > div:first-of-type' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'year_make_model_typography',
				'selector' => '{{WRAPPER}} .wpbs-price-card__header > div:first-of-type',
			]
		);

		$this->end_controls_section();

		// Location Style (in header)
		$this->start_controls_section(
			'header_location_style',
			[
				'label' => esc_html__('Location (Header)', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'header_location_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__header > div:nth-of-type(2)' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'header_location_icon_color',
			[
				'label' => esc_html__('Icon Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__header svg' => 'fill: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'header_location_typography',
				'selector' => '{{WRAPPER}} .wpbs-price-card__header > div:nth-of-type(2)',
			]
		);

		$this->end_controls_section();

		// Brand Links Style
		$this->start_controls_section(
			'brand_style',
			[
				'label' => esc_html__('Brand Links', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('brand_tabs');

		$this->start_controls_tab('brand_normal', ['label' => esc_html__('Normal', 'wp-boat-sync')]);

		$this->add_control(
			'brand_color',
			[
				'label' => esc_html__('Link Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__header a' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab('brand_hover', ['label' => esc_html__('Hover', 'wp-boat-sync')]);

		$this->add_control(
			'brand_hover_color',
			[
				'label' => esc_html__('Link Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__header a:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'brand_typography',
				'selector' => '{{WRAPPER}} .wpbs-price-card__header a',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// Price Style
		$this->start_controls_section(
			'price_style',
			[
				'label' => esc_html__('Price (Body)', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__body .wpbs-price-card__price' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'selector' => '{{WRAPPER}} .wpbs-price-card__body .wpbs-price-card__price',
			]
		);

		$this->add_responsive_control(
			'price_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__body .wpbs-price-card__price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Label Style
		$this->start_controls_section(
			'label_style',
			[
				'label' => esc_html__('Label', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__label' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '{{WRAPPER}} .wpbs-price-card__label',
			]
		);

		$this->add_responsive_control(
			'label_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Status Badge Style
		$this->start_controls_section(
			'status_style',
			[
				'label' => esc_html__('Status Badge', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'status_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__status' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'status_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__status' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'status_typography',
				'selector' => '{{WRAPPER}} .wpbs-price-card__status',
			]
		);

		$this->add_responsive_control(
			'status_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__status' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'status_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__status' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Monthly Payment Style
		$this->start_controls_section(
			'monthly_style',
			[
				'label' => esc_html__('Monthly Payment', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'monthly_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__body .wpbs-price-card__monthly' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-price-card__body .wpbs-price-card__monthly strong' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-price-card__body .wpbs-price-card__monthly span' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'monthly_typography',
				'selector' => '{{WRAPPER}} .wpbs-price-card__body .wpbs-price-card__monthly',
			]
		);

		$this->add_responsive_control(
			'monthly_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__body .wpbs-price-card__monthly' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Body Style
		$this->start_controls_section(
			'body_style',
			[
				'label' => esc_html__('Body', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'body_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__body' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'body_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-price-card__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Sold Badge Style
		$this->start_controls_section(
			'sold_badge_style',
			[
				'label' => esc_html__('Sold Badge', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'sold_badge_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-badge--sold' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'sold_badge_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-badge--sold' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'sold_badge_typography',
				'selector' => '{{WRAPPER}} .wpbs-badge--sold',
			]
		);

		$this->add_responsive_control(
			'sold_badge_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-badge--sold' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'sold_badge_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-badge--sold' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'sold_badge_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-badge--sold' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Button Style
		$this->start_controls_section(
			'button_style',
			[
				'label' => esc_html__('Contact Button', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('button_tabs');

		$this->start_controls_tab('button_normal', ['label' => esc_html__('Normal', 'wp-boat-sync')]);

		$this->add_control(
			'button_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-btn' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-btn' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab('button_hover', ['label' => esc_html__('Hover', 'wp-boat-sync')]);

		$this->add_control(
			'button_hover_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-btn:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_hover_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-btn:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .wpbs-btn',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .wpbs-btn',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'button_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		
		// Get boat ID
		$post_id = null;
		if (!empty($settings['post_id'])) {
			$post_id = intval($settings['post_id']);
		} elseif (!empty($settings['boat_id'])) {
			global $wpdb;
			$table = $wpdb->prefix . 'wpbs_boats';
			$post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $table WHERE boat_id = %s", $settings['boat_id']));
		} else {
			$post_id = get_the_ID();
		}
		
		if (!$post_id || get_post_type($post_id) !== 'boats') {
			return;
		}
		
		// Get boat meta
		$price_raw = get_post_meta($post_id, 'wpbs_price', true);
		$status = get_post_meta($post_id, 'wpbs_status', true);
		$year = get_post_meta($post_id, 'wpbs_year', true);
		$make = get_post_meta($post_id, 'wpbs_make', true);
		$model = get_post_meta($post_id, 'wpbs_model', true);
		$location = get_post_meta($post_id, 'wpbs_location', true);
		$office_phone = get_post_meta($post_id, 'wpbs_office_phone', true);
		
		// Format price
		$price_num = floatval($price_raw);
		$price_display = '';
		if ($price_num > 0) {
			$price_display = '$' . number_format($price_num);
		}
		
		$is_sold = $status && strtolower($status) !== 'active';
		$title = get_the_title($post_id);
		
		// Start output
		echo '<div class="wpbs-price-card">';
		
		// Header with title, year/make/model, location, brand
		echo '<div class="wpbs-price-card__header">';
		echo '<h1 style="font-size:18px;font-weight:600;margin:0 0 8px;">' . esc_html($title) . '</h1>';
		
		$year_make_model = trim(($year ?: '') . ' ' . ($make ?: '') . ' ' . ($model ?: ''));
		if ($year_make_model) {
			echo '<div style="font-size:13px;color:#666;margin-bottom:8px;">' . esc_html($year_make_model) . '</div>';
		}
		
		if ($location) {
			echo '<div style="font-size:12px;color:#999;display:flex;align-items:center;gap:4px;">';
			echo '<svg width="12" height="12" viewBox="0 0 24 24" fill="#999"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>';
			echo esc_html($location);
			echo '</div>';
		}
		
		// Brand links
		$brand_terms = get_the_terms($post_id, 'brand');
		if ($brand_terms && !is_wp_error($brand_terms)) {
			$brand_links = array();
			foreach ($brand_terms as $bt) {
				$link = get_term_link($bt);
				if (!is_wp_error($link)) {
					$brand_links[] = '<a href="' . esc_url($link) . '" style="color:#0066cc;text-decoration:none;">' . esc_html($bt->name) . '</a>';
				}
			}
			if (!empty($brand_links)) {
				echo '<div style="font-size:12px;color:#999;margin-top:6px;">Brand: ' . wp_kses_post(implode(', ', $brand_links)) . '</div>';
			}
		}
		echo '</div>';
		
		// Body with price, monthly payment, buttons
		echo '<div class="wpbs-price-card__body">';
		
		if ($price_display) {
			echo '<div class="wpbs-price-card__price">' . esc_html($price_display) . '</div>';
			
			// Calculate monthly payment
			if ($price_num > 0) {
				$loan_settings = WPBS_Utils::get_settings();
				
				// Safely get loan settings with validation
				$down_payment = 20; // default
				if (isset($loan_settings['loan_down_payment']) && $loan_settings['loan_down_payment'] !== '') {
					$down_payment = floatval($loan_settings['loan_down_payment']);
				}
				
				$interest_rate = 7.5; // default
				if (isset($loan_settings['loan_interest_rate']) && $loan_settings['loan_interest_rate'] !== '') {
					$interest_rate = floatval($loan_settings['loan_interest_rate']);
				}
				
				$term_years = 1; // default
				if (isset($loan_settings['loan_term_years']) && $loan_settings['loan_term_years'] !== '') {
					$term_years = intval($loan_settings['loan_term_years']);
				}
				
				$down_payment_percent = $down_payment / 100;
				$annual_rate = $interest_rate / 100;
				$loan_term_years = max(1, $term_years);
				$loan_term_months = $loan_term_years * 12;
				$loan_amount = $price_num * (1 - $down_payment_percent);
				$monthly_rate = $annual_rate / 12;
				
				if ($monthly_rate > 0 && $loan_term_months > 0) {
					$monthly_payment = $loan_amount * ($monthly_rate * pow(1 + $monthly_rate, $loan_term_months)) / (pow(1 + $monthly_rate, $loan_term_months) - 1);
				} else {
					$monthly_payment = $loan_term_months > 0 ? $loan_amount / $loan_term_months : 0;
				}
				
				echo '<div class="wpbs-price-card__monthly" style="font-size:14px;color:#666;margin-top:4px;">';
				echo 'Est. <strong style="color:#333;">$' . number_format($monthly_payment) . '/mo</strong>';
				echo '<span style="font-size:11px;color:#999;display:block;margin-top:2px;">';
				echo intval($down_payment_percent * 100) . '% down, ' . number_format($annual_rate * 100, 2) . '% APR, ';
				echo $loan_term_years . ' yr' . ($loan_term_years > 1 ? 's' : '');
				echo '</span></div>';
			}
		} else {
			echo '<div class="wpbs-price-card__price">Contact for Price</div>';
		}
		
		// Sold badge
		if ($is_sold) {
			echo '<span class="wpbs-badge wpbs-badge--sold" style="margin-top:8px;display:inline-block;">Sold</span>';
		}
		
		// Buttons
		echo '<div style="margin-top:16px;display:flex;flex-direction:column;gap:10px;">';
		echo '<a href="' . esc_url(get_permalink($post_id)) . '#contact" class="wpbs-btn wpbs-btn--primary">';
		echo '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>';
		echo 'Contact Seller</a>';
		
		if ($office_phone) {
			echo '<a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $office_phone)) . '" class="wpbs-btn wpbs-btn--green">';
			echo '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>';
			echo esc_html($office_phone) . '</a>';
		} else {
			echo '<a href="tel:" class="wpbs-btn wpbs-btn--green">';
			echo '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>';
			echo 'Call Now</a>';
		}
		
		echo '</div>'; // buttons container
		echo '</div>'; // body
		echo '</div>'; // card
	}
}
