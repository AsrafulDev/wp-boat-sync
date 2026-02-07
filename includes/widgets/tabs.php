<?php
/**
 * WPBS Elementor Widget - Boat Tabs
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Elementor_Tabs_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'wpbs_tabs';
	}

	public function get_title()
	{
		return esc_html__('Boat Tabs', 'wp-boat-sync');
	}

	public function get_icon()
	{
		return 'eicon-tabs';
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
				'label' => esc_html__('Tabs Settings', 'wp-boat-sync'),
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

		// Tabs Navigation Style
		$this->start_controls_section(
			'tabs_nav_style',
			[
				'label' => esc_html__('Tab Navigation', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'tabs_nav_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__nav' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'tabs_nav_border',
				'selector' => '{{WRAPPER}} .wpbs-tabs__nav',
			]
		);

		$this->add_responsive_control(
			'tabs_nav_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__nav' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'tabs_nav_gap',
			[
				'label' => esc_html__('Gap Between Tabs', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => ['px' => ['min' => 0, 'max' => 50]],
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__nav' => 'gap: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Tab Button Style
		$this->start_controls_section(
			'tab_button_style',
			[
				'label' => esc_html__('Tab Buttons', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('tab_button_tabs');

		$this->start_controls_tab('tab_button_normal', ['label' => esc_html__('Normal', 'wp-boat-sync')]);

		$this->add_control(
			'tab_button_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__tab' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'tab_button_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__tab' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab('tab_button_hover', ['label' => esc_html__('Hover', 'wp-boat-sync')]);

		$this->add_control(
			'tab_button_hover_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__tab:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'tab_button_hover_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__tab:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab('tab_button_active', ['label' => esc_html__('Active', 'wp-boat-sync')]);

		$this->add_control(
			'tab_button_active_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__tab.is-active' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'tab_button_active_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__tab.is-active' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'tab_button_typography',
				'selector' => '{{WRAPPER}} .wpbs-tabs__tab',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'tab_button_border',
				'selector' => '{{WRAPPER}} .wpbs-tabs__tab',
			]
		);

		$this->add_responsive_control(
			'tab_button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__tab' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'tab_button_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__tab' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Tab Content Style
		$this->start_controls_section(
			'tab_content_style',
			[
				'label' => esc_html__('Tab Content', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'tab_content_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__content' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'tab_content_border',
				'selector' => '{{WRAPPER}} .wpbs-tabs__content',
			]
		);

		$this->add_responsive_control(
			'tab_content_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'tab_content_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_control(
			'tab_content_text_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-tabs__panel' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'tab_content_typography',
				'selector' => '{{WRAPPER}} .wpbs-tabs__panel',
			]
		);

		$this->end_controls_section();
	}

	private function get_boat_meta($post_id)
	{
		// Fetch ALL meta at once to avoid 29 individual queries
		$all_meta = get_post_meta($post_id);
		
		$fields = [
			'length', 'beam', 'draft', 'displacement', 'dry_weight', 'bridge_clearance',
			'deadrise', 'cabins', 'heads', 'engine', 'num_engines', 'total_power',
			'engine_hours', 'engine_type', 'engine_make', 'engine_model', 'fuel_type',
			'drive_type', 'propeller', 'cruising_speed', 'max_speed', 'fuel_capacity',
			'range', 'hull_material', 'hull_id', 'boat_category', 'boat_class',
			'condition', 'water_capacity'
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
		
		// Get boat ID
		$boat_id = $settings['boat_id'];
		$post_id = $settings['post_id'];
		
		if ($post_id) {
			$final_id = $post_id;
		} elseif ($boat_id && is_numeric($boat_id)) {
			$final_id = $boat_id;
		} elseif ($boat_id) {
			$args = array(
				'post_type' => 'boats',
				'meta_query' => array(
					array(
						'key' => 'wpbs_boat_id',
						'value' => $boat_id,
						'compare' => '='
					)
				),
				'posts_per_page' => 1,
				'fields' => 'ids'
			);
			$query = new \WP_Query($args);
			$final_id = $query->posts ? $query->posts[0] : 0;
			wp_reset_postdata();
		} else {
			$final_id = get_the_ID();
		}
		
		if (!$final_id) {
			return;
		}
		
		$post = get_post($final_id);
		$meta = $this->get_boat_meta($final_id);
		
		?>
		<div class="wpbs-tabs">
			<div class="wpbs-tabs__nav">
				<button type="button" class="wpbs-tabs__btn is-active" data-tab="description">Description</button>
				<button type="button" class="wpbs-tabs__btn" data-tab="measurements">Measurements</button>
				<button type="button" class="wpbs-tabs__btn" data-tab="propulsion">Propulsion</button>
				<button type="button" class="wpbs-tabs__btn" data-tab="features">Features</button>
			</div>
			<div class="wpbs-tabs__content" id="wpbs-tab-content">
				<!-- Description Tab -->
				<div class="wpbs-tab-pane is-active" data-pane="description">
					<?php if ($post->post_content): ?>
						<?php echo apply_filters('the_content', $post->post_content); ?>
					<?php else: ?>
						<p class="wpbs-description-empty">No description available.</p>
					<?php endif; ?>
				</div>
				
				<!-- Measurements Tab -->
				<div class="wpbs-tab-pane" data-pane="measurements">
					<div class="wpbs-specs-table">
						<?php 
						$measurements = array(
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
						foreach ($measurements as $label => $value):
							if ($value):
						?>
							<div class="wpbs-specs-row">
								<span class="wpbs-specs-row__label"><?php echo esc_html($label); ?></span>
								<span class="wpbs-specs-row__value"><?php echo esc_html($value); ?></span>
							</div>
						<?php endif; endforeach; ?>
					</div>
				</div>
				
				<!-- Propulsion Tab -->
				<div class="wpbs-tab-pane" data-pane="propulsion">
					<div class="wpbs-specs-table">
						<?php 
						$propulsion = array(
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
						foreach ($propulsion as $label => $value):
							if ($value):
						?>
							<div class="wpbs-specs-row">
								<span class="wpbs-specs-row__label"><?php echo esc_html($label); ?></span>
								<span class="wpbs-specs-row__value"><?php echo esc_html($value); ?></span>
							</div>
						<?php endif; endforeach; ?>
					</div>
				</div>
				
				<!-- Features Tab -->
				<div class="wpbs-tab-pane" data-pane="features">
					<div class="wpbs-specs-table">
						<?php 
						$features = array(
							'Hull Material' => $meta['hull_material'],
							'Hull ID' => $meta['hull_id'],
							'Boat Category' => $meta['boat_category'],
							'Boat Class' => $meta['boat_class'],
							'Condition' => $meta['condition'],
							'Water Capacity' => $meta['water_capacity'],
						);
						foreach ($features as $label => $value):
							if ($value):
						?>
							<div class="wpbs-specs-row">
								<span class="wpbs-specs-row__label"><?php echo esc_html($label); ?></span>
								<span class="wpbs-specs-row__value"><?php echo esc_html($value); ?></span>
							</div>
						<?php endif; endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		
		<script>
		document.addEventListener("DOMContentLoaded", function() {
			var btns = document.querySelectorAll(".wpbs-tabs__btn");
			var panes = document.querySelectorAll(".wpbs-tab-pane");
			btns.forEach(function(b) {
				b.addEventListener("click", function() {
					var t = b.getAttribute("data-tab");
					btns.forEach(function(x) { x.classList.remove("is-active"); });
					b.classList.add("is-active");
					panes.forEach(function(p) {
						p.style.display = p.getAttribute("data-pane") === t ? "block" : "none";
					});
				});
			});
		});
		</script>
		<?php
	}
}
