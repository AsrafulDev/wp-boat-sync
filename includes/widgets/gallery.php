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

		$this->add_control(
			'show_condition_badge',
			[
				'label' => esc_html__('Show Condition Badge', 'wp-boat-sync'),
				'description' => esc_html__('Display a New/Used/Sold label on the main image.', 'wp-boat-sync'),
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
				'size_units' => ['px', 'vh', '%'],
				'range' => [
					'px' => ['min' => 200, 'max' => 800],
					'vh' => ['min' => 20, 'max' => 100],
                    '%' => ['min' => 20, 'max' => 100],
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

		$this->end_controls_section();		// Condition Badge Style Section
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
					'{{WRAPPER}} .wpbs-gallery__condition-badge--new' => 'background-color: {{VALUE}} !important;',
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
					'{{WRAPPER}} .wpbs-gallery__condition-badge--used' => 'background-color: {{VALUE}} !important;',
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
					'{{WRAPPER}} .wpbs-gallery__condition-badge--sold' => 'background-color: {{VALUE}} !important;',
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
					'{{WRAPPER}} .wpbs-gallery__condition-badge' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'badge_typography',
				'label' => esc_html__('Typography', 'wp-boat-sync'),
				'selector' => '{{WRAPPER}} .wpbs-gallery__condition-badge',
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
					'{{WRAPPER}} .wpbs-gallery__condition-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
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
					'{{WRAPPER}} .wpbs-gallery__condition-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
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
					'{{WRAPPER}} .wpbs-gallery__condition-badge' => 'left: auto; right: auto; {{VALUE}}: 8px !important;',
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
					'{{WRAPPER}} .wpbs-gallery__condition-badge' => 'top: {{SIZE}}{{UNIT}} !important;',
				],
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
		
		// Get boat ID using standardized pattern
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
		
		// Build gallery from attachment IDs (matching template pattern)
		$gallery_ids = get_post_meta($final_id, 'wpbs_gallery_attachment_ids', true);
		if (!is_array($gallery_ids)) {
			$gallery_ids = array();
		}
		$featured_id = (int)get_post_thumbnail_id($final_id);
		if ($featured_id) {
			array_unshift($gallery_ids, $featured_id);
		}
		$gallery_ids = array_values(array_unique(array_filter(array_map('intval', $gallery_ids))));
		
		// Get video URLs (matching template pattern)
		$video_urls_raw = get_post_meta($final_id, 'wpbs_embedded_video_urls', true);
		$video_urls = array();
		if ($video_urls_raw) {
			$lines = array_map('trim', explode("\n", $video_urls_raw));
			foreach ($lines as $line) {
				// Strip any trailing pipe and anything after it (malformed save)
				$clean = preg_replace('/\|.*$/', '', $line);
				$clean = trim($clean);
				if ($clean !== '') {
					$video_urls[] = $clean;
				}
			}
		}
		
		$total_images = count($gallery_ids);
		$has_videos = !empty($video_urls);
		$total_media = $total_images + count($video_urls);
		
		$main_large = null;
		if ($total_images > 0) {
			$main_id = (int)$gallery_ids[0];
			$main_large = wp_get_attachment_image_url($main_id, 'large');
		}
		
		$lightbox_enabled = $settings['lightbox'] === 'yes';
		$show_badge = $settings['show_condition_badge'] === 'yes';
		
		// Get condition and status for badge
		$gallery_badge_label = '';
		$gallery_badge_slug = '';
		if ($show_badge && $final_id) {
			$sales_status = get_post_meta($final_id, 'wpbs_sales_status', true);
			$condition = get_post_meta($final_id, 'wpbs_condition', true);
			$is_sold = $sales_status && strtolower((string)$sales_status) !== 'active';
			
			if ($is_sold) {
				$gallery_badge_label = 'Sold';
				$gallery_badge_slug = 'sold';
			} elseif ($condition && strtolower($condition) === 'new') {
				$gallery_badge_label = 'New';
				$gallery_badge_slug = 'new';
			} elseif ($condition && strtolower($condition) === 'used') {
				$gallery_badge_label = 'Used';
				$gallery_badge_slug = 'used';
			}
		}
		
		?>
		<div class="wpbs-gallery" data-wpbs-gallery>
			<div class="wpbs-gallery__main" data-wpbs-lightbox-trigger>
				<?php if ($gallery_badge_label) : ?>
					<div class="wpbs-gallery__condition-badge wpbs-gallery__condition-badge--<?php echo esc_attr($gallery_badge_slug); ?>"><?php echo esc_html($gallery_badge_label); ?></div>
				<?php endif; ?>
				
				<?php if ($main_large): ?>
					<div class="wpbs-gallery__main-link" id="wpbs-main-link" data-index="0">
						<img id="wpbs-main-img" class="wpbs-gallery__main-img" src="<?php echo esc_url($main_large); ?>" alt="<?php echo esc_attr(get_the_title($final_id)); ?>">
					</div>
				<?php else: ?>
					<img class="wpbs-gallery__main-img" src="<?php echo esc_url(plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/images/boat-placeholder.png'); ?>" alt="<?php echo esc_attr(get_the_title($final_id)); ?>">
				<?php endif; ?>

				<?php if ($total_media > 1): ?>
					<button type="button" class="wpbs-gallery__nav wpbs-gallery__nav--prev" aria-label="Previous" data-wpbs-nav="prev">
						<svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
					</button>
					<button type="button" class="wpbs-gallery__nav wpbs-gallery__nav--next" aria-label="Next" data-wpbs-nav="next">
						<svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
					</button>
				<?php endif; ?>

				<?php if ($total_media > 0): ?>
					<button type="button" class="wpbs-gallery__view-btn" data-wpbs-open-lightbox>
						<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
						View <?php echo $total_images; ?> Photos<?php echo $has_videos ? ' & Video' : ''; ?>
					</button>
				<?php endif; ?>
			</div>

			<?php if ($total_media > 1): ?>
				<div class="wpbs-gallery__thumbs" role="list">
					<?php foreach (array_slice($gallery_ids, 0, 20) as $i => $aid):
						$aid   = (int)$aid;
						$thumb = wp_get_attachment_image_url($aid, 'thumbnail');
						$large = wp_get_attachment_image_url($aid, 'large');
						$full  = wp_get_attachment_image_url($aid, 'full');
						if (!$thumb || !$large) continue;
					?>
						<button type="button" class="wpbs-gallery__thumb<?php echo $i === 0 ? ' is-active' : ''; ?>" data-index="<?php echo $i; ?>" data-type="image" data-large="<?php echo esc_url($large); ?>" data-full="<?php echo esc_url($full ?: $large); ?>">
							<img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy">
						</button>
					<?php endforeach; ?>
					<?php 
					// Add video thumbnails
					$video_index = count($gallery_ids);
					foreach ($video_urls as $vurl):
						// Try to extract YouTube/Vimeo thumbnail
						$video_thumb = '';
						$vclean = preg_replace('/\|.*$/', '', trim($vurl));
						if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $vclean, $m) || preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $vclean, $m)) {
							$video_thumb = 'https://img.youtube.com/vi/' . $m[1] . '/mqdefault.jpg';
						} elseif (preg_match('/vimeo\.com\/(\d+)/', $vclean, $m)) {
							$video_thumb = ''; // Vimeo requires API call
						}
					?>
						<button type="button" class="wpbs-gallery__thumb wpbs-gallery__thumb--video" data-index="<?php echo $video_index; ?>" data-type="video" data-video-url="<?php echo esc_url($vclean); ?>">
							<?php if ($video_thumb): ?>
								<img src="<?php echo esc_url($video_thumb); ?>" alt="Video" loading="lazy">
							<?php endif; ?>
							<span class="wpbs-gallery__thumb-play">
								<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
							</span>
						</button>
					<?php $video_index++; endforeach; ?>
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
