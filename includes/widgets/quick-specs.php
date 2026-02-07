<?php
/**
 * WPBS Elementor Widget - Quick Specs
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Elementor_Quick_Specs_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'wpbs_quick_specs';
	}

	public function get_title()
	{
		return esc_html__('Quick Specs', 'wp-boat-sync');
	}

	public function get_icon()
	{
		return 'eicon-info-circle';
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
				'label' => esc_html__('Quick Specs Settings', 'wp-boat-sync'),
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

		// Container Style
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
					'{{WRAPPER}} .wpbs-quick-specs' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .wpbs-quick-specs',
				'fields_options' => [
					'border' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs' => 'border-style: {{VALUE}} !important;']],
					'width' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']],
					'color' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs' => 'border-color: {{VALUE}} !important;']],
				],
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_shadow',
				'selector' => '{{WRAPPER}} .wpbs-quick-specs',
				'fields_options' => [
					'box_shadow' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}} !important;']],
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Heading Style
		$this->start_controls_section(
			'heading_style',
			[
				'label' => esc_html__('Heading', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs__title' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'heading_typography',
				'selector' => '{{WRAPPER}} .wpbs-quick-specs__title',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__title' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__title' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__title' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__title' => 'font-family: {{VALUE}} !important;']],
				],
			]
		);

		$this->add_responsive_control(
			'heading_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs__title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Items Style
		$this->start_controls_section(
			'items_style',
			[
				'label' => esc_html__('Spec Items', 'wp-boat-sync'),
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
					'{{WRAPPER}} .wpbs-quick-specs__list' => 'gap: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_control(
			'item_background',
			[
				'label' => esc_html__('Item Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs__item' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'item_border',
				'selector' => '{{WRAPPER}} .wpbs-quick-specs__item',
				'fields_options' => [
					'border' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__item' => 'border-style: {{VALUE}} !important;']],
					'width' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__item' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']],
					'color' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__item' => 'border-color: {{VALUE}} !important;']],
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
					'{{WRAPPER}} .wpbs-quick-specs__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label' => esc_html__('Item Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Label Style
		$this->start_controls_section(
			'label_style',
			[
				'label' => esc_html__('Labels', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs__label' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '{{WRAPPER}} .wpbs-quick-specs__label',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__label' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__label' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__label' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__label' => 'font-family: {{VALUE}} !important;']],
				],
			]
		);

		$this->end_controls_section();

		// Value Style
		$this->start_controls_section(
			'value_style',
			[
				'label' => esc_html__('Values', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'value_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs__value' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'value_typography',
				'selector' => '{{WRAPPER}} .wpbs-quick-specs__value',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__value' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__value' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__value' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-quick-specs__value' => 'font-family: {{VALUE}} !important;']],
				],
			]
		);

		$this->end_controls_section();

		// Icon Style
		$this->start_controls_section(
			'icon_style',
			[
				'label' => esc_html__('Icons', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs__icon' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__('Size', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => ['px' => ['min' => 10, 'max' => 50]],
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs__icon' => 'font-size: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'icon_spacing',
			[
				'label' => esc_html__('Spacing', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => ['px' => ['min' => 0, 'max' => 30]],
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs__icon' => 'margin-right: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$shortcodes = new WPBS_Shortcodes();
		echo $shortcodes->shortcode_quick_specs([
			'id' => $settings['boat_id'],
			'post_id' => $settings['post_id'],
		]);
	}
}
