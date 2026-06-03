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
			'items_columns',
			[
				'label' => esc_html__('Columns', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '4',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options' => [
					'1' => esc_html__('1', 'wp-boat-sync'),
					'2' => esc_html__('2', 'wp-boat-sync'),
					'3' => esc_html__('3', 'wp-boat-sync'),
					'4' => esc_html__('4', 'wp-boat-sync'),
				],
				'selectors' => [
					'{{WRAPPER}} .wpbs-quick-specs__list' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				],
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
			'icon_position',
			[
				'label' => esc_html__('Position', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left' => esc_html__('Left', 'wp-boat-sync'),
					'top' => esc_html__('Top', 'wp-boat-sync'),
					'right' => esc_html__('Right', 'wp-boat-sync'),
					'bottom' => esc_html__('Bottom', 'wp-boat-sync'),
				],
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

		// Text Alignment
		$this->start_controls_section(
			'text_style',
			[
				'label' => esc_html__('Text Alignment', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'text_align',
			[
				'label' => esc_html__('Alignment', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'left',
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
					'{{WRAPPER}} .wpbs-quick-specs__item' => 'text-align: {{VALUE}};',
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
		
		// Get boat meta data - use the wpbs_ prefixed keys written by the sync
		$all_meta = get_post_meta($final_id);

		// Read canonical meta keys (single-query results are in [0])
		$meta = [];
		$meta['engine_summary'] = isset($all_meta['wpbs_engine_summary']) ? $all_meta['wpbs_engine_summary'][0] : '';
		$meta['engines_json'] = isset($all_meta['wpbs_engines_json']) ? $all_meta['wpbs_engines_json'][0] : '';
		$meta['engine_make'] = isset($all_meta['wpbs_engine_make']) ? $all_meta['wpbs_engine_make'][0] : '';
		$meta['engine_model'] = isset($all_meta['wpbs_engine_model']) ? $all_meta['wpbs_engine_model'][0] : '';
		$meta['total_power'] = isset($all_meta['wpbs_total_engine_power']) ? $all_meta['wpbs_total_engine_power'][0] : '';
		$meta['engine_hours'] = isset($all_meta['wpbs_total_engine_hours']) ? $all_meta['wpbs_total_engine_hours'][0] : '';
		$meta['boat_class'] = isset($all_meta['wpbs_boat_class_codes']) ? $all_meta['wpbs_boat_class_codes'][0] : (isset($all_meta['wpbs_boat_category']) ? $all_meta['wpbs_boat_category'][0] : '');
		$meta['length'] = isset($all_meta['wpbs_length_overall']) ? $all_meta['wpbs_length_overall'][0] : '';
		$meta['year'] = isset($all_meta['wpbs_model_year']) ? $all_meta['wpbs_model_year'][0] : '';
		$meta['model'] = isset($all_meta['wpbs_model']) ? $all_meta['wpbs_model'][0] : '';
		$meta['make'] = isset($all_meta['wpbs_make']) ? $all_meta['wpbs_make'][0] : '';
		$meta['wpbs_number_of_engines'] = isset($all_meta['wpbs_number_of_engines']) ? $all_meta['wpbs_number_of_engines'][0] : '';	
		$meta['passenger_capacity'] = isset($all_meta['wpbs_passenger_capacity']) ? $all_meta['wpbs_passenger_capacity'][0] : '';
		$capacity = $meta['passenger_capacity'];

		// Prepare values - prefer engines_json (detailed), fall back to engine_summary or first-engine fields
		$engine_label = '—';
		if (!empty($meta['engines_json'])) {
			$engines = json_decode($meta['engines_json'], true);
			if (is_array($engines) && count($engines) > 0) {
				if (count($engines) === 1) {
					$e = $engines[0];
					$engine_label = (!empty($e['Make']) || !empty($e['Model'])) ? trim((isset($e['Make']) ? $e['Make'] : '') . ' ' . (isset($e['Model']) ? $e['Model'] : '')) : (!empty($e['Summary']) ? $e['Summary'] : '—');
				} else {
					$parts = [];
					foreach ($engines as $e) {
						$parts[] = (!empty($e['Make']) || !empty($e['Model'])) ? trim((isset($e['Make']) ? $e['Make'] : '') . ' ' . (isset($e['Model']) ? $e['Model'] : '')) : (isset($e['Summary']) ? $e['Summary'] : '');
					}
					$engine_label = implode(' / ', array_filter($parts));
				}
			}
		}
		if ($engine_label === '—') {
			if (!empty($meta['engine_summary'])) {
				$engine_label = $meta['engine_summary'];
			} elseif (!empty($meta['engine_make']) || !empty($meta['engine_model'])) {
				$engine_label = trim($meta['engine_make'] . ' ' . $meta['engine_model']);
			} else {
				$engine_label = '—';
			}
		}
		$class_label = $meta['boat_class'] ?: '—';
		$model_label = $meta['model'] ?: ($meta['make'] ? $meta['make'] : '—');

		// Dynamic CSS classes
		$icon_position = isset($settings['icon_position']) ? $settings['icon_position'] : 'left';
		$item_icon_class = 'wpbs-quick-specs__item--icon-' . $icon_position;

		$text_align = isset($settings['text_align']) ? $settings['text_align'] : '';
		$list_text_class = $text_align ? 'wpbs-quick-specs__list--text-' . $text_align : '';

		// Grid columns: use Elementor responsive settings or fallback to defaults
		$cols_desktop = isset($settings['items_columns']) ? $settings['items_columns'] : '4';
		$cols_class = 'wpbs-quick-specs__list--cols-' . $cols_desktop;
		
		?>
		<div class="wpbs-quick-specs">
			<div class="wpbs-quick-specs__list <?php echo esc_attr($cols_class . ' ' . $list_text_class); ?>">
				<!-- Engine -->
				<div class="wpbs-quick-specs__item <?php echo esc_attr($item_icon_class); ?>">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path d="m347.35,178.61c-.15,0-.3,0-.45-.02h-165.87c-2.76,0-5-2.24-5-5v-47.05c0-15.55,12.66-28.21,28.22-28.21h119.89c15.56,0,28.21,12.66,28.21,28.21v47.07c0,1.43-.61,2.79-1.68,3.74-.92.82-2.1,1.26-3.32,1.26Zm-161.32-10.02h156.32v-42.05c0-10.04-8.17-18.21-18.21-18.21h-119.89c-10.05,0-18.22,8.17-18.22,18.21v42.05Z" /><path d="m318.65,244.53h-108.92c-13.29,0-24.87-9-28.17-21.88l-5.37-20.94c-.1-.41-.16-.82-.16-1.24v-.38c0-2.76,2.24-5,5-5h165.81c.08,0,.15,0,.23,0,1.37-.05,2.72.44,3.71,1.38.99.94,1.57,2.25,1.57,3.61v.39c0,.42-.05.83-.16,1.24l-5.36,20.94c-3.3,12.88-14.89,21.88-28.18,21.88Zm-131.27-39.44l3.87,15.08c2.17,8.46,9.77,14.36,18.49,14.36h108.92c8.72,0,16.33-5.91,18.5-14.36l3.86-15.08h-153.63Z" /><path d="m346.84,205.09h-166.52c-10.06,0-18.25-8.19-18.25-18.25,0-4.88,1.9-9.46,5.34-12.91,3.45-3.45,8.03-5.34,12.91-5.34h166.52c.28,0,.56,0,.85.03,9.77.44,17.4,8.41,17.4,18.22,0,4.88-1.9,9.46-5.34,12.91-3.28,3.27-7.62,5.16-12.21,5.33-.17.01-.43.01-.7.01Zm-166.52-26.5c-2.2,0-4.28.86-5.83,2.42-1.56,1.56-2.42,3.63-2.42,5.83,0,4.55,3.7,8.25,8.25,8.25h166.52c.08,0,.15,0,.23,0,2.17-.08,4.13-.94,5.62-2.42,1.55-1.55,2.41-3.62,2.41-5.83,0-4.45-3.48-8.07-7.92-8.23-.09,0-.18,0-.27-.02h-166.58Zm166.43-.02s0,0,0,0c0,0,0,0,0,0Z" /><path d="m320.31,224.53h-23.97c-2.76,0-5-2.24-5-5s2.24-5,5-5h23.97c2.76,0,5,2.24,5,5s-2.24,5-5,5Z" /><path d="m378.83,233.28h-39.8c-1.63,0-3.16-.79-4.1-2.13-.94-1.34-1.16-3.04-.6-4.58l6.49-17.87c.72-1.98,2.6-3.29,4.7-3.29h33.31c2.76,0,5,2.24,5,5v17.87c0,2.76-2.24,5-5,5Zm-32.67-10h27.67v-7.87h-24.81l-2.86,7.87Z" /><path d="m419.43,242c-.66,0-1.33-.04-1.99-.12l-25.21-1.93c-.08,0-.16-.01-.25-.02-10.34-1.31-18.14-10.16-18.14-20.58s7.8-19.27,18.14-20.58c.08,0,.16-.02.25-.02l25.21-1.93c4.78-.57,9.59.93,13.21,4.12,3.65,3.22,5.74,7.85,5.74,12.72v11.4c0,4.87-2.09,9.5-5.74,12.72-3.11,2.74-7.11,4.24-11.21,4.24Zm-26.31-12.01l25.19,1.93c.08,0,.16.01.25.02,2,.26,3.95-.34,5.47-1.68,1.52-1.34,2.35-3.19,2.35-5.22v-11.4c0-2.02-.84-3.88-2.35-5.22-1.52-1.34-3.46-1.94-5.47-1.68-.08,0-.16.02-.25.02l-25.19,1.93c-5.3.73-9.28,5.28-9.28,10.64s3.98,9.92,9.28,10.64Z" /><path d="m233.76,413.24c-14.88,0-26.98-12.1-26.98-26.98v-145.31c0-2.76,2.24-5,5-5h86.82c1.57,0,3.05.74,4,2,.94,1.26,1.24,2.88.81,4.39l-43.73,151.41c-3.31,11.48-13.97,19.49-25.92,19.49Zm-16.98-167.29v140.31c0,9.36,7.62,16.98,16.98,16.98,7.52,0,14.22-5.04,16.31-12.27l41.88-145.02h-75.17Z" /><path d="m211.78,362.72h-24.87c-10.37,0-18.81-8.44-18.81-18.81s8.44-18.81,18.81-18.81h24.87c2.76,0,5,2.24,5,5v27.63c0,2.76-2.24,5-5,5Zm-24.87-27.63c-4.86,0-8.81,3.95-8.81,8.81s3.95,8.81,8.81,8.81h19.87v-17.63h-19.87Z" /><path d="m134.6,360.33c-9.01,0-16.34-7.33-16.34-16.34s7.33-16.34,16.34-16.34,16.34,7.33,16.34,16.34-7.33,16.34-16.34,16.34Zm0-22.68c-3.5,0-6.34,2.85-6.34,6.34s2.85,6.34,6.34,6.34,6.34-2.84,6.34-6.34-2.85-6.34-6.34-6.34Z" /><path d="m139.13,337.65h-9.06c-2.43,0-4.51-1.75-4.93-4.15l-7.54-43.78c-.86-5.02.52-10.14,3.81-14.04,3.28-3.9,8.09-6.13,13.19-6.13s9.9,2.24,13.19,6.13c3.28,3.9,4.67,9.01,3.81,14.04l-7.54,43.78c-.41,2.4-2.49,4.15-4.93,4.15Zm-4.85-10h.63l6.82-39.63c.36-2.11-.22-4.26-1.6-5.89-1.38-1.64-3.4-2.58-5.54-2.58s-4.16.94-5.54,2.58c-1.38,1.64-1.96,3.79-1.6,5.9l6.82,39.62Z" /><path d="m134.6,418.43c-5.1,0-9.9-2.24-13.19-6.13-3.28-3.9-4.67-9.01-3.81-14.04l7.54-43.78c.41-2.4,2.49-4.15,4.93-4.15h9.06c2.43,0,4.51,1.75,4.93,4.15l7.54,43.78c.86,5.02-.52,10.14-3.81,14.04-3.28,3.9-8.09,6.13-13.19,6.13Zm-.32-58.1l-6.82,39.63c-.36,2.11.22,4.26,1.6,5.89,1.38,1.64,3.4,2.58,5.54,2.58s4.16-.94,5.54-2.58c1.38-1.64,1.96-3.79,1.6-5.9l-6.82-39.62h-.63Z" /><path d="m173.1,348.91h-27.16c-2.76,0-5-2.24-5-5s2.24-5,5-5h27.16c2.76,0,5,2.24,5,5s-2.24,5-5,5Z" /></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-specs__label">Engine</div>
						<div class="wpbs-quick-specs__value"><?php echo esc_html($engine_label); ?></div>
					</div>
				</div>
				
				<!-- Total Power -->
				<div class="wpbs-quick-specs__item <?php echo esc_attr($item_icon_class); ?>">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path d="m258,306.66c-27.11,0-49.17-22.06-49.17-49.17s22.06-49.17,49.17-49.17,49.17,22.06,49.17,49.17-22.06,49.17-49.17,49.17Zm0-88.33c-21.6,0-39.17,17.57-39.17,39.17s17.57,39.17,39.17,39.17,39.17-17.57,39.17-39.17-17.57-39.17-39.17-39.17Z"/><path d="m220.41,239.31c-1.49,0-2.95-.66-3.94-1.92-34.8-44.45-28.12-75.46-21.05-89.99,10.22-20.98,33.39-34.02,60.47-34.02,20.55,0,38,5.08,50.48,14.69,11.42,8.8,17.97,21.17,17.97,33.94,0,20.28-11.74,26.97-21.16,32.35-9.41,5.37-16.84,9.61-16.12,25.88.12,2.76-2.01,5.09-4.77,5.22-2.73.09-5.09-2.01-5.22-4.77-.99-22.38,11.29-29.38,21.16-35.01,9.01-5.14,16.12-9.19,16.12-23.66,0-25.36-29.41-38.63-58.46-38.63-23.22,0-42.95,10.88-51.48,28.4-6.05,12.43-11.52,39.28,19.93,79.45,1.7,2.17,1.32,5.32-.85,7.02-.91.72-2,1.06-3.08,1.06Z"/><path d="m190.15,366.87c-21.95,0-43.09-13.32-55.9-35.5-10.27-17.79-14.6-35.45-12.52-51.06,1.91-14.29,9.35-26.15,20.41-32.54,17.56-10.14,29.22-3.32,38.59,2.16,9.35,5.47,16.74,9.78,30.47,1.02,2.33-1.49,5.42-.8,6.9,1.53,1.49,2.33.8,5.42-1.53,6.9-18.89,12.05-31.09,4.91-40.9-.82-8.95-5.23-16.02-9.36-28.55-2.13-21.97,12.68-18.75,44.79-4.23,69.94,11.61,20.11,30.9,31.74,50.33,30.39,13.79-.97,39.78-9.67,58.84-56.98,1.03-2.56,3.94-3.8,6.51-2.77,2.56,1.03,3.8,3.94,2.77,6.51-21.09,52.36-51.29,62.09-67.41,63.22-1.27.09-2.54.13-3.8.13Z" /><path d="m327.44,367.88c-7.12,0-13.95-1.74-19.93-5.19-17.56-10.14-17.49-23.65-17.43-34.5.06-10.83.1-19.39-14.35-26.9-2.45-1.27-3.4-4.29-2.13-6.74,1.27-2.45,4.29-3.4,6.74-2.13,19.88,10.33,19.8,24.47,19.74,35.83-.05,10.37-.1,18.56,12.43,25.79,21.96,12.68,48.16-6.15,62.69-31.31,11.61-20.11,12.05-42.64,1.15-58.79-7.74-11.46-28.26-29.61-78.77-22.46-2.74.39-5.26-1.52-5.65-4.25-.39-2.73,1.51-5.26,4.25-5.65,55.89-7.92,79.41,13.37,88.46,26.77,13.06,19.34,12.77,45.93-.77,69.38-10.27,17.79-23.4,30.37-37.96,36.37-6.13,2.52-12.39,3.78-18.45,3.78Z" /><path d="m258,277.2c-10.87,0-19.71-8.84-19.71-19.71s8.84-19.71,19.71-19.71,19.71,8.84,19.71,19.71-8.84,19.71-19.71,19.71Zm0-29.41c-5.35,0-9.71,4.35-9.71,9.71s4.35,9.71,9.71,9.71,9.71-4.35,9.71-9.71-4.35-9.71-9.71-9.71Z" /><path d="m258,385.26c-11.73,0-23.34-1.59-34.53-4.72-2.66-.74-4.21-3.5-3.47-6.16.74-2.66,3.5-4.21,6.16-3.47,10.31,2.89,21.02,4.35,31.83,4.35s21.24-1.42,31.41-4.23c2.66-.73,5.42.83,6.15,3.49.74,2.66-.83,5.42-3.49,6.15-11.04,3.05-22.51,4.6-34.07,4.6Z" /><path d="m375.24,225.99c-2.13,0-4.11-1.37-4.77-3.52-5.63-18.09-15.72-34.78-29.2-48.25-1.95-1.95-1.95-5.12,0-7.07,1.95-1.95,5.12-1.95,7.07,0,14.62,14.62,25.57,32.72,31.68,52.35.82,2.64-.65,5.44-3.29,6.26-.5.15-1,.23-1.49.23Z" /><path d="m139.75,229.39c-.45,0-.9-.06-1.35-.19-2.66-.74-4.21-3.5-3.47-6.16,5.88-21.04,17.19-40.36,32.72-55.89,1.95-1.95,5.12-1.95,7.07,0,1.95,1.95,1.95,5.12,0,7.07-14.32,14.32-24.75,32.13-30.16,51.51-.62,2.21-2.63,3.66-4.81,3.66Z" /><path d="m258,416.61c-28.01,0-55.55-7.38-79.66-21.34-2.39-1.38-3.2-4.44-1.82-6.83,1.38-2.39,4.44-3.2,6.83-1.82,22.58,13.08,48.39,20,74.64,20s52.43-7.01,75.13-20.28c2.39-1.39,5.45-.59,6.84,1.79,1.39,2.38.59,5.45-1.79,6.84-24.23,14.16-51.96,21.65-80.18,21.65Z" /><path d="m412.12,262.49c-2.76,0-5-2.24-5-5,0-26.53-7.05-52.57-20.39-75.32-12.95-22.09-31.49-40.59-53.59-53.52-2.38-1.39-3.19-4.46-1.79-6.84,1.39-2.38,4.45-3.19,6.84-1.79,23.58,13.78,43.35,33.53,57.17,57.09,14.24,24.28,21.77,52.08,21.77,80.38,0,2.76-2.24,5-5,5Z" /><path d="m103.88,262.49c-2.76,0-5-2.24-5-5,0-28.32,7.54-56.13,21.79-80.42,13.83-23.57,33.62-43.32,57.23-57.1,2.39-1.39,5.45-.59,6.84,1.8,1.39,2.38.59,5.45-1.8,6.84-22.13,12.92-40.68,31.42-53.65,53.52-13.36,22.76-20.42,48.82-20.42,75.36,0,2.76-2.24,5-5,5Z" /></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-specs__label">Total Power</div>
					<div class="wpbs-quick-specs__value"><?php echo esc_html($meta['total_power'] ?: '—'); ?></div>
					</div>
				</div>
				
				<!-- Engine Hours -->
				<div class="wpbs-quick-specs__item <?php echo esc_attr($item_icon_class); ?>">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="19.5" fill="none" stroke="#e0e0e0"/><path fill="#104f79" d="M31.72 15.33c-.3-.71-.68-1.4-1.11-2.04-.43-.64-.92-1.23-1.47-1.78-.54-.54-1.14-1.03-1.78-1.47-.64-.43-1.35-.8-2.04-1.11C23.86 8.32 22.28 8 20.67 8s-3.19.32-4.67.94c-.71.3-1.4.68-2.04 1.11-.64.43-1.23.92-1.78 1.47-.55.54-1.04 1.14-1.47 1.78-.43.64-.8 1.35-1.11 2.04C8.99 16.81 8.67 18.38 8.67 20s.32 3.19.94 4.67c.3.71.68 1.4 1.11 2.04.43.64.92 1.23 1.47 1.78.54.54 1.14 1.03 1.78 1.47.64.43 1.35.8 2.04 1.11 1.48.62 3.05.94 4.67.94s3.19-.32 4.67-.94c.71-.3 1.4-.68 2.04-1.11.64-.43 1.23-.92 1.78-1.47.54-.54 1.03-1.14 1.47-1.78.43-.64.8-1.35 1.11-2.04.62-1.48.94-3.05.94-4.67s-.32-3.19-.94-4.67Zm-.71 8.96c-.57 1.34-1.38 2.54-2.41 3.58s-2.24 1.86-3.58 2.41c-1.38.58-2.86.88-4.37.88s-2.99-.3-4.37-.88c-1.34-.57-2.54-1.38-3.58-2.41s-1.86-2.24-2.41-3.58c-.58-1.38-.88-2.86-.88-4.37s.3-2.99.88-4.37c.57-1.34 1.38-2.54 2.41-3.58s2.24-1.86 3.58-2.41c1.38-.58 2.86-.88 4.37-.88s2.99.3 4.37.88c1.34.57 2.54 1.38 3.58 2.41s1.86 2.24 2.41 3.58c.58 1.38.88 2.86.88 4.37s-.3 2.99-.88 4.37Z"/><path fill="#104f79" d="M23.29 14.08c-.94 1.16-2.98 3.75-3.75 5.38-.3.64-.03 1.41.64 1.71.64.3 1.4.02 1.71-.64.75-1.61 1.47-4.85 1.78-6.31.04-.2-.21-.32-.34-.16l-.04.02Z"/><rect fill="#104f79" x="20.17" y="11.12" width=".99" height="1.19"/><rect fill="#104f79" x="14.04" y="13.37" width=".99" height="1.19" transform="rotate(-45 14.54 13.97)"/><rect fill="#104f79" x="11.79" y="19.51" width="1.19" height=".99"/><rect fill="#104f79" x="14.04" y="25.09" width=".99" height="1.19" transform="rotate(-45 14.54 25.69)"/><rect fill="#104f79" x="25.74" y="25.09" width=".99" height="1.19" transform="rotate(45 26.24 25.69)"/><rect fill="#104f79" x="28.35" y="19.51" width="1.19" height=".99"/><rect fill="#104f79" x="25.74" y="13.37" width=".99" height="1.19" transform="rotate(45 26.24 13.97)"/></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-specs__label">Engine Hours</div>
						<?php if ($meta['wpbs_number_of_engines'] > 1 && $meta['engine_hours'] > 0) { 
								$avarage__enging_hours = $meta['engine_hours'] / $meta['wpbs_number_of_engines'];
								// $engine_hours_label = sprintf('%s (Avg: %s)', esc_html($meta['engine_hours'] ?: '—'), esc_html(number_format($avarage__enging_hours, 1)));
								$engine_hours_label = esc_html(number_format($avarage__enging_hours, 0));
							} else {
								$engine_hours_label = esc_html($meta['engine_hours'] ?: '—');
							}
						?>
							
						<div class="wpbs-quick-specs__value"><?php echo esc_html($engine_hours_label); ?></div>
					</div>
				</div>
				
				<!-- Class -->
				<div class="wpbs-quick-specs__item <?php echo esc_attr($item_icon_class); ?>">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path d="m163.73,294.58c-4.25,1.36-8.44,1.48-12.52.34-5.4-1.51-8.73-4.75-8.74-4.76-.94-.95-2.22-1.49-3.56-1.49h-.23c-1.07,0-2.1.34-2.96.97-7.45,5.47-14.8,7.25-21.84,5.28-5.4-1.51-8.74-4.75-8.75-4.76-.94-.95-2.22-1.49-3.56-1.49h-.22c-1.06,0-2.1.34-2.96.97-7.45,5.47-14.8,7.25-21.84,5.28-5.33-1.49-8.65-4.67-8.77-4.78-1.94-1.95-5.09-1.96-7.05-.03-1.96,1.94-1.98,5.1-.05,7.07,6.1,6.18,22.02,14.14,40.47,2.66,7.5,5.37,21.48,9.86,37.34,0,4.26,3.06,10.63,5.83,18.25,5.83,3.64,0,7.57-.63,11.68-2.17l-4.69-8.92Zm-4.01-48.89c-29.54-21.9-49.36-27.74-50.2-27.98-1.65-.47-3.44-.06-4.72,1.09l-20.45,18.45-43.59-4.88c-1.76-.21-3.51.56-4.57,1.99-1.06,1.43-1.28,3.32-.57,4.96,9.52,22.09,39.88,40.75,52.5,47.75.77.43,1.6.63,2.42.63,1.76,0,3.46-.93,4.38-2.58,1.33-2.41.46-5.46-1.95-6.8-7.88-4.37-31.64-18.43-43.51-34.92l111.66,12.51h.01l22.31-6.13.25-1.41-23.97-2.68Zm-62.09-6.95l11.68-10.54c4.57,1.69,15.03,6.08,29.33,15.13l-41.01-4.59Z" /><path d="m473.12,247.44c-.98-1.58-2.74-2.48-4.62-2.34l-88.59,6.64v-7.84l59.81-4.48c1.71-.13,3.23-1.13,4.03-2.64.81-1.51.79-3.33-.06-4.82l-64.44-113.42c-.03-.06-.08-.11-.11-.17-.07-.11-.14-.21-.21-.31-.11-.15-.23-.29-.36-.43-.07-.08-.15-.16-.23-.24-.15-.14-.3-.26-.46-.38-.08-.06-.16-.13-.25-.18-.21-.14-.44-.26-.67-.37-.05-.02-.1-.05-.15-.07-.27-.11-.54-.19-.82-.25-.09-.02-.19-.03-.29-.05-.21-.03-.42-.06-.64-.06-.05,0-.1-.02-.15-.02-.06,0-.12.02-.18.02-.2.01-.4.03-.61.06-.11.02-.23.04-.35.06-.04.01-.09.02-.14.03-.15.04-.28.1-.42.15-.1.04-.21.06-.31.11-.27.12-.54.26-.78.42-.03.02-.04.04-.07.06-.22.15-.43.32-.62.51-.07.07-.13.15-.2.22-.14.15-.26.3-.38.46-.07.09-.13.19-.19.29-.09.16-.18.32-.26.49-.05.1-.1.21-.14.32-.08.19-.13.38-.18.57-.03.1-.06.2-.08.3-.06.31-.09.62-.09.93v14.56l-41.79,69.24c.75,1.79,1.3,3.7,1.65,5.68l1.5,8.46,38.64-64.03v79.7l-35.41,2.66,1.75,9.9,33.66-2.53v7.84l-18.33,1.37,16.92,4.65c1.46.4,2.66,1.44,3.26,2.83.14.33.25.67.32,1.01l87.63-6.57-15.53,26c-1.41,2.38-.62,5.45,1.76,6.85,2.38,1.41,5.45.62,6.85-1.76l20.39-34.25c.95-1.6.93-3.6-.06-5.18Zm-93.21-107.5l51.18,90.09-51.18,3.84v-93.93Zm93.71,150.79c-1.93-1.94-5.09-1.94-7.05-.02-1.18,1.16-11.95,10.96-27.53-.49-.86-.63-1.9-.97-2.96-.97h-.21c-1.33,0-2.62.54-3.56,1.49-.46.46-11.44,11.27-27.49-.52-.86-.63-1.9-.97-2.96-.97h-.22c-1.31,0-2.59.54-3.53,1.46-1.18,1.16-11.94,10.95-27.52-.49-.86-.63-1.9-.97-2.96-.97h-.21c-1.31,0-2.6.54-3.53,1.46-.56.55-3.29,3.06-7.69,4.3l-5.6,10.65c7.16.12,13.17-2.41,17.22-5.26,14.47,8.87,27.24,4.91,34.23,0,6.25,3.84,12.18,5.27,17.47,5.27,6.95,0,12.79-2.48,16.75-5.27,17.05,10.46,31.74,3.09,37.39-2.64,1.93-1.95,1.9-5.08-.04-7.03Z" /><path d="m372.08,262.35c-.07-.34-.18-.68-.32-1.01-.6-1.39-1.8-2.43-3.26-2.83l-31.79-8.73-6.94-39.29c-.35-1.98-.9-3.89-1.65-5.68-4.17-10.23-14.17-17.18-25.6-17.18h-17.56v-4.44c0-7.43-6.04-13.47-13.47-13.47h-22.83c-7.43,0-13.47,6.04-13.47,13.47v4.44h-17.56c-13.45,0-24.91,9.61-27.25,22.86l-6.69,37.88-.25,1.41-22.31,6.13h-.01l-9.47,2.6c-1.46.4-2.66,1.44-3.26,2.83s-.54,2.98.16,4.32l46.84,89.21c.86,1.64,2.56,2.67,4.42,2.67h120.53c1.86,0,3.56-1.03,4.43-2.67l46.83-89.21c.54-1.02.71-2.19.48-3.31Zm-126.89-79.16c0-1.91,1.56-3.47,3.47-3.47h22.83c1.92,0,3.47,1.56,3.47,3.47v4.44h-29.77v-4.44Zm9.89,164.35h-52.25l-42.54-81.03,94.79-26.05v107.08Zm6.12-118.5c-.08-.02-.16-.03-.24-.05-.58-.1-1.18-.1-1.77,0-.08.02-.16.03-.24.05-.07.02-.13.03-.2.04l-64.64,17.76,6.11-34.61c1.5-8.46,8.82-14.6,17.41-14.6h84.89c8.59,0,15.91,6.14,17.41,14.6l6.11,34.61-64.64-17.76c-.06,0-.13-.02-.2-.04Zm56.12,118.5h-52.24v-107.08l94.78,26.05-42.54,81.03Z" /></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-specs__label">Class</div>
						<div class="wpbs-quick-specs__value"><?php echo esc_html($class_label); ?></div>
					</div>
				</div>
				
				<!-- Length -->
				<div class="wpbs-quick-specs__item <?php echo esc_attr($item_icon_class); ?>">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path d="m359.98,396.11h-207.96c-16.54,0-30-13.46-30-30v-110.84c0-16.54,13.46-30,30-30h207.96c16.54,0,30,13.46,30,30v110.84c0,16.54-13.46,30-30,30Zm-207.96-160.84c-11.03,0-20,8.97-20,20v110.84c0,11.03,8.97,20,20,20h207.96c11.03,0,20-8.97,20-20v-110.84c0-11.03-8.97-20-20-20h-207.96Z" /><path d="m159.26,396.11c-2.76,0-5-2.24-5-5v-47.97c0-2.76,2.24-5,5-5s5,2.24,5,5v47.97c0,2.76-2.24,5-5,5Z" /><path d="m191.51,396.11c-2.76,0-5-2.24-5-5v-85.6c0-2.76,2.24-5,5-5s5,2.24,5,5v85.6c0,2.76-2.24,5-5,5Z" /><path d="m223.75,396.11c-2.76,0-5-2.24-5-5v-47.97c0-2.76,2.24-5,5-5s5,2.24,5,5v47.97c0,2.76-2.24,5-5,5Z" /><path d="m256,396.11c-2.76,0-5-2.24-5-5v-85.6c0-2.76,2.24-5,5-5s5,2.24,5,5v85.6c0,2.76-2.24,5-5,5Z" /><path d="m288.25,396.11c-2.76,0-5-2.24-5-5v-47.97c0-2.76,2.24-5,5-5s5,2.24,5,5v47.97c0,2.76-2.24,5-5,5Z" /><path d="m320.49,396.11c-2.76,0-5-2.24-5-5v-85.6c0-2.76,2.24-5,5-5s5,2.24,5,5v85.6c0,2.76-2.24,5-5,5Z" /><path d="m352.74,396.11c-2.76,0-5-2.24-5-5v-47.97c0-2.76,2.24-5,5-5s5,2.24,5,5v47.97c0,2.76-2.24,5-5,5Z" /><path d="m384.98,164H127.02c-2.76,0-5-2.24-5-5s2.24-5,5-5h257.96c2.76,0,5,2.24,5,5s-2.24,5-5,5Z" /><path d="m165.13,202.12c-1.28,0-2.56-.49-3.54-1.46l-38.11-38.11c-.94-.94-1.46-2.21-1.46-3.54s.53-2.6,1.46-3.54l38.11-38.11c1.95-1.95,5.12-1.95,7.07,0,1.95,1.95,1.95,5.12,0,7.07l-34.58,34.58,34.58,34.58c1.95,1.95,1.95,5.12,0,7.07-.98.98-2.26,1.46-3.54,1.46Z" /><path d="m346.87,202.12c-1.28,0-2.56-.49-3.54-1.46-1.95-1.95-1.95-5.12,0-7.07l34.58-34.58-34.58-34.58c-1.95-1.95-1.95-5.12,0-7.07,1.95-1.95,5.12-1.95,7.07,0l38.11,38.11c.94.94,1.46,2.21,1.46,3.54s-.53,2.6-1.46,3.54l-38.11,38.11c-.98.98-2.26,1.46-3.54,1.46Z" /></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-specs__label">Length</div>
						<div class="wpbs-quick-specs__value"><?php echo esc_html($meta['length'] ?: '—'); ?></div>
					</div>
				</div>
				
				<!-- Year -->
				<div class="wpbs-quick-specs__item <?php echo esc_attr($item_icon_class); ?>">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path d="m365.67,387.07h-221.18c-12.68,0-23-10.32-23-23v-203.13c0-12.68,10.32-23,23-23h221.18c12.68,0,23,10.32,23,23v203.13c0,12.68-10.32,23-23,23Zm-221.18-239.13c-7.17,0-13,5.83-13,13v203.13c0,7.17,5.83,13,13,13h221.18c7.17,0,13-5.83,13-13v-203.13c0-7.17-5.83-13-13-13h-221.18Z" /><path d="m383.67,210.53H126.49c-2.76,0-5-2.24-5-5s2.24-5,5-5h257.18c2.76,0,5,2.24,5,5s-2.24,5-5,5Z" /><path d="m152.96,259.33c-1.06-1.99-.13-4.64,1.99-5.71,1.99-.93,4.64-.13,5.71,1.99l14.73,29.46,14.73-29.46c1.06-2.12,3.58-2.92,5.71-1.99,2.12,1.06,3.05,3.72,1.99,5.71l-18.18,36.36v38.75c0,2.52-1.73,4.25-4.25,4.25s-4.25-1.73-4.25-4.25v-38.75l-18.18-36.36Zm62.37-5.57h26.54c2.52,0,4.25,1.72,4.25,4.25s-1.73,4.25-4.25,4.25h-22.29v28.13h19.64c2.52,0,4.25,1.73,4.25,4.25s-1.73,4.25-4.25,4.25h-19.64v30.79h22.29c2.52,0,4.25,1.73,4.25,4.25s-1.73,4.25-4.25,4.25h-26.54c-2.52,0-4.25-1.73-4.25-4.25v-75.91c0-2.52,1.72-4.25,4.25-4.25Zm86.53,84.8c-2.26.66-4.51-.66-5.18-2.92-.4-1.33-.8-2.79-1.19-4.38-.93-2.79-1.86-6.5-3.05-11.15h-25.75c-1.46,4.64-2.12,8.36-3.05,11.15-.4,1.59-.8,3.05-1.2,4.38-.66,2.26-2.79,3.58-5.31,2.92-2.26-.66-3.58-2.92-2.92-5.18l21.23-76.97c.53-1.99,2.12-3.18,4.11-3.18s3.58,1.19,4.11,3.18l21.23,76.97c.66,2.26-.53,4.51-3.05,5.18Zm-22.29-65.16c-2.79,9.56-6.9,25.08-10.48,38.22h20.97c-3.58-13.14-7.96-28.66-10.48-38.22Zm65.56,26.67l12.21,32.91c.93,2.12-.27,4.51-2.52,5.44-2.12.93-4.51-.27-5.44-2.52l-12.74-34.37h-10.88v32.91c0,2.52-1.73,4.25-4.25,4.25-2.26,0-4.25-1.73-4.25-4.25v-76.44c0-2.52,1.99-4.25,4.25-4.25h15.92c11.15,0,20.17,9.02,20.17,20.17v7.43c0,8.49-5.04,15.66-12.47,18.71Zm3.98-18.71v-7.43c0-6.37-5.17-11.68-11.68-11.68h-11.68v30.79h11.68c6.5,0,11.68-5.18,11.68-11.68Z" /><path d="m314.47,174.57c-2.76,0-5-2.24-5-5v-53.01c0-2.76,2.24-5,5-5s5,2.24,5,5v53.01c0,2.76-2.24,5-5,5Zm-118.78,0c-2.76,0-5-2.24-5-5v-53.01c0-2.76,2.24-5,5-5s5,2.24,5,5v53.01c0,2.76-2.24,5-5,5Z" /></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-specs__label">Year</div>
						<div class="wpbs-quick-specs__value"><?php echo esc_html($meta['year'] ?: '—'); ?></div>
					</div>
				</div>
				
				<!-- Model -->
				<div class="wpbs-quick-specs__item <?php echo esc_attr($item_icon_class); ?>">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path d="m430.37,331.25c-23.1,0-34.69,6.55-46.03,12.85-10.58,5.96-20.66,11.51-41.16,11.51-8.65,0-16.21-1.01-22.93-3.11-7.06-2.18-12.51-5.21-18.39-8.48-4.2-2.35-8.48-4.79-13.44-6.8-9.74-4.03-19.99-5.96-32.42-5.96s-22.76,1.93-32.42,5.96c-5.12,2.1-9.41,4.54-13.61,6.89-10.67,5.96-20.66,11.51-41.16,11.51s-30.57-5.54-41.24-11.51c-4.2-2.35-8.57-4.79-13.61-6.89-7.73-3.28-15.62-5.04-24.86-5.71-2.35-.17-4.96-.25-7.48-.25-2.77,0-5.04,2.27-5.04,4.96s2.27,4.96,5.04,4.96c2.35,0,4.62.08,6.8.25,8.06.59,15.03,2.1,21.75,4.96,4.45,1.85,8.31,4.03,12.6,6.38,11.25,6.3,22.93,12.85,46.03,12.85s34.69-6.55,45.94-12.85c4.2-2.35,8.06-4.54,12.6-6.38,8.57-3.61,17.39-5.21,28.64-5.21s19.99,1.6,28.56,5.21c4.62,1.93,8.65,4.2,12.6,6.38,5.96,3.36,12.09,6.8,20.16,9.32,7.73,2.35,16.21,3.53,25.87,3.53,23.1,0,34.77-6.55,46.03-12.85,10.67-5.96,20.66-11.59,41.16-11.59,2.77,0,4.96-2.18,4.96-4.96s-2.18-4.96-4.96-4.96Z" /><path d="m430.37,296.15c-23.1,0-34.77,6.47-46.03,12.77-2.52,1.43-4.96,2.77-7.56,4.12l56.11-67.86c2.69-3.28,3.28-7.64,1.43-11.51-1.76-3.78-5.54-6.13-9.74-6.13h-52.24l-31.41-22.88s-.04-.03-.06-.05l-31.02-22.6c-.57-.42-1.24-.69-1.93-.84-.17-.92-.33-1.76-.59-2.59-.17-.84-.42-1.6-.76-2.44-.25-.84-.59-1.68-1.01-2.52v-.08c-.59-1.26-1.26-2.52-2.02-3.7-.76-1.18-1.6-2.35-2.52-3.36-.92-1.09-1.93-2.1-2.94-3.02-1.51-1.43-3.19-2.6-4.96-3.53-.59-.42-1.26-.76-1.85-1.01-1.34-.67-2.77-1.26-4.28-1.68-.59-.25-1.18-.42-1.85-.5-.5-.17-1.09-.25-1.68-.34-1.68-.34-3.44-.5-5.21-.5h-62.57l-6.55-14.03h32c2.77,0,4.96-2.18,4.96-4.96s-2.18-4.96-4.96-4.96h-65.68c-2.77,0-5.04,2.18-5.04,4.96s2.27,4.96,5.04,4.96h22.68l6.64,14.03h-27.72c-9.66,0-17.55,7.81-17.55,17.55s7.9,17.55,17.55,17.55h9.91l-17.97,36.54h-7.22c-1.6,0-3.11.76-4.03,2.1l-15.12,21.5h-15.37c-1.6,0-3.11.84-4.12,2.1l-30.49,43.51c-3.44-.42-7.14-.59-11-.59-2.77,0-5.04,2.18-5.04,4.96s2.27,4.96,5.04,4.96c3.78,0,7.31.17,10.67.59.17,0,.34,0,.5.08.59,0,1.09.08,1.6.17,4.79.67,9.24,1.85,13.52,3.44.17,0,.42.08.59.17,5.21,2.02,9.57,4.45,14.19,7.05,11.34,6.3,23.01,12.85,46.11,12.85s34.69-6.55,45.94-12.85c10.67-5.88,20.66-11.51,41.24-11.51s30.49,5.63,41.16,11.51c11.25,6.3,22.93,12.85,46.03,12.85,7.14,0,13.52-.59,19.4-1.85,11.34-2.44,19.07-6.72,26.62-11,10.58-5.88,20.66-11.51,41.16-11.51,2.77,0,4.96-2.27,4.96-4.96s-2.18-4.96-4.96-4.96Zm-184.24-105.08l45.49.07,13.98.05,8.17,5.95,8.93,6.51h-68.77c-4.3,0-7.79-3.49-7.79-7.78v-4.79Zm-69.07-9.99c-4.2,0-7.64-3.44-7.64-7.64s3.44-7.64,7.64-7.64h101.21c8.65,0,16.38,5.54,19.15,13.77l.49,1.47h-56.74c-.2,0-.4.02-.6.04h-63.51Zm20.91,9.91l38.23.06v4.81c0,9.77,7.95,17.72,17.71,17.72h82.4l19.14,13.95h-12.78c-.18-.02-.37-.03-.56-.03s-.38.01-.56.03h-22.78c-.18-.02-.37-.03-.56-.03s-.38.01-.56.03h-137.57l17.89-36.54Zm-33.6,46.45h119.69v5.96c0,4.28-3.53,7.73-7.81,7.73h-121.53l9.66-13.69Zm191.92,82.14c-1.09.17-2.27.34-3.53.42-3.02.34-6.22.5-9.57.5-20.49,0-30.57-5.63-41.15-11.59-11.25-6.3-22.93-12.77-46.03-12.77s-34.77,6.47-46.03,12.77c-10.67,5.96-20.66,11.59-41.16,11.59s-30.57-5.63-41.24-11.59c-4.7-2.69-9.66-5.38-15.54-7.64-.25-.08-.59-.25-.84-.25-2.52-1.01-5.04-1.85-7.73-2.44l26.37-37.54h146.39c9.74,0,17.72-7.9,17.72-17.64v-5.96h19.27v18.67c0,2.74,2.23,4.97,4.97,4.97s4.97-2.23,4.97-4.97v-18.67h13.98v18.67c0,2.74,2.22,4.97,4.96,4.97s4.97-2.23,4.97-4.97v-18.67h13.65v18.67c0,2.74,2.23,4.97,4.97,4.97s4.97-2.23,4.97-4.97v-18.67h55.69l-67.86,81.72c-.67.17-1.43.34-2.18.42Z" /></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-specs__label">Model</div>
						<div class="wpbs-quick-specs__value"><?php echo esc_html($model_label); ?></div>
					</div>
				</div>
				
				<!-- Capacity -->
				<div class="wpbs-quick-specs__item <?php echo esc_attr($item_icon_class); ?>">
					<svg class="wpbs-quick-specs__icon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="19.5" fill="none" stroke="#e0e0e0"/><path fill="#104f79" d="M18.85 11.93c-1.42.39-2.53 1.56-2.9 3.05-.32 1.3.07 2.77.99 3.77l.39.42-.44.16-.44.15-.34-.34c-.21-.2-.55-.44-.86-.6l-.52-.26.22-.27c1.04-1.26 1-3.04-.09-4.27-.74-.83-1.94-1.21-2.97-.92-1.06.29-2 1.29-2.24 2.36-.08.36-.08 1.12 0 1.48.09.41.38.98.67 1.33l.26.31-.34.14c-1.08.46-1.93 1.5-2.16 2.68-.11.51-.13 2.48-.02 2.67.18.34 2.55 1.21 3.32 1.21.26 0 .45-.21.45-.5 0-.31-.13-.4-.77-.54-.69-.15-1.27-.34-1.76-.57l-.37-.18.02-.96c.02-.82.05-1.03.16-1.32.29-.77.88-1.37 1.63-1.66.32-.12.5-.14 1.67-.16.84-.01 1.45.01 1.71.06.45.08 1 .35 1.29.62l.19.18-.44.45c-.81.83-1.28 1.84-1.4 3.02-.04.33-.06 1.11-.05 1.72.02 1.32-.04 1.21.84 1.63 3.35 1.61 7.07 1.61 10.39 0 .91-.44.85-.32.87-1.63.01-.61-.01-1.39-.05-1.72-.12-1.18-.59-2.19-1.4-3.02l-.44-.45.2-.18c.31-.28.86-.55 1.3-.62.25-.05.87-.06 1.71-.06 1.17.01 1.35.03 1.67.16.75.29 1.34.89 1.63 1.66.11.29.14.5.16 1.33l.02.96-.22.12c-.4.23-1.21.48-1.85.62-.69.15-.82.23-.82.54 0 .29.19.5.48.5.28 0 1.11-.2 1.68-.4.67-.23 1.53-.67 1.61-.81.1-.19.08-2.16-.02-2.67-.24-1.16-1.09-2.22-2.16-2.68l-.34-.14.26-.31c.48-.58.74-1.28.74-2.07-.01-.92-.27-1.57-.9-2.23-.64-.67-1.32-.86-2.21-.86-1.64.01-3.03 1.48-3.03 3.2 0 .74.28 1.5.76 2.09l.22.27-.52.26c-.32.16-.65.4-.86.6l-.34.34-.45-.16-.45-.16.21-.17c.52-.45 1.08-1.45 1.24-2.23.36-1.8-.53-3.67-2.17-4.5-.82-.42-1.8-.53-2.65-.32Zm1.53.93c1.11.24 2.11 1.28 2.34 2.44.21 1.04-.08 2.03-.86 2.81-1.22 1.27-2.98 1.27-4.20 0s-1.22-2.54 0-3.81c.76-.79 1.67-1.07 2.72-.85ZM13.5 13.88c.38.18.89.69 1.08 1.11.15.3.16.42.16.93 0 .52-.02.65-.17.98-.21.42-.7.92-1.08 1.11-.23.11-.39.13-.84.13-.49 0-.64-.02-.94-.15-1.24-.63-1.61-2.24-.84-3.38.21-.29.61-.58 1-.73.34-.13 1.15-.13 1.50 0Zm14.3.03c.45.23.84.64 1.03 1.07.14.3.16.43.16.93 0 .52-.02.65-.17.93-.22.45-.61.86-1.03 1.11-.28.15-.39.17-.88.17-.45 0-.61-.03-.84-.13-.39-.19-.87-.62-1.08-1.04-.15-.3-.17-.43-.17-.93 0-.70.11-1.01.52-1.47.47-.57.85-.72 1.52-.69.37.01.56.04.81.15Zm-5.87 6.21c1.34.29 2.49 1.44 2.83 2.82.13.54.18 2.74.07 2.83-.17.14-1.29.59-1.94.78-1.11.33-1.82.43-3.09.43s-1.98-.1-3.09-.43c-.65-.2-1.77-.65-1.94-.78-.11-.09-.06-2.29.07-2.83.34-1.37 1.48-2.51 2.86-2.82.56-.13 2.69-.13 3.23 0Z"/></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-specs__label">Capacity</div>
						<div class="wpbs-quick-specs__value"><?php echo esc_html($capacity ?: '—'); ?></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
