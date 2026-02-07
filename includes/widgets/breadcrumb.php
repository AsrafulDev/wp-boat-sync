<?php
/**
 * WPBS Elementor Widget - Breadcrumb
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Elementor_Breadcrumb_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'wpbs_breadcrumb';
	}

	public function get_title()
	{
		return esc_html__('Boat Breadcrumb', 'wp-boat-sync');
	}

	public function get_icon()
	{
		return 'eicon-navigation-horizontal';
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
				'label' => esc_html__('Breadcrumb Settings', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'separator',
			[
				'label' => esc_html__('Separator', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '›',
				'placeholder' => '›',
			]
		);

		$this->add_control(
			'show_home',
			[
				'label' => esc_html__('Show Home', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'home_text',
			[
				'label' => esc_html__('Home Text', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Home',
				'condition' => [
					'show_home' => 'yes',
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

		$this->add_control(
			'container_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-breadcrumb' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .wpbs-breadcrumb',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-breadcrumb' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
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
					'{{WRAPPER}} .wpbs-breadcrumb' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'container_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-breadcrumb' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'text_align',
			[
				'label' => esc_html__('Alignment', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__('Left', 'wp-boat-sync'),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__('Center', 'wp-boat-sync'),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__('Right', 'wp-boat-sync'),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .wpbs-breadcrumb' => 'text-align: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Link Style
		$this->start_controls_section(
			'link_style',
			[
				'label' => esc_html__('Links', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('link_tabs');

		$this->start_controls_tab('link_normal', ['label' => esc_html__('Normal', 'wp-boat-sync')]);

		$this->add_control(
			'link_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-breadcrumb a' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab('link_hover', ['label' => esc_html__('Hover', 'wp-boat-sync')]);

		$this->add_control(
			'link_hover_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-breadcrumb a:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'link_typography',
				'selector' => '{{WRAPPER}} .wpbs-breadcrumb a',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// Current Page Style
		$this->start_controls_section(
			'current_style',
			[
				'label' => esc_html__('Current Page', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'current_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-breadcrumb__current' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'current_typography',
				'selector' => '{{WRAPPER}} .wpbs-breadcrumb__current',
			]
		);

		$this->end_controls_section();

		// Separator Style
		$this->start_controls_section(
			'separator_style',
			[
				'label' => esc_html__('Separator', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'separator_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-breadcrumb__separator' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'separator_typography',
				'selector' => '{{WRAPPER}} .wpbs-breadcrumb__separator',
			]
		);

		$this->add_responsive_control(
			'separator_spacing',
			[
				'label' => esc_html__('Spacing', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => ['px' => ['min' => 0, 'max' => 30]],
				'selectors' => [
					'{{WRAPPER}} .wpbs-breadcrumb__separator' => 'margin: 0 {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$separator = $settings['separator'] ? esc_html($settings['separator']) : '›';
		$home_text = $settings['home_text'] ? esc_html($settings['home_text']) : 'Home';
		$show_home = $settings['show_home'] === 'yes';

		$breadcrumb_items = [];

		// Add home
		if ($show_home) {
			$breadcrumb_items[] = '<a href="' . esc_url(home_url('/')) . '">' . $home_text . '</a>';
		}

		// Add boats archive
		if (is_singular('boats') || is_post_type_archive('boats') || is_tax('brand') || is_tax('boat_status')) {
			$breadcrumb_items[] = '<a href="' . esc_url(get_post_type_archive_link('boats')) . '">Boats for Sale</a>';
		}

		// Add taxonomy term
		if (is_tax()) {
			$term = get_queried_object();
			if ($term) {
				$breadcrumb_items[] = '<span class="wpbs-breadcrumb__current">' . esc_html($term->name) . '</span>';
			}
		}

		// Add current boat
		if (is_singular('boats')) {
			$breadcrumb_items[] = '<span class="wpbs-breadcrumb__current">' . get_the_title() . '</span>';
		}

		if (!empty($breadcrumb_items)) {
			echo '<div class="wpbs-breadcrumb">';
			echo implode(' <span class="wpbs-breadcrumb__separator">' . $separator . '</span> ', $breadcrumb_items);
			echo '</div>';
		}
	}
}
