<?php
/**
 * WPBS Elementor Widget - Boat Accordion
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Elementor_Accordion_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'wpbs_accordion';
	}

	public function get_title()
	{
		return esc_html__('Boat Accordion', 'wp-boat-sync');
	}

	public function get_icon()
	{
		return 'eicon-accordion';
	}

	public function get_categories()
	{
		return ['wpbs-boat'];
	}

	public function get_style_depends()
	{
		return ['wpbs-frontend'];
	}

	public function get_script_depends()
	{
		return ['wpbs-gallery'];
	}

	protected function register_controls()
	{
		// Content Section
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Accordion Settings', 'wp-boat-sync'),
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

		// Sections Visibility
		$this->start_controls_section(
			'sections_visibility',
			[
				'label' => esc_html__('Show/Hide Sections', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_description',
			[
				'label' => esc_html__('Show Description', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_measurements',
			[
				'label' => esc_html__('Show Measurements', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_propulsion',
			[
				'label' => esc_html__('Show Propulsion', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_features',
			[
				'label' => esc_html__('Show Features', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_additional',
			[
				'label' => esc_html__('Show More Details', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_location',
			[
				'label' => esc_html__('Show Location', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'default_open',
			[
				'label' => esc_html__('Default Open Section', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'description',
				'options' => [
					'none' => esc_html__('None', 'wp-boat-sync'),
					'description' => esc_html__('Description', 'wp-boat-sync'),
					'measurements' => esc_html__('Measurements', 'wp-boat-sync'),
					'propulsion' => esc_html__('Propulsion', 'wp-boat-sync'),
					'features' => esc_html__('Features', 'wp-boat-sync'),
				],
			]
		);

		$this->end_controls_section();

		// Container Style
		$this->start_controls_section(
			'container_style',
			[
				'label' => esc_html__('Container', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'items_gap',
			[
				'label' => esc_html__('Gap Between Items', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => ['px' => ['min' => 0, 'max' => 50]],
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion' => 'gap: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Accordion Item Style
		$this->start_controls_section(
			'item_style',
			[
				'label' => esc_html__('Item', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'item_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__item' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'item_border',
				'selector' => '{{WRAPPER}} .wpbs-accordion__item',
				'fields_options' => [
					'border' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__item' => 'border-style: {{VALUE}} !important;']],
					'width' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__item' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']],
					'color' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__item' => 'border-color: {{VALUE}} !important;']],
				],
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'item_shadow',
				'selector' => '{{WRAPPER}} .wpbs-accordion__item',
				'fields_options' => [
					'box_shadow' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__item' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}} !important;']],
				],
			]
		);

		$this->end_controls_section();

		// Title/Header Style
		$this->start_controls_section(
			'header_style',
			[
				'label' => esc_html__('Header', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('header_tabs');

		$this->start_controls_tab('header_normal', ['label' => esc_html__('Normal', 'wp-boat-sync')]);

		$this->add_control(
			'header_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__header' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'header_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__header' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab('header_hover', ['label' => esc_html__('Hover', 'wp-boat-sync')]);

		$this->add_control(
			'header_hover_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__header:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'header_hover_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__header:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab('header_active', ['label' => esc_html__('Active', 'wp-boat-sync')]);

		$this->add_control(
			'header_active_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__item.is-active .wpbs-accordion__header' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'header_active_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__item.is-active .wpbs-accordion__header' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'header_typography',
				'selector' => '{{WRAPPER}} .wpbs-accordion__header',
				'separator' => 'before',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__header' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__header' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__header' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__header' => 'font-family: {{VALUE}} !important;']],
				],
			]
		);

		$this->add_responsive_control(
			'header_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Icon Style
		$this->start_controls_section(
			'icon_style',
			[
				'label' => esc_html__('Icon', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__icon' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__('Size', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => ['px' => ['min' => 10, 'max' => 40]],
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__icon' => 'font-size: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Content Style
		$this->start_controls_section(
			'content_style',
			[
				'label' => esc_html__('Content', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'content_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__content' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'content_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__content' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'selector' => '{{WRAPPER}} .wpbs-accordion__content',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__content' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__content' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__content' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion__content' => 'font-family: {{VALUE}} !important;']],
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$shortcodes = new WPBS_Shortcodes();
		echo $shortcodes->shortcode_accordion([
			'id' => $settings['boat_id'],
			'post_id' => $settings['post_id'],
			'show_description' => $settings['show_description'],
			'show_measurements' => $settings['show_measurements'],
			'show_propulsion' => $settings['show_propulsion'],
			'show_features' => $settings['show_features'],
			'show_additional' => $settings['show_additional'],
			'show_location' => $settings['show_location'],
			'default_open' => $settings['default_open'],
		]);
	}
}
