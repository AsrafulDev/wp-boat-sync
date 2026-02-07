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

        //card Title Style
        $this->start_controls_section(
            'card_title_style',
            [
                'label' => esc_html__('Card Title', 'wp-boat-sync'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'card_title_color',
            [
                'label' => esc_html__('Color', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpbs-accordion-details__title' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'card_title_typography',
                'selector' => '{{WRAPPER}} .wpbs-accordion-details__title',
                'fields_options' => [
                    'typography' => ['default' => 'custom'],
                    'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-details__title' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
                    'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-details__title' => 'font-weight: {{VALUE}} !important;']],
                    'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-details__title' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
                    'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-details__title' => 'font-family: {{VALUE}} !important;']],
                ],
            ]
        );
        $this->add_responsive_control(
            'card_title_padding',
            [
                'label' => esc_html__('Padding', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpbs-accordion-details__title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
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
					'{{WRAPPER}} .wpbs-accordion-item__header h3' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'header_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion-item__header h3' => 'background-color: {{VALUE}} !important;',
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
					'{{WRAPPER}} .wpbs-accordion-item__header:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'header_hover_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion-item__header:hover' => 'background-color: {{VALUE}} !important;',
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
					'{{WRAPPER}} .wpbs-accordion__item.is-active .wpbs-accordion-item__header h3' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'header_active_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion__item.is-active .wpbs-accordion-item__header h3' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'header_typography',
				'selector' => '{{WRAPPER}} .wpbs-accordion-item__header h3',
				'separator' => 'before',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-item__header h3' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-item__header h3' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-item__header h3' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-item__header h3' => 'font-family: {{VALUE}} !important;']],
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
					'{{WRAPPER}} .wpbs-accordion-item__header h3' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
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
					'{{WRAPPER}} .wpbs-accordion-item__icon' => 'color: {{VALUE}} !important;',
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
					'{{WRAPPER}} .wpbs-accordion-item__content' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'content_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-accordion-item__content' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'selector' => '{{WRAPPER}} .wpbs-accordion-item__content',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-item__content' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-item__content' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-item__content' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-accordion-item__content' => 'font-family: {{VALUE}} !important;']],
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
					'{{WRAPPER}} .wpbs-accordion-item__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Helper to get boat meta fields - OPTIMIZED to use single bulk fetch
	 */
	private function get_boat_meta($post_id)
	{
		// Fetch ALL meta at once to avoid 40+ individual queries
		$all_meta = get_post_meta($post_id);
		
		$fields = [
			'length', 'beam', 'draft', 'displacement', 'dry_weight', 'bridge_clearance',
			'deadrise', 'cabins', 'heads', 'num_engines', 'engine_make', 'engine_model',
			'engine_type', 'total_power', 'engine_hours', 'fuel_type', 'drive_type',
			'propeller', 'cruising_speed', 'max_speed', 'fuel_capacity', 'range',
			'water_capacity', 'condition', 'boat_category', 'boat_class', 'hull_material',
			'location', 'engine', 'nominal_length', 'min_draft', 'cabin_headroom',
			'engines_json', 'keel_type', 'trim_tabs', 'windlass_type', 'electrical_circuit',
			'builder_name', 'designer_name', 'additional_detail_html', 'boat_city',
			'state', 'boat_country', 'wpbs_hull_id'
		];

		$meta = [];
		foreach ($fields as $field) {
			$key = 'wpbs_' . $field;
			$meta[$field] = isset($all_meta[$key]) ? $all_meta[$key][0] : '';
		}
		return $meta;
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		// Get boat ID using the same pattern as other widgets
		$final_id = null;

		if (!empty($settings['post_id'])) {
			$final_id = intval($settings['post_id']);
		} elseif (!empty($settings['boat_id'])) {
			$boat_id_setting = $settings['boat_id'];
			if (is_numeric($boat_id_setting)) {
				$final_id = intval($boat_id_setting);
			} else {
				$args = [
					'post_type' => 'boats',
					'meta_key' => 'wpbs_boat_id',
					'meta_value' => $boat_id_setting,
					'posts_per_page' => 1,
					'fields' => 'ids'
				];
				$query = new \WP_Query($args);
				if ($query->have_posts()) {
					$final_id = $query->posts[0];
				}
				wp_reset_postdata();
			}
		} else {
			$final_id = get_the_ID();
		}

		if (!$final_id) {
			return;
		}

		$post = get_post($final_id);
		if (!$post) {
			return;
		}

		$meta = $this->get_boat_meta($final_id);

		// All meta now fetched in bulk - just reference from $meta array
		$nominal_length = $meta['nominal_length'];
		$min_draft = $meta['min_draft'];
		$cabin_headroom = $meta['cabin_headroom'];
		$engines_json = $meta['engines_json'];
		$keel_type = $meta['keel_type'];
		$trim_tabs = $meta['trim_tabs'];
		$windlass = $meta['windlass_type'];
		$electrical = $meta['electrical_circuit'];
		$builder = $meta['builder_name'];
		$designer = $meta['designer_name'];
		$additional_detail = $meta['additional_detail_html'];
		$boat_city = $meta['boat_city'];
		$state = $meta['state'];
		$country = $meta['boat_country'];

		echo '<div class="wpbs-accordion-details">';
		echo '<h2 class="wpbs-accordion-details__title">Boat Details</h2>';

		// Description
		if ($settings['show_description'] === 'yes') {
			$is_open = $settings['default_open'] === 'description' ? ' open' : '';
			echo '<details class="wpbs-accordion-item"' . $is_open . '>';
			echo '<summary class="wpbs-accordion-item__header"><h3>Description</h3></summary>';
			echo '<div class="wpbs-accordion-item__content">';
			if ($post->post_content) {
				echo '<div class="wpbs-description-text" data-wpbs-expandable>';
				echo apply_filters('the_content', $post->post_content);
				echo '</div>';
				echo '<button type="button" class="wpbs-show-more-btn" data-wpbs-toggle-expand>Show More</button>';
			} else {
				echo '<p class="wpbs-description-empty">No description available.</p>';
			}
			echo '</div></details>';
		}

		// Measurements
		if ($settings['show_measurements'] === 'yes') {
			$is_open = $settings['default_open'] === 'measurements' ? ' open' : '';
			echo '<details class="wpbs-accordion-item"' . $is_open . '>';
			echo '<summary class="wpbs-accordion-item__header"><h3>Measurements</h3></summary>';
			echo '<div class="wpbs-accordion-item__content"><div class="wpbs-details-grid">';

			// Dimensions Cell
			$dimensions = [];
			if ($meta['length']) $dimensions['Length Overall'] = $meta['length'];
			if ($nominal_length) $dimensions['Nominal Length'] = $nominal_length;
			if ($min_draft) $dimensions['Min Draft'] = $min_draft;
			if ($meta['beam']) $dimensions['Beam'] = $meta['beam'];
			if ($meta['bridge_clearance']) $dimensions['Bridge Clearance'] = $meta['bridge_clearance'];
			if ($cabin_headroom) $dimensions['Cabin Headroom'] = $cabin_headroom;
			if ($meta['dry_weight']) $dimensions['Dry Weight'] = $meta['dry_weight'];
			if ($meta['displacement']) $dimensions['Displacement'] = $meta['displacement'];

			if (!empty($dimensions)) {
				echo '<div class="wpbs-details-cell"><h3>Dimensions</h3><div class="wpbs-details-cell__content">';
				foreach ($dimensions as $label => $value) {
					echo '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
				}
				echo '</div></div>';
			}

			// Tanks Cell
			$tanks = [];
			if ($meta['water_capacity']) $tanks['Fresh Water Tanks'] = $meta['water_capacity'];
			if ($meta['fuel_capacity']) $tanks['Fuel Tanks'] = $meta['fuel_capacity'];

			if (!empty($tanks)) {
				echo '<div class="wpbs-details-cell"><h3>Tanks</h3><div class="wpbs-details-cell__content">';
				foreach ($tanks as $label => $value) {
					echo '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
				}
				echo '</div></div>';
			}

			// Miscellaneous Cell
			$misc = [];
			if ($meta['cabins']) $misc['Cabins'] = $meta['cabins'];
			if ($meta['deadrise']) $misc['Deadrise At Transom'] = $meta['deadrise'];
			if ($meta['heads']) $misc['Heads'] = $meta['heads'];
			if ($meta['hull_id']) $misc['Hull ID'] = $meta['hull_id'];

			if (!empty($misc)) {
				echo '<div class="wpbs-details-cell"><h3>Miscellaneous</h3><div class="wpbs-details-cell__content">';
				foreach ($misc as $label => $value) {
					echo '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
				}
				echo '</div></div>';
			}

			echo '</div></div></details>';
		}

		// Propulsion
		if ($settings['show_propulsion'] === 'yes') {
			$is_open = $settings['default_open'] === 'propulsion' ? ' open' : '';
			echo '<details class="wpbs-accordion-item"' . $is_open . '>';
			echo '<summary class="wpbs-accordion-item__header"><h3>Propulsion</h3></summary>';
			echo '<div class="wpbs-accordion-item__content"><div class="wpbs-details-grid">';

			// Parse engines JSON
			$engines = [];
			if ($engines_json) {
				$engines = json_decode($engines_json, true);
			}

			if (!empty($engines) && is_array($engines)) {
				$engine_num = 1;
				foreach ($engines as $eng) {
					echo '<div class="wpbs-details-cell"><h3>Engine ' . $engine_num . '</h3><div class="wpbs-details-cell__content">';
					if (!empty($eng['Make'])) echo '<p><span class="wpbs-details-label">Engine Make:</span><span class="wpbs-details-value">' . esc_html($eng['Make']) . '</span></p>';
					if (!empty($eng['Model'])) echo '<p><span class="wpbs-details-label">Engine Model:</span><span class="wpbs-details-value">' . esc_html($eng['Model']) . '</span></p>';
					if (!empty($eng['Year'])) echo '<p><span class="wpbs-details-label">Engine Year:</span><span class="wpbs-details-value">' . esc_html($eng['Year']) . '</span></p>';
					if (!empty($eng['EnginePower'])) echo '<p><span class="wpbs-details-label">Total Power:</span><span class="wpbs-details-value">' . esc_html($eng['EnginePower']) . '</span></p>';
					if (!empty($eng['Type'])) echo '<p><span class="wpbs-details-label">Engine Type:</span><span class="wpbs-details-value">' . esc_html($eng['Type']) . '</span></p>';
					if (!empty($eng['DriveType'])) echo '<p><span class="wpbs-details-label">Drive Type:</span><span class="wpbs-details-value">' . esc_html($eng['DriveType']) . '</span></p>';
					if (!empty($eng['Fuel'])) echo '<p><span class="wpbs-details-label">Fuel Type:</span><span class="wpbs-details-value">' . esc_html($eng['Fuel']) . '</span></p>';
					if (!empty($eng['PropellerType'])) echo '<p><span class="wpbs-details-label">Propeller Type:</span><span class="wpbs-details-value">' . esc_html($eng['PropellerType']) . '</span></p>';
					if (!empty($eng['PropellerMaterial'])) echo '<p><span class="wpbs-details-label">Propeller Material:</span><span class="wpbs-details-value">' . esc_html($eng['PropellerMaterial']) . '</span></p>';
					echo '</div></div>';
					$engine_num++;
				}
			} else {
				// Fallback to single engine meta
				echo '<div class="wpbs-details-cell"><h3>Engine</h3><div class="wpbs-details-cell__content">';
				if ($meta['num_engines']) echo '<p><span class="wpbs-details-label">Number of Engines:</span><span class="wpbs-details-value">' . esc_html($meta['num_engines']) . '</span></p>';
				if ($meta['engine_make']) echo '<p><span class="wpbs-details-label">Engine Make:</span><span class="wpbs-details-value">' . esc_html($meta['engine_make']) . '</span></p>';
				if ($meta['engine_model']) echo '<p><span class="wpbs-details-label">Engine Model:</span><span class="wpbs-details-value">' . esc_html($meta['engine_model']) . '</span></p>';
				if ($meta['engine_type']) echo '<p><span class="wpbs-details-label">Engine Type:</span><span class="wpbs-details-value">' . esc_html($meta['engine_type']) . '</span></p>';
				if ($meta['total_power']) echo '<p><span class="wpbs-details-label">Total Power:</span><span class="wpbs-details-value">' . esc_html($meta['total_power']) . '</span></p>';
				if ($meta['engine_hours']) echo '<p><span class="wpbs-details-label">Engine Hours:</span><span class="wpbs-details-value">' . esc_html($meta['engine_hours']) . '</span></p>';
				if ($meta['fuel_type']) echo '<p><span class="wpbs-details-label">Fuel Type:</span><span class="wpbs-details-value">' . esc_html(ucfirst($meta['fuel_type'])) . '</span></p>';
				if ($meta['drive_type']) echo '<p><span class="wpbs-details-label">Drive Type:</span><span class="wpbs-details-value">' . esc_html($meta['drive_type']) . '</span></p>';
				if ($meta['propeller']) echo '<p><span class="wpbs-details-label">Propeller:</span><span class="wpbs-details-value">' . esc_html($meta['propeller']) . '</span></p>';
				echo '</div></div>';
			}

			// Performance Cell
			$performance = [];
			if ($meta['cruising_speed']) $performance['Cruising Speed'] = $meta['cruising_speed'];
			if ($meta['max_speed']) $performance['Max Speed'] = $meta['max_speed'];
			if ($meta['range']) $performance['Range'] = $meta['range'];

			if (!empty($performance)) {
				echo '<div class="wpbs-details-cell"><h3>Performance</h3><div class="wpbs-details-cell__content">';
				foreach ($performance as $label => $value) {
					echo '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
				}
				echo '</div></div>';
			}

			echo '</div></div></details>';
		}

		// Features
		if ($settings['show_features'] === 'yes') {
			$is_open = $settings['default_open'] === 'features' ? ' open' : '';
			echo '<details class="wpbs-accordion-item"' . $is_open . '>';
			echo '<summary class="wpbs-accordion-item__header"><h3>Features</h3></summary>';
			echo '<div class="wpbs-accordion-item__content"><div class="wpbs-details-grid">';

			// General Features Cell
			$features = [];
			if ($meta['condition']) $features['Condition'] = $meta['condition'];
			if ($meta['boat_category']) $features['Category'] = $meta['boat_category'];
			if ($meta['boat_class']) $features['Class'] = $meta['boat_class'];
			if ($meta['hull_material']) $features['Hull Material'] = $meta['hull_material'];
			if ($keel_type) $features['Keel Type'] = $keel_type;

			if (!empty($features)) {
				echo '<div class="wpbs-details-cell"><h3>General</h3><div class="wpbs-details-cell__content">';
				foreach ($features as $label => $value) {
					echo '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
				}
				echo '</div></div>';
			}

			// Electronics Cell
			$electronics = [];
			if ($trim_tabs) $electronics['Trim Tabs'] = '✓';
			if ($windlass) $electronics['Windlass'] = $windlass;
			if ($electrical) $electronics['Electrical Circuit'] = $electrical;

			if (!empty($electronics)) {
				echo '<div class="wpbs-details-cell"><h3>Electronics & Equipment</h3><div class="wpbs-details-cell__content">';
				foreach ($electronics as $label => $value) {
					echo '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
				}
				echo '</div></div>';
			}

			// Builder & Designer Cell
			$builder_info = [];
			if ($builder) $builder_info['Builder'] = $builder;
			if ($designer) $builder_info['Designer'] = $designer;

			if (!empty($builder_info)) {
				echo '<div class="wpbs-details-cell"><h3>Builder & Designer</h3><div class="wpbs-details-cell__content">';
				foreach ($builder_info as $label => $value) {
					echo '<p><span class="wpbs-details-label">' . esc_html($label) . ':</span><span class="wpbs-details-value">' . esc_html($value) . '</span></p>';
				}
				echo '</div></div>';
			}

			echo '</div></div></details>';
		}

		// More Details (Additional Description)
		if ($settings['show_additional'] === 'yes' && $additional_detail) {
			echo '<details class="wpbs-accordion-item">';
			echo '<summary class="wpbs-accordion-item__header"><h3>More Details</h3></summary>';
			echo '<div class="wpbs-accordion-item__content">';
			echo '<div class="wpbs-additional-details">' . wp_kses_post($additional_detail) . '</div>';
			echo '</div></details>';
		}

		// Location
		if ($settings['show_location'] === 'yes' && $meta['location']) {
			echo '<details class="wpbs-accordion-item" open>';
			echo '<summary class="wpbs-accordion-item__header"><h3>Location</h3></summary>';
			echo '<div class="wpbs-accordion-item__content">';
			echo '<div class="wpbs-location-info">';
			echo '<p><strong>' . esc_html($meta['location']) . '</strong></p>';
			if ($boat_city || $state || $country) {
				echo '<p>' . esc_html(implode(', ', array_filter([$boat_city, $state, $country]))) . '</p>';
			}
			echo '</div></div></details>';
		}

		echo '</div>';
	}
}
