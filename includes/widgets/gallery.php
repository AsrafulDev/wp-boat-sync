<?php
/**
 * WPBS Elementor Widget - Boat Gallery
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Elementor_Gallery_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'wpbs_gallery';
	}

	public function get_title()
	{
		return esc_html__('Boat Gallery', 'wp-boat-sync');
	}

	public function get_icon()
	{
		return 'eicon-gallery-grid';
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
				'label' => esc_html__('Gallery Settings', 'wp-boat-sync'),
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

		$this->add_control(
			'lightbox',
			[
				'label' => esc_html__('Enable Lightbox', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// Main Image Style
		$this->start_controls_section(
			'main_image_style',
			[
				'label' => esc_html__('Main Image', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'main_image_height',
			[
				'label' => esc_html__('Height', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', 'vh'],
				'range' => [
					'px' => ['min' => 200, 'max' => 800],
					'vh' => ['min' => 20, 'max' => 100],
				],
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__main' => 'height: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'main_image_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__main' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpbs-gallery__main-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'main_image_shadow',
				'selector' => '{{WRAPPER}} .wpbs-gallery__main',
			]
		);

		$this->end_controls_section();

		// Navigation Buttons
		$this->start_controls_section(
			'nav_buttons_style',
			[
				'label' => esc_html__('Navigation Buttons', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'nav_button_size',
			[
				'label' => esc_html__('Button Size', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => ['px' => ['min' => 30, 'max' => 100]],
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__nav' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->start_controls_tabs('nav_button_tabs');

		$this->start_controls_tab('nav_button_normal', ['label' => esc_html__('Normal', 'wp-boat-sync')]);

		$this->add_control(
			'nav_button_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__nav' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'nav_button_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__nav' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab('nav_button_hover', ['label' => esc_html__('Hover', 'wp-boat-sync')]);

		$this->add_control(
			'nav_button_hover_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__nav:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'nav_button_hover_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__nav:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'nav_button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__nav' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Thumbnails Style
		$this->start_controls_section(
			'thumbnails_style',
			[
				'label' => esc_html__('Thumbnails', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'thumbnail_size',
			[
				'label' => esc_html__('Thumbnail Size', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => ['px' => ['min' => 50, 'max' => 200]],
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__thumb' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnail_gap',
			[
				'label' => esc_html__('Gap', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => ['px' => ['min' => 0, 'max' => 50]],
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__thumbs' => 'gap: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnail_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__thumb' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpbs-gallery__thumb img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'thumbnail_border',
				'selector' => '{{WRAPPER}} .wpbs-gallery__thumb',
			]
		);

		$this->add_control(
			'thumbnail_active_border',
			[
				'label' => esc_html__('Active Border Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__thumb.is-active' => 'border-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// View Button Style
		$this->start_controls_section(
			'view_button_style',
			[
				'label' => esc_html__('View Button', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('view_button_tabs');

		$this->start_controls_tab('view_button_normal', ['label' => esc_html__('Normal', 'wp-boat-sync')]);

		$this->add_control(
			'view_button_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__view-btn' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'view_button_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__view-btn' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab('view_button_hover', ['label' => esc_html__('Hover', 'wp-boat-sync')]);

		$this->add_control(
			'view_button_hover_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__view-btn:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'view_button_hover_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__view-btn:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'view_button_typography',
				'selector' => '{{WRAPPER}} .wpbs-gallery__view-btn',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'view_button_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__view-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'view_button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-gallery__view-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$shortcodes = new WPBS_Shortcodes();
		echo $shortcodes->shortcode_gallery([
			'id' => $settings['boat_id'],
			'post_id' => $settings['post_id'],
			'lightbox' => $settings['lightbox'],
		]);
	}
}
