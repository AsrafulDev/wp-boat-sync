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

		// --- Boats Archive Link ---
		$this->add_control(
			'show_boats_archive',
			[
				'label' => esc_html__('Show "Boats for Sale" Link', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'boats_archive_text',
			[
				'label' => esc_html__('  Archive Link Text', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Boats for Sale',
				'condition' => ['show_boats_archive' => 'yes'],
			]
		);

		// --- Builder (Brand) ---
		$this->add_control(
			'show_builder',
			[
				'label' => esc_html__('Show Builder', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'builder_link',
			[
				'label' => esc_html__('  Link Builder', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => ['show_builder' => 'yes'],
			]
		);

		// --- Boat Class ---
		$this->add_control(
			'show_boat_class',
			[
				'label' => esc_html__('Show Boat Class', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'boat_class_link',
			[
				'label' => esc_html__('  Link Boat Class', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => ['show_boat_class' => 'yes'],
			]
		);

		// --- Boat Category ---
		$this->add_control(
			'show_boat_category',
			[
				'label' => esc_html__('Show Category', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'boat_category_link',
			[
				'label' => esc_html__('  Link Category', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => ['show_boat_category' => 'yes'],
			]
		);

		// --- Location ---
		$this->add_control(
			'show_location',
			[
				'label' => esc_html__('Show Location', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'location_link',
			[
				'label' => esc_html__('  Link Location', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => ['show_location' => 'yes'],
			]
		);

		// --- Model Year ---
		$this->add_control(
			'show_model_year',
			[
				'label' => esc_html__('Show Model Year', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'model_year_link',
			[
				'label' => esc_html__('  Link Model Year', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-boat-sync'),
				'label_off' => esc_html__('No', 'wp-boat-sync'),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => ['show_model_year' => 'yes'],
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
		$show_boats_archive = $settings['show_boats_archive'] === 'yes';
		$boats_archive_text = $settings['boats_archive_text'] ? esc_html($settings['boats_archive_text']) : 'Boats for Sale';

		$breadcrumb_items = [];

		// Add home
		if ($show_home) {
			$breadcrumb_items[] = '<a href="' . esc_url(home_url('/')) . '">' . $home_text . '</a>';
		}

		// Add boats archive
		if ($show_boats_archive && (is_singular('boats') || is_post_type_archive('boats') || is_tax('brand') || is_tax('boat_status') || is_tax('boat_class') || is_tax('boat_category') || is_tax('boat_location') || is_tax('model_year'))) {
			$breadcrumb_items[] = '<a href="' . esc_url(get_post_type_archive_link('boats')) . '">' . $boats_archive_text . '</a>';
		}

		// Add taxonomy term
		if (is_tax()) {
			$term = get_queried_object();
			if ($term) {
				$breadcrumb_items[] = '<span class="wpbs-breadcrumb__current">' . esc_html($term->name) . '</span>';
			}
		}

		// Add builder, boat class, category, location on single boat pages
		if (is_singular('boats')) {
			$post_id = get_the_ID();

			// Builder (brand taxonomy)
			if ($settings['show_builder'] === 'yes') {
				$brands = get_the_terms($post_id, 'brand');
				if ($brands && !is_wp_error($brands)) {
					$brand = $brands[0];
					if ($settings['builder_link'] === 'yes') {
						$breadcrumb_items[] = '<a href="' . esc_url(get_term_link($brand)) . '">' . esc_html($brand->name) . '</a>';
					} else {
						$breadcrumb_items[] = '<span>' . esc_html($brand->name) . '</span>';
					}
				}
			}

			// Boat Class (boat_class taxonomy)
			if ($settings['show_boat_class'] === 'yes') {
				$classes = get_the_terms($post_id, 'boat_class');
				if ($classes && !is_wp_error($classes)) {
					$class = $classes[0];
					if ($settings['boat_class_link'] === 'yes') {
						$breadcrumb_items[] = '<a href="' . esc_url(get_term_link($class)) . '">' . esc_html($class->name) . '</a>';
					} else {
						$breadcrumb_items[] = '<span>' . esc_html($class->name) . '</span>';
					}
				}
			}

			// Boat Category (boat_category taxonomy)
			if ($settings['show_boat_category'] === 'yes') {
				$cats = get_the_terms($post_id, 'boat_category');
				if ($cats && !is_wp_error($cats)) {
					$cat = $cats[0];
					if ($settings['boat_category_link'] === 'yes') {
						$breadcrumb_items[] = '<a href="' . esc_url(get_term_link($cat)) . '">' . esc_html($cat->name) . '</a>';
					} else {
						$breadcrumb_items[] = '<span>' . esc_html($cat->name) . '</span>';
					}
				}
			}

			// Location (boat_location taxonomy)
			if ($settings['show_location'] === 'yes') {
				$locs = get_the_terms($post_id, 'boat_location');
				if ($locs && !is_wp_error($locs)) {
					$loc = $locs[0];
					if ($settings['location_link'] === 'yes') {
						$breadcrumb_items[] = '<a href="' . esc_url(get_term_link($loc)) . '">' . esc_html($loc->name) . '</a>';
					} else {
						$breadcrumb_items[] = '<span>' . esc_html($loc->name) . '</span>';
					}
				}
			}

			// Model Year (model_year taxonomy)
			if ($settings['show_model_year'] === 'yes') {
				$years = get_the_terms($post_id, 'model_year');
				if ($years && !is_wp_error($years)) {
					$yr = $years[0];
					if ($settings['model_year_link'] === 'yes') {
						$breadcrumb_items[] = '<a href="' . esc_url(get_term_link($yr)) . '">' . esc_html($yr->name) . '</a>';
					} else {
						$breadcrumb_items[] = '<span>' . esc_html($yr->name) . '</span>';
					}
				}
			}

			$breadcrumb_items[] = '<span class="wpbs-breadcrumb__current">' . get_the_title() . '</span>';
		}

		if (!empty($breadcrumb_items)) {
			echo '<div class="wpbs-breadcrumb">';
			echo implode(' <span class="wpbs-breadcrumb__separator">' . $separator . '</span> ', $breadcrumb_items);
			echo '</div>';
		}
	}
}
