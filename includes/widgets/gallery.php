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
		
		// Get gallery images
		$gallery_meta = get_post_meta($final_id, 'wpbs_gallery', true);
		$gallery_ids = array();
		
		if ($gallery_meta) {
			$ids = explode(',', $gallery_meta);
			foreach ($ids as $id) {
				$id = trim($id);
				if ($id && wp_attachment_is_image($id)) {
					$gallery_ids[] = (int)$id;
				}
			}
		}
		
		if (empty($gallery_ids) && has_post_thumbnail($final_id)) {
			$gallery_ids[] = get_post_thumbnail_id($final_id);
		}
		
		$total = count($gallery_ids);
		if ($total === 0) {
			return;
		}
		
		$main_id = (int)$gallery_ids[0];
		$main_large = wp_get_attachment_image_url($main_id, 'large');
		$lightbox_enabled = $settings['lightbox'] === 'yes';
		
		?>
		<div class="wpbs-gallery" data-wpbs-gallery>
			<div class="wpbs-gallery__main" data-wpbs-lightbox-trigger>
				<div class="wpbs-gallery__main-link" id="wpbs-main-link" data-index="0">
					<img id="wpbs-main-img" class="wpbs-gallery__main-img" src="<?php echo esc_url($main_large); ?>" alt="<?php echo esc_attr(get_the_title($final_id)); ?>">
				</div>
				
				<?php if ($total > 1): ?>
					<button type="button" class="wpbs-gallery__nav wpbs-gallery__nav--prev" aria-label="Previous" data-wpbs-nav="prev">
						<svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
					</button>
					<button type="button" class="wpbs-gallery__nav wpbs-gallery__nav--next" aria-label="Next" data-wpbs-nav="next">
						<svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
					</button>
				<?php endif; ?>
				
				<button type="button" class="wpbs-gallery__view-btn" data-wpbs-open-lightbox>
					<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
					View <?php echo $total; ?> Photos
				</button>
			</div>
			
			<?php if ($total > 1): ?>
				<div class="wpbs-gallery__thumbs" role="list">
					<?php foreach (array_slice($gallery_ids, 0, 20) as $i => $aid): 
						$thumb = wp_get_attachment_image_url($aid, 'thumbnail');
						$large = wp_get_attachment_image_url($aid, 'large');
						$full = wp_get_attachment_image_url($aid, 'full');
						$active = $i === 0 ? ' is-active' : '';
					?>
						<button type="button" class="wpbs-gallery__thumb<?php echo $active; ?>" data-index="<?php echo $i; ?>" data-type="image" data-large="<?php echo esc_url($large); ?>" data-full="<?php echo esc_url($full); ?>">
							<img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy">
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		
		<?php if ($lightbox_enabled): ?>
			<div class="wpbs-lightbox" id="wpbs-lightbox">
				<div class="wpbs-lightbox__overlay"></div>
				<div class="wpbs-lightbox__container">
					<button type="button" class="wpbs-lightbox__close" aria-label="Close">
						<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
					</button>
					<button type="button" class="wpbs-lightbox__nav wpbs-lightbox__nav--prev" aria-label="Previous">
						<svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
					</button>
					<button type="button" class="wpbs-lightbox__nav wpbs-lightbox__nav--next" aria-label="Next">
						<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
					</button>
					<div class="wpbs-lightbox__content">
						<img class="wpbs-lightbox__main-img" src="" alt="">
					</div>
					<div class="wpbs-lightbox__counter"></div>
				</div>
			</div>
		<?php endif; ?>
		<?php
	}
}
