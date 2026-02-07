<?php
/**
 * WPBS Elementor Widget - Boat Overview
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Elementor_Overview_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'wpbs_overview';
	}

	public function get_title()
	{
		return esc_html__('Boat Overview', 'wp-boat-sync');
	}

	public function get_icon()
	{
		return 'eicon-text-area';
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
				'label' => esc_html__('Overview Settings', 'wp-boat-sync'),
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
					'{{WRAPPER}} .wpbs-overview' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .wpbs-overview',
				'fields_options' => [
					'border' => ['selectors' => ['{{WRAPPER}} .wpbs-overview' => 'border-style: {{VALUE}} !important;']],
					'width' => ['selectors' => ['{{WRAPPER}} .wpbs-overview' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']],
					'color' => ['selectors' => ['{{WRAPPER}} .wpbs-overview' => 'border-color: {{VALUE}} !important;']],
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
					'{{WRAPPER}} .wpbs-overview' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_shadow',
				'selector' => '{{WRAPPER}} .wpbs-overview',
				'fields_options' => [
					'box_shadow' => ['selectors' => ['{{WRAPPER}} .wpbs-overview' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}} !important;']],
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
					'{{WRAPPER}} .wpbs-overview' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Title Style
		$this->start_controls_section(
			'title_style',
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
					'{{WRAPPER}} .wpbs-overview__title' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .wpbs-overview__title',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__title' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__title' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__title' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__title' => 'font-family: {{VALUE}} !important;']],
				],
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-overview__title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
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
			'content_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-overview__content' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'selector' => '{{WRAPPER}} .wpbs-overview__content',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content' => 'font-family: {{VALUE}} !important;']],
				],
			]
		);

		$this->add_control(
			'link_color',
			[
				'label' => esc_html__('Link Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .wpbs-overview__content a' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'link_hover_color',
			[
				'label' => esc_html__('Link Hover Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-overview__content a:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Heading Style (h2, h3, etc. within content)
		$this->start_controls_section(
			'heading_style',
			[
				'label' => esc_html__('Content Headings', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-overview__content h1' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-overview__content h2' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-overview__content h3' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-overview__content h4' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-overview__content h5' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-overview__content h6' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'heading_typography',
				'selector' => '{{WRAPPER}} .wpbs-overview__content h1, {{WRAPPER}} .wpbs-overview__content h2, {{WRAPPER}} .wpbs-overview__content h3, {{WRAPPER}} .wpbs-overview__content h4, {{WRAPPER}} .wpbs-overview__content h5, {{WRAPPER}} .wpbs-overview__content h6',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content h1, {{WRAPPER}} .wpbs-overview__content h2, {{WRAPPER}} .wpbs-overview__content h3, {{WRAPPER}} .wpbs-overview__content h4, {{WRAPPER}} .wpbs-overview__content h5, {{WRAPPER}} .wpbs-overview__content h6' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content h1, {{WRAPPER}} .wpbs-overview__content h2, {{WRAPPER}} .wpbs-overview__content h3, {{WRAPPER}} .wpbs-overview__content h4, {{WRAPPER}} .wpbs-overview__content h5, {{WRAPPER}} .wpbs-overview__content h6' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content h1, {{WRAPPER}} .wpbs-overview__content h2, {{WRAPPER}} .wpbs-overview__content h3, {{WRAPPER}} .wpbs-overview__content h4, {{WRAPPER}} .wpbs-overview__content h5, {{WRAPPER}} .wpbs-overview__content h6' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content h1, {{WRAPPER}} .wpbs-overview__content h2, {{WRAPPER}} .wpbs-overview__content h3, {{WRAPPER}} .wpbs-overview__content h4, {{WRAPPER}} .wpbs-overview__content h5, {{WRAPPER}} .wpbs-overview__content h6' => 'font-family: {{VALUE}} !important;']],
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
					'{{WRAPPER}} .wpbs-overview__content h1' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpbs-overview__content h2' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpbs-overview__content h3' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpbs-overview__content h4' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpbs-overview__content h5' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpbs-overview__content h6' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// List Style
		$this->start_controls_section(
			'list_style',
			[
				'label' => esc_html__('Lists', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'list_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-overview__content ul' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-overview__content ol' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .wpbs-overview__content li' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'list_typography',
				'selector' => '{{WRAPPER}} .wpbs-overview__content ul, {{WRAPPER}} .wpbs-overview__content ol, {{WRAPPER}} .wpbs-overview__content li',
				'fields_options' => [
					'typography' => ['default' => 'custom'],
					'font_size' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content ul, {{WRAPPER}} .wpbs-overview__content ol, {{WRAPPER}} .wpbs-overview__content li' => 'font-size: {{SIZE}}{{UNIT}} !important;']],
					'font_weight' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content ul, {{WRAPPER}} .wpbs-overview__content ol, {{WRAPPER}} .wpbs-overview__content li' => 'font-weight: {{VALUE}} !important;']],
					'line_height' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content ul, {{WRAPPER}} .wpbs-overview__content ol, {{WRAPPER}} .wpbs-overview__content li' => 'line-height: {{SIZE}}{{UNIT}} !important;']],
					'font_family' => ['selectors' => ['{{WRAPPER}} .wpbs-overview__content ul, {{WRAPPER}} .wpbs-overview__content ol, {{WRAPPER}} .wpbs-overview__content li' => 'font-family: {{VALUE}} !important;']],
				],
			]
		);

		$this->add_responsive_control(
			'list_spacing',
			[
				'label' => esc_html__('Item Spacing', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => ['px' => ['min' => 0, 'max' => 30]],
				'selectors' => [
					'{{WRAPPER}} .wpbs-overview__content li' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();
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
		} else {
			$final_id = get_the_ID();
		}
		
		if (!$final_id) {
			return;
		}
		
		$post = get_post($final_id);
		$status = get_post_meta($final_id, 'wpbs_status', true);
		$is_sold = $status && strtolower($status) !== 'active';
		$content = $post->post_content;
		$content = apply_filters('the_content', $content);
		
		?>
		<div class="wpbs-overview">
			<h2 class="wpbs-overview__title">Boat Overview
				<?php if ($is_sold): ?>
					<span class="wpbs-badge wpbs-badge--sold">Sold</span>
				<?php endif; ?>
			</h2>
			<div class="wpbs-overview__content"><?php echo $content; ?></div>
		</div>
		<?php
	}
}
