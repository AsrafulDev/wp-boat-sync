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
		$boat_id = $settings['boat_id'];
		$post_id = $settings['post_id'];
		
		// Get boat ID (prefer post_id, fallback to boat_id)
		if ($post_id) {
			$final_id = $post_id;
		} elseif ($boat_id && is_numeric($boat_id)) {
			$final_id = $boat_id;
		} elseif ($boat_id) {
			// Try to find by WPBS ID
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
			echo '<div class="wpbs-quick-specs"><p>No boat found.</p></div>';
			return;
		}
		
		// Get boat meta data - bulk fetch to avoid 13 individual queries
		$all_meta = get_post_meta($final_id);
		$fields = ['engine', 'engine_make', 'engine_model', 'total_power', 'engine_hours',
			'boat_class', 'boat_category', 'length', 'year', 'model', 'make', 'passenger_capacity'];
		
		$meta = [];
		foreach ($fields as $field) {
			$key = 'wpbs_' . $field;
			$meta[$field] = isset($all_meta[$key]) ? $all_meta[$key][0] : '';
		}
		$capacity = $meta['passenger_capacity'];
		
		// Prepare values
		$engine_label = $meta['engine'] ?: ($meta['engine_make'] && $meta['engine_model'] ? $meta['engine_make'] . ' ' . $meta['engine_model'] : '—');
		$class_label = $meta['boat_class'] ?: ($meta['boat_category'] ?: '—');
		$model_label = $meta['model'] ?: ($meta['make'] ? $meta['make'] : '—');
		
		?>
		<div class="wpbs-quick-specs">
			<div class="wpbs-quick-specs__list">
				<!-- Engine -->
				<div class="wpbs-quick-specs__item">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m347.35,178.61c-.15,0-.3,0-.45-.02h-165.87c-2.76,0-5-2.24-5-5v-47.05c0-15.55,12.66-28.21,28.22-28.21h119.89c15.56,0,28.21,12.66,28.21,28.21v47.07c0,1.43-.61,2.79-1.68,3.74-.92.82-2.1,1.26-3.32,1.26Zm-161.32-10.02h156.32v-42.05c0-10.04-8.17-18.21-18.21-18.21h-119.89c-10.05,0-18.22,8.17-18.22,18.21v42.05Z"/><path fill="#104f79" d="m318.65,244.53h-108.92c-13.29,0-24.87-9-28.17-21.88l-5.37-20.94c-.1-.41-.16-.82-.16-1.24v-.38c0-2.76,2.24-5,5-5h165.81c.08,0,.15,0,.23,0,1.37-.05,2.72.44,3.71,1.38.99.94,1.57,2.25,1.57,3.61v.39c0,.42-.05.83-.16,1.24l-5.36,20.94c-3.3,12.88-14.89,21.88-28.18,21.88Z"/><path fill="#104f79" d="m233.76,413.24c-14.88,0-26.98-12.1-26.98-26.98v-145.31c0-2.76,2.24-5,5-5h86.82c1.57,0,3.05.74,4,2,.94,1.26,1.24,2.88.81,4.39l-43.73,151.41c-3.31,11.48-13.97,19.49-25.92,19.49Z"/></svg>
					<div class="wpbs-quick-specs__label">Engine</div>
					<div class="wpbs-quick-specs__value"><?php echo esc_html($engine_label); ?></div>
				</div>
				
				<!-- Total Power -->
				<div class="wpbs-quick-specs__item">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m258,306.66c-27.11,0-49.17-22.06-49.17-49.17s22.06-49.17,49.17-49.17,49.17,22.06,49.17,49.17-22.06,49.17-49.17,49.17Zm0-88.33c-21.6,0-39.17,17.57-39.17,39.17s17.57,39.17,39.17,39.17,39.17-17.57,39.17-39.17-17.57-39.17-39.17-39.17Z"/><path fill="#104f79" d="m220.41,239.31c-1.49,0-2.95-.66-3.94-1.92-34.8-44.45-28.12-75.46-21.05-89.99,10.22-20.98,33.39-34.02,60.47-34.02,20.55,0,38,5.08,50.48,14.69,11.42,8.8,17.97,21.17,17.97,33.94,0,20.28-11.74,26.97-21.16,32.35-9.41,5.37-16.84,9.61-16.12,25.88.12,2.76-2.01,5.09-4.77,5.22-2.73.09-5.09-2.01-5.22-4.77-.99-22.38,11.29-29.38,21.16-35.01,9.01-5.14,16.12-9.19,16.12-23.66,0-25.36-29.41-38.63-58.46-38.63-23.22,0-42.95,10.88-51.48,28.4-6.05,12.43-11.52,39.28,19.93,79.45,1.7,2.17,1.32,5.32-.85,7.02-.91.72-2,1.06-3.08,1.06Z"/><path fill="#104f79" d="m258,277.2c-10.87,0-19.71-8.84-19.71-19.71s8.84-19.71,19.71-19.71,19.71,8.84,19.71,19.71-8.84,19.71-19.71,19.71Zm0-29.41c-5.35,0-9.71,4.35-9.71,9.71s4.35,9.71,9.71,9.71,9.71-4.35,9.71-9.71-4.35-9.71-9.71-9.71Z"/></svg>
					<div class="wpbs-quick-specs__label">Total Power</div>
					<div class="wpbs-quick-specs__value"><?php echo esc_html($meta['total_power'] ?: '—'); ?></div>
				</div>
				
				<!-- Engine Hours -->
				<div class="wpbs-quick-specs__item">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="19.5" fill="none" stroke="#e0e0e0"/><path fill="#104f79" d="M31.72 15.33c-.3-.71-.68-1.4-1.11-2.04-.43-.64-.92-1.23-1.47-1.78-.54-.54-1.14-1.03-1.78-1.47-.64-.43-1.35-.8-2.04-1.11C23.86 8.32 22.28 8 20.67 8s-3.19.32-4.67.94c-.71.3-1.4.68-2.04 1.11-.64.43-1.23.92-1.78 1.47-.55.54-1.04 1.14-1.47 1.78-.43.64-.8 1.35-1.11 2.04C8.99 16.81 8.67 18.38 8.67 20s.32 3.19.94 4.67c.3.71.68 1.4 1.11 2.04.43.64.92 1.23 1.47 1.78.54.54 1.14 1.03 1.78 1.47.64.43 1.35.8 2.04 1.11 1.48.62 3.05.94 4.67.94s3.19-.32 4.67-.94c.71-.3 1.4-.68 2.04-1.11.64-.43 1.23-.92 1.78-1.47.54-.54 1.03-1.14 1.47-1.78.43-.64.8-1.35 1.11-2.04.62-1.48.94-3.05.94-4.67s-.32-3.19-.94-4.67Zm-.71 8.96c-.57 1.34-1.38 2.54-2.41 3.58s-2.24 1.86-3.58 2.41c-1.38.58-2.86.88-4.37.88s-2.99-.3-4.37-.88c-1.34-.57-2.54-1.38-3.58-2.41s-1.86-2.24-2.41-3.58c-.58-1.38-.88-2.86-.88-4.37s.3-2.99.88-4.37c.57-1.34 1.38-2.54 2.41-3.58s2.24-1.86 3.58-2.41c1.38-.58 2.86-.88 4.37-.88s2.99.3 4.37.88c1.34.57 2.54 1.38 3.58 2.41s1.86 2.24 2.41 3.58c.58 1.38.88 2.86.88 4.37s-.3 2.99-.88 4.37Z"/><path fill="#104f79" d="M23.29 14.08c-.94 1.16-2.98 3.75-3.75 5.38-.3.64-.03 1.41.64 1.71.64.3 1.4.02 1.71-.64.75-1.61 1.47-4.85 1.78-6.31.04-.2-.21-.32-.34-.16l-.04.02Z"/><rect fill="#104f79" x="20.17" y="11.12" width=".99" height="1.19"/><rect fill="#104f79" x="14.04" y="13.37" width=".99" height="1.19" transform="rotate(-45 14.54 13.97)"/><rect fill="#104f79" x="11.79" y="19.51" width="1.19" height=".99"/><rect fill="#104f79" x="14.04" y="25.09" width=".99" height="1.19" transform="rotate(-45 14.54 25.69)"/><rect fill="#104f79" x="25.74" y="25.09" width=".99" height="1.19" transform="rotate(45 26.24 25.69)"/><rect fill="#104f79" x="28.35" y="19.51" width="1.19" height=".99"/><rect fill="#104f79" x="25.74" y="13.37" width=".99" height="1.19" transform="rotate(45 26.24 13.97)"/></svg>
					<div class="wpbs-quick-specs__label">Engine Hours</div>
					<div class="wpbs-quick-specs__value"><?php echo esc_html($meta['engine_hours'] ?: '—'); ?></div>
				</div>
				
				<!-- Class -->
				<div class="wpbs-quick-specs__item">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m163.73,294.58c-4.25,1.36-8.44,1.48-12.52.34-5.4-1.51-8.73-4.75-8.74-4.76-.94-.95-2.22-1.49-3.56-1.49h-.23c-1.07,0-2.1.34-2.96.97-7.45,5.47-14.8,7.25-21.84,5.28-5.4-1.51-8.74-4.75-8.75-4.76-.94-.95-2.22-1.49-3.56-1.49h-.22c-1.06,0-2.1.34-2.96.97-7.45,5.47-14.8,7.25-21.84,5.28-5.33-1.49-8.65-4.67-8.77-4.78-1.94-1.95-5.09-1.96-7.05-.03-1.96,1.94-1.98,5.1-.05,7.07,6.1,6.18,22.02,14.14,40.47,2.66,7.5,5.37,21.48,9.86,37.34,0,4.26,3.06,10.63,5.83,18.25,5.83,3.64,0,7.57-.63,11.68-2.17l-4.69-8.92Z"/><path fill="#104f79" d="m372.08,262.35c-.07-.34-.18-.68-.32-1.01-.6-1.39-1.8-2.43-3.26-2.83l-31.79-8.73-6.94-39.29c-.35-1.98-.9-3.89-1.65-5.68-4.17-10.23-14.17-17.18-25.6-17.18h-17.56v-4.44c0-7.43-6.04-13.47-13.47-13.47h-22.83c-7.43,0-13.47,6.04-13.47,13.47v4.44h-17.56c-13.45,0-24.91,9.61-27.25,22.86l-6.69,37.88-.25,1.41-22.31,6.13h-.01l-9.47,2.6c-1.46.4-2.66,1.44-3.26,2.83s-.54,2.98.16,4.32l46.84,89.21c.86,1.64,2.56,2.67,4.42,2.67h120.53c1.86,0,3.56-1.03,4.43-2.67l46.83-89.21c.54-1.02.71-2.19.48-3.31Z"/></svg>
					<div class="wpbs-quick-specs__label">Class</div>
					<div class="wpbs-quick-specs__value"><?php echo esc_html($class_label); ?></div>
				</div>
				
				<!-- Length -->
				<div class="wpbs-quick-specs__item">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m359.98,396.11h-207.96c-16.54,0-30-13.46-30-30v-110.84c0-16.54,13.46-30,30-30h207.96c16.54,0,30,13.46,30,30v110.84c0,16.54-13.46,30-30,30Zm-207.96-160.84c-11.03,0-20,8.97-20,20v110.84c0,11.03,8.97,20,20,20h207.96c11.03,0,20-8.97,20-20v-110.84c0-11.03-8.97-20-20-20h-207.96Z"/><path fill="#104f79" d="m384.98,164h-257.96c-2.76,0-5-2.24-5-5s2.24-5,5-5h257.96c2.76,0,5,2.24,5,5s-2.24,5-5,5Z"/><path fill="#104f79" d="m165.13,202.12c-1.28,0-2.56-.49-3.54-1.46l-38.11-38.11c-.94-.94-1.46-2.21-1.46-3.54s.53-2.6,1.46-3.54l38.11-38.11c1.95-1.95,5.12-1.95,7.07,0,1.95,1.95,1.95,5.12,0,7.07l-34.58,34.58,34.58,34.58c1.95,1.95,1.95,5.12,0,7.07-.98.98-2.26,1.46-3.54,1.46Z"/><path fill="#104f79" d="m346.87,202.12c-1.28,0-2.56-.49-3.54-1.46-1.95-1.95-1.95-5.12,0-7.07l34.58-34.58-34.58-34.58c-1.95-1.95-1.95-5.12,0-7.07,1.95-1.95,5.12-1.95,7.07,0l38.11,38.11c.94.94,1.46,2.21,1.46,3.54s-.53,2.6-1.46,3.54l-38.11,38.11c-.98.98-2.26,1.46-3.54,1.46Z"/></svg>
					<div class="wpbs-quick-specs__label">Length</div>
					<div class="wpbs-quick-specs__value"><?php echo esc_html($meta['length'] ?: '—'); ?></div>
				</div>
				
				<!-- Year -->
				<div class="wpbs-quick-specs__item">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m365.67,387.07h-221.18c-12.68,0-23-10.32-23-23v-203.13c0-12.68,10.32-23,23-23h221.18c12.68,0,23,10.32,23,23v203.13c0,12.68-10.32,23-23,23Zm-221.18-239.13c-7.17,0-13,5.83-13,13v203.13c0,7.17,5.83,13,13,13h221.18c7.17,0,13-5.83,13-13v-203.13c0-7.17-5.83-13-13-13h-221.18Z"/><path fill="#104f79" d="m383.67,210.53h-257.18c-2.76,0-5-2.24-5-5s2.24-5,5-5h257.18c2.76,0,5,2.24,5,5s-2.24,5-5,5Z"/><path fill="#104f79" d="m152.96,259.33c-1.06-1.99-.13-4.64,1.99-5.71,1.99-.93,4.64-.13,5.71,1.99l14.73,29.46,14.73-29.46c1.06-2.12,3.58-2.92,5.71-1.99,2.12,1.06,3.05,3.72,1.99,5.71l-18.18,36.36v38.75c0,2.52-1.73,4.25-4.25,4.25s-4.25-1.73-4.25-4.25v-38.75l-18.18-36.36Z"/><path fill="#104f79" d="m314.47,174.57c-2.76,0-5-2.24-5-5v-53.01c0-2.76,2.24-5,5-5s5,2.24,5,5v53.01c0,2.76-2.24,5-5,5Zm-118.78,0c-2.76,0-5-2.24-5-5v-53.01c0-2.76,2.24-5,5-5s5,2.24,5,5v53.01c0,2.76-2.24,5-5,5Z"/></svg>
					<div class="wpbs-quick-specs__label">Year</div>
					<div class="wpbs-quick-specs__value"><?php echo esc_html($meta['year'] ?: '—'); ?></div>
				</div>
				
				<!-- Model -->
				<div class="wpbs-quick-specs__item">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m430.37,331.25c-23.1,0-34.69,6.55-46.03,12.85-10.58,5.96-20.66,11.51-41.16,11.51-8.65,0-16.21-1.01-22.93-3.11-7.06-2.18-12.51-5.21-18.39-8.48-4.2-2.35-8.48-4.79-13.44-6.8-9.74-4.03-19.99-5.96-32.42-5.96s-22.76,1.93-32.42,5.96c-5.12,2.1-9.41,4.54-13.61,6.89-10.67,5.96-20.66,11.51-41.16,11.51s-30.57-5.54-41.24-11.51c-4.2-2.35-8.57-4.79-13.61-6.89-7.73-3.28-15.62-5.04-24.86-5.71-2.35-.17-4.96-.25-7.48-.25-2.77,0-5.04,2.27-5.04,4.96s2.27,4.96,5.04,4.96c2.35,0,4.62.08,6.8.25,8.06.59,15.03,2.1,21.75,4.96,4.45,1.85,8.31,4.03,12.6,6.38,11.25,6.3,22.93,12.85,46.03,12.85s34.69-6.55,45.94-12.85c4.2-2.35,8.06-4.54,12.6-6.38,8.57-3.61,17.39-5.21,28.64-5.21s19.99,1.6,28.56,5.21c4.62,1.93,8.65,4.2,12.6,6.38,5.96,3.36,12.09,6.8,20.16,9.32,7.73,2.35,16.21,3.53,25.87,3.53,23.1,0,34.77-6.55,46.03-12.85,10.67-5.96,20.66-11.59,41.16-11.59,2.77,0,4.96-2.18,4.96-4.96s-2.18-4.96-4.96-4.96Z"/><path fill="#104f79" d="m372.08,262.35c-.07-.34-.18-.68-.32-1.01-.6-1.39-1.8-2.43-3.26-2.83l-31.79-8.73-6.94-39.29c-.35-1.98-.9-3.89-1.65-5.68-4.17-10.23-14.17-17.18-25.6-17.18h-17.56v-4.44c0-7.43-6.04-13.47-13.47-13.47h-22.83c-7.43,0-13.47,6.04-13.47,13.47v4.44h-17.56c-13.45,0-24.91,9.61-27.25,22.86l-6.69,37.88-.25,1.41-22.31,6.13h-.01l-9.47,2.6c-1.46.4-2.66,1.44-3.26,2.83s-.54,2.98.16,4.32l46.84,89.21c.86,1.64,2.56,2.67,4.42,2.67h120.53c1.86,0,3.56-1.03,4.43-2.67l46.83-89.21c.54-1.02.71-2.19.48-3.31Z"/></svg>
					<div class="wpbs-quick-specs__label">Model</div>
					<div class="wpbs-quick-specs__value"><?php echo esc_html($model_label); ?></div>
				</div>
				
				<!-- Capacity -->
				<div class="wpbs-quick-specs__item">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="19.5" fill="none" stroke="#e0e0e0"/><path fill="#104f79" d="M18.85 11.93c-1.42.39-2.53 1.56-2.9 3.05-.32 1.3.07 2.77.99 3.77l.39.42-.44.16-.44.15-.34-.34c-.21-.2-.55-.44-.86-.6l-.52-.26.22-.27c1.04-1.26 1-3.04-.09-4.27-.74-.83-1.94-1.21-2.97-.92-1.06.29-2 1.29-2.24 2.36-.08.36-.08 1.12 0 1.48.09.41.38.98.67 1.33l.26.31-.34.14c-1.08.46-1.93 1.5-2.16 2.68-.11.51-.13 2.48-.02 2.67.18.34 2.55 1.21 3.32 1.21.26 0 .45-.21.45-.5 0-.31-.13-.4-.77-.54-.69-.15-1.27-.34-1.76-.57l-.37-.18.02-.96c.02-.82.05-1.03.16-1.32.29-.77.88-1.37 1.63-1.66.32-.12.5-.14 1.67-.16.84-.01 1.45.01 1.71.06.45.08 1 .35 1.29.62l.19.18-.44.45c-.81.83-1.28 1.84-1.4 3.02-.04.33-.06 1.11-.05 1.72.02 1.32-.04 1.21.84 1.63 3.35 1.61 7.07 1.61 10.39 0 .91-.44.85-.32.87-1.63.01-.61-.01-1.39-.05-1.72-.12-1.18-.59-2.19-1.4-3.02l-.44-.45.2-.18c.31-.28.86-.55 1.3-.62.25-.05.87-.06 1.71-.06 1.17.01 1.35.03 1.67.16.75.29 1.34.89 1.63 1.66.11.29.14.5.16 1.33l.02.96-.22.12c-.4.23-1.21.48-1.85.62-.69.15-.82.23-.82.54 0 .29.19.5.48.5.28 0 1.11-.2 1.68-.4.67-.23 1.53-.67 1.61-.81.1-.19.08-2.16-.02-2.67-.24-1.16-1.09-2.22-2.16-2.68l-.34-.14.26-.31c.48-.58.74-1.28.74-2.07-.01-.92-.27-1.57-.9-2.23-.64-.67-1.32-.86-2.21-.86-1.64.01-3.03 1.48-3.03 3.2 0 .74.28 1.5.76 2.09l.22.27-.52.26c-.32.16-.65.4-.86.6l-.34.34-.45-.16-.45-.16.21-.17c.52-.45 1.08-1.45 1.24-2.23.36-1.8-.53-3.67-2.17-4.5-.82-.42-1.8-.53-2.65-.32Zm1.53.93c1.11.24 2.11 1.28 2.34 2.44.21 1.04-.08 2.03-.86 2.81-1.22 1.27-2.98 1.27-4.20 0s-1.22-2.54 0-3.81c.76-.79 1.67-1.07 2.72-.85ZM13.5 13.88c.38.18.89.69 1.08 1.11.15.3.16.42.16.93 0 .52-.02.65-.17.98-.21.42-.7.92-1.08 1.11-.23.11-.39.13-.84.13-.49 0-.64-.02-.94-.15-1.24-.63-1.61-2.24-.84-3.38.21-.29.61-.58 1-.73.34-.13 1.15-.13 1.50 0Zm14.3.03c.45.23.84.64 1.03 1.07.14.3.16.43.16.93 0 .52-.02.65-.17.93-.22.45-.61.86-1.03 1.11-.28.15-.39.17-.88.17-.45 0-.61-.03-.84-.13-.39-.19-.87-.62-1.08-1.04-.15-.3-.17-.43-.17-.93 0-.70.11-1.01.52-1.47.47-.57.85-.72 1.52-.69.37.01.56.04.81.15Zm-5.87 6.21c1.34.29 2.49 1.44 2.83 2.82.13.54.18 2.74.07 2.83-.17.14-1.29.59-1.94.78-1.11.33-1.82.43-3.09.43s-1.98-.1-3.09-.43c-.65-.2-1.77-.65-1.94-.78-.11-.09-.06-2.29.07-2.83.34-1.37 1.48-2.51 2.86-2.82.56-.13 2.69-.13 3.23 0Z"/></svg>
					<div class="wpbs-quick-specs__label">Capacity</div>
					<div class="wpbs-quick-specs__value"><?php echo esc_html($capacity ?: '—'); ?></div>
				</div>
			</div>
		</div>
		<?php
	}
}
