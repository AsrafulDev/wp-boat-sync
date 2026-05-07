<?php
/**
 * WPBS Elementor Widget - Boat Grid
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Elementor_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'wpbs_boat_grid';
	}

	public function get_title()
	{
		return esc_html__('Boat Grid', 'wp-boat-sync');
	}

	public function get_icon()
	{
		return 'eicon-posts-grid';
	}

	public function get_categories()
	{
		return ['wpbs-boat'];
	}

	public function get_keywords()
	{
		return ['boat', 'grid', 'listing', 'yacht'];
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
				'label' => esc_html__('Grid Settings', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__('Posts Per Page', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 12,
				'min' => 1,
				'max' => 100,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__('Columns', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'desktop_default' => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'min' => 1,
				'max' => 6,
				'selectors' => [
					'{{WRAPPER}} .wpbs-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr) !important;',
				],
			]
		);

		$this->add_control(
			'filter',
			[
				'label' => esc_html__('Show Filter', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'filter_position',
			[
				'label' => esc_html__('Filter Position', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'top',
				'options' => [
					'top' => esc_html__('Top', 'wp-boat-sync'),
					'left' => esc_html__('Left', 'wp-boat-sync'),
					'right' => esc_html__('Right', 'wp-boat-sync'),
				],
				'condition' => [
					'filter' => 'yes',
				],
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => esc_html__('Order By', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date' => esc_html__('Date', 'wp-boat-sync'),
					'price_low' => esc_html__('Price: Low to High', 'wp-boat-sync'),
					'price_high' => esc_html__('Price: High to Low', 'wp-boat-sync'),
					'year' => esc_html__('Year: Newest', 'wp-boat-sync'),
				],
			]
		);

		$this->add_control(
			'show_pagination',
			[
				'label' => esc_html__('Show Pagination', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_condition_badge',
			[
				'label' => esc_html__('Show Condition Badge', 'wp-boat-sync'),
				'description' => esc_html__('Display a New/Used/Sold label on the boat image.', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// Pre-Filter Section
		$this->start_controls_section(
			'prefilter_section',
			[
				'label' => esc_html__('Pre-Filter', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// Get fresh filter options (bypass cache for admin)
		global $wpdb;
		
		// Get all categories (no limit)
		$categories = $wpdb->get_col(
			"SELECT DISTINCT pm.meta_value 
			 FROM {$wpdb->postmeta} pm 
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
			 WHERE p.post_type = 'boats' 
			 AND p.post_status = 'publish' 
			 AND pm.meta_key = 'wpbs_boat_category' 
			 AND pm.meta_value != '' 
			 ORDER BY pm.meta_value ASC"
		);
		
		// Build category options
		$category_options = ['' => esc_html__('All Categories', 'wp-boat-sync')];
		foreach (array_filter($categories) as $cat) {
			$category_options[$cat] = $cat;
		}

		$this->add_control(
			'prefilter_category',
			[
				'label' => esc_html__('Category', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => $category_options,
				'label_block' => true,
			]
		);

		// Get all builders (no limit)
		$builders = $wpdb->get_col(
			"SELECT DISTINCT pm.meta_value 
			 FROM {$wpdb->postmeta} pm 
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
			 WHERE p.post_type = 'boats' 
			 AND p.post_status = 'publish' 
			 AND pm.meta_key = 'wpbs_make' 
			 AND pm.meta_value != '' 
			 ORDER BY pm.meta_value ASC"
		);
		
		// Build builder options
		$builder_options = ['' => esc_html__('All Builders', 'wp-boat-sync')];
		foreach (array_filter($builders) as $builder) {
			$builder_options[$builder] = $builder;
		}

		$this->add_control(
			'prefilter_builder',
			[
				'label' => esc_html__('Builder/Make', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => $builder_options,
				'label_block' => true,
			]
		);

		// Get all locations (no limit)
		$locations = $wpdb->get_col(
			"SELECT DISTINCT pm.meta_value 
			 FROM {$wpdb->postmeta} pm 
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
			 WHERE p.post_type = 'boats' 
			 AND p.post_status = 'publish' 
			 AND pm.meta_key = 'wpbs_location' 
			 AND pm.meta_value != '' 
			 ORDER BY pm.meta_value ASC"
		);
		
		// Build location options
		$location_options = ['' => esc_html__('All Locations', 'wp-boat-sync')];
		foreach (array_filter($locations) as $loc) {
			$location_options[$loc] = $loc;
		}

		$this->add_control(
			'prefilter_location',
			[
				'label' => esc_html__('Location', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => $location_options,
				'label_block' => true,
			]
		);

		$this->add_control(
			'prefilter_condition',
			[
				'label' => esc_html__('Condition', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__('All', 'wp-boat-sync'),
					'new' => esc_html__('New', 'wp-boat-sync'),
					'used' => esc_html__('Used', 'wp-boat-sync'),
				],
			]
		);

		$this->add_control(
			'prefilter_featured',
			[
				'label' => esc_html__('Featured Only', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->end_controls_section();

		// Grid Style Section
		$this->start_controls_section(
			'grid_style_section',
			[
				'label' => esc_html__('Grid', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'grid_gap',
			[
				'label' => esc_html__('Gap', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 24,
				],
				'selectors' => [
					'{{WRAPPER}} .wpbs-grid' => 'gap: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Card Style Section
		$this->start_controls_section(
			'card_style_section',
			[
				'label' => esc_html__('Card', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_background',
			[
				'label' => esc_html__('Background Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-card' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'selector' => '{{WRAPPER}} .wpbs-card',
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .wpbs-card',
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Image Style Section
		$this->start_controls_section(
			'image_style_section',
			[
				'label' => esc_html__('Image', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label' => esc_html__('Height', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 600,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 280,
				],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__slider' => 'height: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__slider' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpbs-card-slider__item img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Condition Badge Style Section
		$this->start_controls_section(
			'condition_badge_style_section',
			[
				'label' => esc_html__('Condition Badge', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_condition_badge' => 'yes',
				],
			]
		);

		$this->add_control(
			'badge_new_bg',
			[
				'label' => esc_html__('New Badge Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#0b5fff',
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__condition-badge--new' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'badge_used_bg',
			[
				'label' => esc_html__('Used Badge Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#6b7280',
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__condition-badge--used' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'badge_sold_bg',
			[
				'label' => esc_html__('Sold Badge Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#dc3545',
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__condition-badge--sold' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'badge_text_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__condition-badge' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'badge_typography',
				'label' => esc_html__('Typography', 'wp-boat-sync'),
				'selector' => '{{WRAPPER}} .wpbs-card__condition-badge',
			]
		);

		$this->add_control(
			'badge_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'default' => [
					'top' => 3,
					'right' => 3,
					'bottom' => 3,
					'left' => 3,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__condition-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'badge_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'default' => [
					'top' => 4,
					'right' => 10,
					'bottom' => 4,
					'left' => 10,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__condition-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'badge_horizontal_position',
			[
				'label' => esc_html__('Horizontal Position', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__('Left', 'wp-boat-sync'),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__('Right', 'wp-boat-sync'),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'left',
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__condition-badge' => 'left: auto; right: auto; {{VALUE}}: 8px !important;',
				],
			]
		);

		$this->add_responsive_control(
			'badge_top_offset',
			[
				'label' => esc_html__('Top Offset', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => ['min' => 0, 'max' => 50],
				],
				'default' => ['unit' => 'px', 'size' => 8],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__condition-badge' => 'top: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Title Style Section
		$this->start_controls_section(
			'title_style_section',
			[
				'label' => esc_html__('Title', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__title a' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-card__title' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label' => esc_html__('Hover Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__title a:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .wpbs-card__title, {{WRAPPER}} .wpbs-card__title a',
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => esc_html__('Spacing', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__title' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Price Style Section
		$this->start_controls_section(
			'price_style_section',
			[
				'label' => esc_html__('Price', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__price' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'selector' => '{{WRAPPER}} .wpbs-card__price',
			]
		);

		$this->add_responsive_control(
			'price_spacing',
			[
				'label' => esc_html__('Spacing', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__price' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Meta Style Section
		$this->start_controls_section(
			'meta_style_section',
			[
				'label' => esc_html__('Meta', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__meta' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'meta_typography',
				'selector' => '{{WRAPPER}} .wpbs-card__meta',
			]
		);

		$this->add_responsive_control(
			'meta_spacing',
			[
				'label' => esc_html__('Spacing', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__meta' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Button Style Section
		$this->start_controls_section(
			'button_style_section',
			[
				'label' => esc_html__('Button', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('button_style_tabs');

		$this->start_controls_tab(
			'button_normal_tab',
			[
				'label' => esc_html__('Normal', 'wp-boat-sync'),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__button' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => esc_html__('Background Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__button' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'button_hover_tab',
			[
				'label' => esc_html__('Hover', 'wp-boat-sync'),
			]
		);

		$this->add_control(
			'button_hover_text_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__button:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_hover_background_color',
			[
				'label' => esc_html__('Background Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__button:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .wpbs-card__button',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .wpbs-card__button',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-card__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Filter Style Section
		$this->start_controls_section(
			'filter_style_section',
			[
				'label' => esc_html__('Filter', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'filter' => 'yes',
				],
			]
		);

		$this->add_control(
			'filter_background',
			[
				'label' => esc_html__('Background Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'filter_border',
				'selector' => '{{WRAPPER}} .wpbs-filter-bar',
			]
		);

		$this->add_responsive_control(
			'filter_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'filter_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'filter_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		// Filter Label
		$this->add_control(
			'filter_label_heading',
			[
				'label' => esc_html__('Labels', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'filter_label_color',
			[
				'label' => esc_html__('Label Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar label' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-filter-bar .wpbs-filter-label' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'filter_label_typography',
				'selector' => '{{WRAPPER}} .wpbs-filter-bar label, {{WRAPPER}} .wpbs-filter-bar .wpbs-filter-label',
			]
		);

		// Filter Inputs
		$this->add_control(
			'filter_input_heading',
			[
				'label' => esc_html__('Input Fields', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'filter_input_text_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar input' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-filter-bar select' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'filter_input_background',
			[
				'label' => esc_html__('Background Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar input' => 'background-color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-filter-bar select' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'filter_input_border',
				'selector' => '{{WRAPPER}} .wpbs-filter-bar input, {{WRAPPER}} .wpbs-filter-bar select',
			]
		);

		$this->add_responsive_control(
			'filter_input_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpbs-filter-bar select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'filter_input_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpbs-filter-bar select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'filter_input_typography',
				'selector' => '{{WRAPPER}} .wpbs-filter-bar input, {{WRAPPER}} .wpbs-filter-bar select',
			]
		);

		// Filter Button
		$this->add_control(
			'filter_button_heading',
			[
				'label' => esc_html__('Search Button', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs('filter_button_tabs');

		$this->start_controls_tab(
			'filter_button_normal',
			[
				'label' => esc_html__('Normal', 'wp-boat-sync'),
			]
		);

		$this->add_control(
			'filter_button_text_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar button[type="submit"]' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'filter_button_background',
			[
				'label' => esc_html__('Background Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar button[type="submit"]' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'filter_button_hover',
			[
				'label' => esc_html__('Hover', 'wp-boat-sync'),
			]
		);

		$this->add_control(
			'filter_button_hover_text_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar button[type="submit"]:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'filter_button_hover_background',
			[
				'label' => esc_html__('Background Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar button[type="submit"]:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'filter_button_border',
				'selector' => '{{WRAPPER}} .wpbs-filter-bar button[type="submit"]',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'filter_button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar button[type="submit"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'filter_button_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-filter-bar button[type="submit"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'filter_button_typography',
				'selector' => '{{WRAPPER}} .wpbs-filter-bar button[type="submit"]',
			]
		);

		$this->end_controls_section();

		// Sort Bar Style Section
		$this->start_controls_section(
			'sort_style_section',
			[
				'label' => esc_html__('Sort Bar', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'sort_bar_background',
			[
				'label' => esc_html__('Background Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-archive-header' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'sort_bar_border',
				'selector' => '{{WRAPPER}} .wpbs-archive-header',
			]
		);

		$this->add_responsive_control(
			'sort_bar_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-archive-header' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'sort_bar_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-archive-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'sort_bar_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-archive-header' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		// Count Text
		$this->add_control(
			'sort_count_heading',
			[
				'label' => esc_html__('Count Text', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sort_count_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					// '{{WRAPPER}} .wpbs-archive-count' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'sort_count_typography',
				// 'selector' => '{{WRAPPER}} .wpbs-archive-count',
			]
		);

		// Sort Label
		$this->add_control(
			'sort_label_heading',
			[
				'label' => esc_html__('Sort Label', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sort_label_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-archive-sort > span' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'sort_label_typography',
				'selector' => '{{WRAPPER}} .wpbs-archive-sort > span',
			]
		);

		// Sort Dropdown
		$this->add_control(
			'sort_dropdown_heading',
			[
				'label' => esc_html__('Sort Dropdown', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sort_dropdown_text_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-archive-sort select' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'sort_dropdown_background',
			[
				'label' => esc_html__('Background Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-archive-sort select' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'sort_dropdown_border',
				'selector' => '{{WRAPPER}} .wpbs-archive-sort select',
			]
		);

		$this->add_responsive_control(
			'sort_dropdown_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-archive-sort select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'sort_dropdown_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-archive-sort select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'sort_dropdown_typography',
				'selector' => '{{WRAPPER}} .wpbs-archive-sort select',
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		// Hide condition badge when switch is off
		if (empty($settings['show_condition_badge']) || $settings['show_condition_badge'] !== 'yes') {
			echo '<style>.elementor-element-' . esc_attr($this->get_id()) . ' .wpbs-card__condition-badge { display: none !important; }</style>';
		}

		// Get responsive columns
		$desktop_cols = isset($settings['columns']) ? max(1, min(6, (int)$settings['columns'])) : 3;
		$tablet_cols = isset($settings['columns_tablet']) ? max(1, min(6, (int)$settings['columns_tablet'])) : 2;
		$mobile_cols = isset($settings['columns_mobile']) ? max(1, min(6, (int)$settings['columns_mobile'])) : 1;

		// Build shortcode attributes
		$atts = [
			'posts_per_page' => $settings['posts_per_page'],
			'columns' => $desktop_cols . ',' . $tablet_cols . ',' . $mobile_cols,
			'filter' => $settings['filter'] === 'yes' ? 'true' : 'false',
			'filter_position' => $settings['filter_position'],
			'orderby' => $settings['orderby'],
		];

		// Apply pre-filters
		if (!empty($settings['prefilter_category'])) {
			$atts['category'] = $settings['prefilter_category'];
		}
		
		if (!empty($settings['prefilter_builder'])) {
			$atts['builder'] = $settings['prefilter_builder'];
		}
		
		if (!empty($settings['prefilter_location'])) {
			$atts['location'] = $settings['prefilter_location'];
		}
		
		if (!empty($settings['prefilter_condition'])) {
			$atts['condition'] = $settings['prefilter_condition'];
		}
		
		if ($settings['prefilter_featured'] === 'yes') {
			$atts['featured'] = 'true';
		}
		
		// Pass pagination setting
		if (isset($settings['show_pagination'])) {
			$atts['pagination'] = $settings['show_pagination'];
		}

		// If on a taxonomy page, auto-filter by that taxonomy
		if (is_tax('brand')) {
			$term = get_queried_object();
			if ($term && isset($term->name)) {
				// Pre-filter by brand - override if not already set
				if (empty($atts['builder'])) {
					$atts['builder'] = $term->name;
				}
			}
		} elseif (is_tax('boat_status')) {
			// Handle boat_status taxonomy if needed
			$term = get_queried_object();
			if ($term) {
				// You can add status filtering here if your shortcode supports it
			}
		}

		// Detect current page (Elementor-safe)
$paged = 1;

if (isset($_GET['paged'])) {
    $paged = max(1, (int) $_GET['paged']);
} elseif (isset($_POST['paged'])) {
    $paged = max(1, (int) $_POST['paged']);
}

// Pass paged to shortcode
$atts['paged'] = $paged;

// Call the shortcode
$shortcodes = new WPBS_Shortcodes();
echo $shortcodes->shortcode_grid($atts);
	}

}
