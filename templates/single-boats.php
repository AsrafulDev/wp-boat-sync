<?php
/**
 * Single template for WPBS boats – BoatTrader.com exact match
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

while (have_posts()) : the_post();
	$post_id    = get_the_ID();
	$price      = get_post_meta($post_id, 'wpbs_price', true);
	$length     = get_post_meta($post_id, 'wpbs_length_overall', true);
	$year       = get_post_meta($post_id, 'wpbs_model_year', true);
	$make       = get_post_meta($post_id, 'wpbs_make', true);
	$model      = get_post_meta($post_id, 'wpbs_model', true);
	$engine     = get_post_meta($post_id, 'wpbs_engine_summary', true);
	$location   = get_post_meta($post_id, 'wpbs_location', true);
	$status     = get_post_meta($post_id, 'wpbs_sales_status', true);
	$hull_type  = get_post_meta($post_id, 'wpbs_hull_material', true);
	$fuel_type  = get_post_meta($post_id, 'wpbs_fuel_type', true);
	$condition  = get_post_meta($post_id, 'wpbs_condition', true);
	$total_power = get_post_meta($post_id, 'wpbs_total_engine_power', true);
	$engine_hrs = get_post_meta($post_id, 'wpbs_total_engine_hours', true);
	$num_engines = get_post_meta($post_id, 'wpbs_number_of_engines', true);
	$dealer     = get_post_meta($post_id, 'wpbs_dealer_name', true);
	$beam       = get_post_meta($post_id, 'wpbs_beam', true);
	$dry_weight = get_post_meta($post_id, 'wpbs_dry_weight', true);
	$cabins     = get_post_meta($post_id, 'wpbs_cabins_count', true);
	$heads      = get_post_meta($post_id, 'wpbs_heads_count', true);
	$engine_type = get_post_meta($post_id, 'wpbs_engine_type', true);
	$engine_make = get_post_meta($post_id, 'wpbs_engine_make', true);
	$engine_model = get_post_meta($post_id, 'wpbs_engine_model', true);
	$propeller  = get_post_meta($post_id, 'wpbs_propeller_type', true);
	$drive_type = get_post_meta($post_id, 'wpbs_drive_type', true);
	$boat_category = get_post_meta($post_id, 'wpbs_boat_category', true);
	$boat_class = get_post_meta($post_id, 'wpbs_boat_class_codes', true);
	$hull_id    = get_post_meta($post_id, 'wpbs_hull_id', true);
	$cruising_speed = get_post_meta($post_id, 'wpbs_cruising_speed', true);
	$max_speed  = get_post_meta($post_id, 'wpbs_max_speed', true);
	$fuel_capacity = get_post_meta($post_id, 'wpbs_fuel_tank_capacity', true);
	$water_capacity = get_post_meta($post_id, 'wpbs_water_tank_capacity', true);
	$office_phone = get_post_meta($post_id, 'wpbs_office_phone', true);
	$office_email = get_post_meta($post_id, 'wpbs_office_email', true);
	$is_sold    = $status && strtolower((string)$status) !== 'active';

	// Build gallery from attachment IDs
	$gallery_ids = get_post_meta($post_id, 'wpbs_gallery_attachment_ids', true);
	if (!is_array($gallery_ids)) {
		$gallery_ids = array();
	}
	$featured_id = (int)get_post_thumbnail_id($post_id);
	if ($featured_id) {
		array_unshift($gallery_ids, $featured_id);
	}
	$gallery_ids  = array_values(array_unique(array_filter(array_map('intval', $gallery_ids))));
	$total_images = count($gallery_ids);

	// Get embedded videos
	$video_urls_raw = get_post_meta($post_id, 'wpbs_embedded_video_urls', true);
	$video_urls = array();
	if ($video_urls_raw) {
		$video_urls = array_filter(array_map('trim', explode("\n", $video_urls_raw)));
	}
	$has_videos = !empty($video_urls);
	$total_media = $total_images + count($video_urls);

	$main_id    = !empty($gallery_ids) ? (int)$gallery_ids[0] : 0;
	$main_large = $main_id ? wp_get_attachment_image_url($main_id, 'large') : '';
	$main_full  = $main_id ? wp_get_attachment_image_url($main_id, 'full') : '';

	// Build gallery items array for JS lightbox (images + videos)
	$gallery_items = array();
	foreach ($gallery_ids as $idx => $aid) {
		$gallery_items[] = array(
			'type' => 'image',
			'thumb' => wp_get_attachment_image_url($aid, 'thumbnail'),
			'large' => wp_get_attachment_image_url($aid, 'large'),
			'full' => wp_get_attachment_image_url($aid, 'full'),
		);
	}
	foreach ($video_urls as $vurl) {
		$gallery_items[] = array(
			'type' => 'video',
			'url' => $vurl,
			'thumb' => '', // Video thumbnail placeholder
		);
	}

	// Price formatting
	$price_display = '';
	$price_num = 0;
	if ($price) {
		$price_num = (float)preg_replace('/[^0-9.]/', '', $price);
		$price_display = '$' . number_format($price_num);
	}

	// Monthly estimate (rough 15yr 7% calculation)
	$monthly = '';
	if ($price_num > 5000) {
		$monthly = '$' . number_format(round($price_num * 0.009), 0) . '/mo*';
	}
?>

<div class="wpbs-wrap">
	<!-- Breadcrumb -->
	<div style="font-size:12px;color:#666;margin-bottom:12px;">
		<a href="<?php echo esc_url(home_url('/')); ?>" style="color:#0066cc;text-decoration:none;">Home</a> &rsaquo;
		<a href="<?php echo esc_url(get_post_type_archive_link('boats')); ?>" style="color:#0066cc;text-decoration:none;">Boats for Sale</a> &rsaquo;
		<span><?php the_title(); ?></span>
	</div>

	<div class="wpbs-single-wrap">
		<!-- Main Content Column -->
		<div class="wpbs-single-main">
			<!-- Gallery -->
			<div class="wpbs-gallery" data-wpbs-gallery>
				<div class="wpbs-gallery__main" data-wpbs-lightbox-trigger>
					<?php if ($main_large) : ?>
					<div class="wpbs-gallery__main-link" id="wpbs-main-link" data-index="0">
						<img id="wpbs-main-img" class="wpbs-gallery__main-img" src="<?php echo esc_url($main_large); ?>" alt="<?php the_title_attribute(); ?>">
					</div>
					<?php else : ?>
					<img class="wpbs-gallery__main-img" src="<?php echo esc_url(plugin_dir_url(WPBS_PLUGIN_FILE) . 'assets/images/boat-placeholder.png'); ?>" alt="<?php the_title_attribute(); ?>">
					<?php endif; ?>

					<?php if ($total_media > 1) : ?>
					<button type="button" class="wpbs-gallery__nav wpbs-gallery__nav--prev" aria-label="Previous" data-wpbs-nav="prev">
						<svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
					</button>
					<button type="button" class="wpbs-gallery__nav wpbs-gallery__nav--next" aria-label="Next" data-wpbs-nav="next">
						<svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
					</button>
					<?php endif; ?>

					<?php if ($total_media > 0) : ?>
					<button type="button" class="wpbs-gallery__view-btn" data-wpbs-open-lightbox>
						<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
						View <?php echo $total_images; ?> Photos<?php echo $has_videos ? ' & Video' : ''; ?>
					</button>
					<?php endif; ?>
				</div>

				<?php if ($total_media > 1) : ?>
				<div class="wpbs-gallery__thumbs" role="list">
					<?php foreach (array_slice($gallery_ids, 0, 20) as $i => $aid) :
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
					foreach ($video_urls as $vurl) :
						// Try to extract YouTube/Vimeo thumbnail
						$video_thumb = '';
						if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $vurl, $m) || preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $vurl, $m)) {
							$video_thumb = 'https://img.youtube.com/vi/' . $m[1] . '/mqdefault.jpg';
						} elseif (preg_match('/vimeo\.com\/(\d+)/', $vurl, $m)) {
							$video_thumb = ''; // Vimeo requires API call
						}
					?>
					<button type="button" class="wpbs-gallery__thumb wpbs-gallery__thumb--video" data-index="<?php echo $video_index; ?>" data-type="video" data-video-url="<?php echo esc_url($vurl); ?>">
						<?php if ($video_thumb) : ?>
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

			<!-- Quick Specs Icons Row -->
			<div class="wpbs-quick-specs">
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 24 24"><path d="M20.57 14.86L22 13.43 20.57 12 17 15.57 8.43 7 12 3.43 10.57 2 9.14 3.43 7.71 2 5.57 4.14 4.14 2.71 2.71 4.14l1.43 1.43L2 7.71l1.43 1.43L2 10.57 3.43 12 7 8.43 15.57 17 12 20.57 13.43 22l1.43-1.43L16.29 22l2.14-2.14 1.43 1.43 1.43-1.43-1.43-1.43L22 16.29z"/></svg>
					<div class="wpbs-quick-spec__label">Engines</div>
					<div class="wpbs-quick-spec__value"><?php echo esc_html($num_engines ? $num_engines . 'x ' . ($engine_make ?: '') : ($engine ?: '—')); ?></div>
				</div>
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
					<div class="wpbs-quick-spec__label">Total Power</div>
					<div class="wpbs-quick-spec__value"><?php echo esc_html($total_power ?: '—'); ?></div>
				</div>
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
					<div class="wpbs-quick-spec__label">Engine Hours</div>
					<div class="wpbs-quick-spec__value"><?php echo esc_html($engine_hrs ?: '—'); ?></div>
				</div>
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 24 24"><path d="M19.77 7.23l.01-.01-3.72-3.72L15 4.56l2.11 2.11c-.94.36-1.61 1.26-1.61 2.33 0 1.38 1.12 2.5 2.5 2.5.36 0 .69-.08 1-.21v7.21c0 .55-.45 1-1 1s-1-.45-1-1V14c0-1.1-.9-2-2-2h-1V5c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v16h10v-7.5h1.5v5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V9c0-.69-.28-1.32-.73-1.77zM12 10H6V5h6v5z"/></svg>
					<div class="wpbs-quick-spec__label">Fuel</div>
					<div class="wpbs-quick-spec__value"><?php echo esc_html(ucfirst($fuel_type ?: '—')); ?></div>
				</div>
			</div>

			<!-- Boat Overview -->
			<div class="wpbs-overview">
				<h2 class="wpbs-overview__title">
					Boat Overview
					<?php if ($is_sold) : ?><span class="wpbs-badge wpbs-badge--sold">Sold</span><?php endif; ?>
				</h2>
				<p class="wpbs-overview__text">
					<?php
					$excerpt = wp_trim_words(get_the_content(), 50, '...');
					echo esc_html($excerpt);
					?>
				</p>
			</div>

			<!-- Boat Details Tabs -->
			<div class="wpbs-tabs">
				<div class="wpbs-tabs__nav">
					<button type="button" class="wpbs-tabs__btn is-active" data-tab="description">Description</button>
					<button type="button" class="wpbs-tabs__btn" data-tab="measurements">Measurements</button>
					<button type="button" class="wpbs-tabs__btn" data-tab="propulsion">Propulsion</button>
					<button type="button" class="wpbs-tabs__btn" data-tab="features">Features</button>
				</div>
				<div class="wpbs-tabs__content" id="wpbs-tab-content">
					<!-- Description Tab (default) -->
					<div class="wpbs-tab-pane" data-pane="description">
						<?php if (get_the_content()) : ?>
							<?php the_content(); ?>
						<?php else : ?>
							<p style="color:#666;">No description available.</p>
						<?php endif; ?>
					</div>

					<!-- Measurements Tab -->
					<div class="wpbs-tab-pane" data-pane="measurements" style="display:none;">
						<div class="wpbs-specs-table">
							<?php if ($length) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Length Overall</span><span class="wpbs-specs-row__value"><?php echo esc_html($length); ?></span></div>
							<?php endif; ?>
							<?php if ($beam) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Beam</span><span class="wpbs-specs-row__value"><?php echo esc_html($beam); ?></span></div>
							<?php endif; ?>
							<?php if ($dry_weight) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Dry Weight</span><span class="wpbs-specs-row__value"><?php echo esc_html($dry_weight); ?></span></div>
							<?php endif; ?>
							<?php if ($year) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Year</span><span class="wpbs-specs-row__value"><?php echo esc_html($year); ?></span></div>
							<?php endif; ?>
							<?php if ($make) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Make</span><span class="wpbs-specs-row__value"><?php echo esc_html($make); ?></span></div>
							<?php endif; ?>
							<?php if ($model) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Model</span><span class="wpbs-specs-row__value"><?php echo esc_html($model); ?></span></div>
							<?php endif; ?>
							<?php if ($hull_type) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Hull Material</span><span class="wpbs-specs-row__value"><?php echo esc_html($hull_type); ?></span></div>
							<?php endif; ?>
							<?php if ($hull_id) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Hull ID</span><span class="wpbs-specs-row__value"><?php echo esc_html($hull_id); ?></span></div>
							<?php endif; ?>
							<?php if ($cabins) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Cabins</span><span class="wpbs-specs-row__value"><?php echo esc_html($cabins); ?></span></div>
							<?php endif; ?>
							<?php if ($heads) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Heads</span><span class="wpbs-specs-row__value"><?php echo esc_html($heads); ?></span></div>
							<?php endif; ?>
						</div>
					</div>

					<!-- Propulsion Tab -->
					<div class="wpbs-tab-pane" data-pane="propulsion" style="display:none;">
						<div class="wpbs-specs-table">
							<?php if ($num_engines) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Number of Engines</span><span class="wpbs-specs-row__value"><?php echo esc_html($num_engines); ?></span></div>
							<?php endif; ?>
							<?php if ($engine_make) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Engine Make</span><span class="wpbs-specs-row__value"><?php echo esc_html($engine_make); ?></span></div>
							<?php endif; ?>
							<?php if ($engine_model) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Engine Model</span><span class="wpbs-specs-row__value"><?php echo esc_html($engine_model); ?></span></div>
							<?php endif; ?>
							<?php if ($engine_type) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Engine Type</span><span class="wpbs-specs-row__value"><?php echo esc_html($engine_type); ?></span></div>
							<?php endif; ?>
							<?php if ($total_power) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Total Power</span><span class="wpbs-specs-row__value"><?php echo esc_html($total_power); ?></span></div>
							<?php endif; ?>
							<?php if ($engine_hrs) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Engine Hours</span><span class="wpbs-specs-row__value"><?php echo esc_html($engine_hrs); ?></span></div>
							<?php endif; ?>
							<?php if ($fuel_type) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Fuel Type</span><span class="wpbs-specs-row__value"><?php echo esc_html(ucfirst($fuel_type)); ?></span></div>
							<?php endif; ?>
							<?php if ($fuel_capacity) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Fuel Capacity</span><span class="wpbs-specs-row__value"><?php echo esc_html($fuel_capacity); ?></span></div>
							<?php endif; ?>
							<?php if ($water_capacity) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Water Capacity</span><span class="wpbs-specs-row__value"><?php echo esc_html($water_capacity); ?></span></div>
							<?php endif; ?>
							<?php if ($propeller) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Propeller</span><span class="wpbs-specs-row__value"><?php echo esc_html($propeller); ?></span></div>
							<?php endif; ?>
							<?php if ($drive_type) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Drive Type</span><span class="wpbs-specs-row__value"><?php echo esc_html($drive_type); ?></span></div>
							<?php endif; ?>
							<?php if ($cruising_speed) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Cruising Speed</span><span class="wpbs-specs-row__value"><?php echo esc_html($cruising_speed); ?></span></div>
							<?php endif; ?>
							<?php if ($max_speed) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Max Speed</span><span class="wpbs-specs-row__value"><?php echo esc_html($max_speed); ?></span></div>
							<?php endif; ?>
						</div>
					</div>

					<!-- Features Tab -->
					<div class="wpbs-tab-pane" data-pane="features" style="display:none;">
						<div class="wpbs-specs-table">
							<?php if ($condition) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Condition</span><span class="wpbs-specs-row__value"><?php echo esc_html($condition); ?></span></div>
							<?php endif; ?>
							<?php if ($boat_category) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Category</span><span class="wpbs-specs-row__value"><?php echo esc_html($boat_category); ?></span></div>
							<?php endif; ?>
							<?php if ($boat_class) : ?>
							<div class="wpbs-specs-row"><span class="wpbs-specs-row__label">Class</span><span class="wpbs-specs-row__value"><?php echo esc_html($boat_class); ?></span></div>
							<?php endif; ?>
						</div>
						<p style="color:#666;margin-top:16px;">Contact dealer for full features list.</p>
					</div>
				</div>
			</div>

		</div>

		<!-- Sidebar Column -->
		<div class="wpbs-sidebar">
			<!-- Title & Price Card -->
			<div class="wpbs-price-card">
				<div class="wpbs-price-card__header">
					<h1 style="font-size:18px;font-weight:600;margin:0 0 8px;"><?php the_title(); ?></h1>
					<div style="font-size:13px;color:#666;margin-bottom:8px;">
						<?php echo esc_html(trim(($year ?: '') . ' ' . ($make ?: '') . ' ' . ($model ?: ''))); ?>
					</div>
					<?php if ($location) : ?>
					<div style="font-size:12px;color:#999;display:flex;align-items:center;gap:4px;">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="#999"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
						<?php echo esc_html($location); ?>
					</div>
					<?php endif; ?>
				</div>
				<div class="wpbs-price-card__body">
					<?php if ($price_display) : ?>
					<div class="wpbs-price-card__price"><?php echo esc_html($price_display); ?></div>
					<?php if ($monthly) : ?>
					<div class="wpbs-price-card__monthly"><a href="#calculator"><?php echo esc_html($monthly); ?></a> <small style="color:#999;">or Customize</small></div>
					<?php endif; ?>
					<?php else : ?>
					<div class="wpbs-price-card__price">Contact for Price</div>
					<?php endif; ?>

					<div style="margin-top:16px;display:flex;flex-direction:column;gap:10px;">
						<a href="#contact" class="wpbs-btn wpbs-btn--primary">
							<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
							Contact Seller
						</a>
						<?php if ($office_phone) : ?>
						<a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $office_phone)); ?>" class="wpbs-btn wpbs-btn--green">
							<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
							<?php echo esc_html($office_phone); ?>
						</a>
						<?php else : ?>
						<a href="tel:" class="wpbs-btn wpbs-btn--green">
							<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
							Call Now
						</a>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Dealer Card -->
			<div class="wpbs-dealer-card">
				<div class="wpbs-dealer-card__header">
					<div class="wpbs-dealer-card__logo">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="#999"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
					</div>
					<div class="wpbs-dealer-card__info">
						<h3 class="wpbs-dealer-card__name"><?php echo esc_html($dealer ?: 'Dealer'); ?></h3>
						<?php if ($location) : ?>
						<div class="wpbs-dealer-card__location">
							<svg width="10" height="10" viewBox="0 0 24 24" fill="#666"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
							<?php echo esc_html($location); ?>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="wpbs-dealer-card__actions">
					<a href="<?php echo esc_url(get_post_type_archive_link('boats')); ?>" class="wpbs-btn wpbs-btn--outline">View All Boats</a>
				</div>
			</div>
		</div>
	</div>

	<!-- More From Dealer -->
	<?php
	$related = new WP_Query(array(
		'post_type'      => 'boats',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'post__not_in'   => array($post_id),
		'orderby'        => 'rand',
	));
	if ($related->have_posts()) :
	?>
	<div class="wpbs-more-boats">
		<div class="wpbs-more-boats__header">
			<h2 class="wpbs-more-boats__title">More From This Dealer</h2>
			<a href="<?php echo esc_url(get_post_type_archive_link('boats')); ?>" class="wpbs-more-boats__link">View Dealer Website</a>
		</div>
		<div class="wpbs-more-boats__grid">
			<?php while ($related->have_posts()) : $related->the_post();
				$r_price = get_post_meta(get_the_ID(), 'wpbs_price', true);
				$r_price_num = $r_price ? (float)preg_replace('/[^0-9.]/', '', $r_price) : 0;
			?>
			<a href="<?php the_permalink(); ?>" class="wpbs-more-boats__item">
				<div class="wpbs-more-boats__img">
					<?php if (has_post_thumbnail()) : the_post_thumbnail('medium'); endif; ?>
				</div>
				<div class="wpbs-more-boats__name"><?php the_title(); ?></div>
				<div class="wpbs-more-boats__price"><?php echo $r_price_num ? '$' . number_format($r_price_num) : 'Contact'; ?></div>
			</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
	<?php endif; ?>
</div>

<!-- Lightbox Modal -->
<div class="wpbs-lightbox" id="wpbs-lightbox" style="display:none;">
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
			<img class="wpbs-lightbox__img" id="wpbs-lightbox-img" src="" alt="">
			<div class="wpbs-lightbox__video" id="wpbs-lightbox-video" style="display:none;"></div>
		</div>
		<div class="wpbs-lightbox__counter"><span id="wpbs-lightbox-current">1</span> / <span id="wpbs-lightbox-total"><?php echo $total_media; ?></span></div>
		<div class="wpbs-lightbox__thumbs" id="wpbs-lightbox-thumbs"></div>
	</div>
</div>

<script>
// Gallery items data for lightbox
var wpbsGalleryItems = <?php echo json_encode($gallery_items); ?>;

// Tab switching
document.addEventListener('DOMContentLoaded', function() {
	var tabBtns = document.querySelectorAll('.wpbs-tabs__btn');
	var tabPanes = document.querySelectorAll('.wpbs-tab-pane');
	tabBtns.forEach(function(btn) {
		btn.addEventListener('click', function() {
			var tab = btn.getAttribute('data-tab');
			tabBtns.forEach(function(b) { b.classList.remove('is-active'); });
			btn.classList.add('is-active');
			tabPanes.forEach(function(p) {
				p.style.display = p.getAttribute('data-pane') === tab ? 'block' : 'none';
			});
		});
	});

	// Lightbox functionality
	var lightbox = document.getElementById('wpbs-lightbox');
	var lightboxImg = document.getElementById('wpbs-lightbox-img');
	var lightboxVideo = document.getElementById('wpbs-lightbox-video');
	var lightboxCurrent = document.getElementById('wpbs-lightbox-current');
	var currentIndex = 0;
	var totalItems = wpbsGalleryItems.length;

	function openLightbox(index) {
		currentIndex = index || 0;
		showItem(currentIndex);
		lightbox.style.display = 'flex';
		document.body.style.overflow = 'hidden';
	}

	function closeLightbox() {
		lightbox.style.display = 'none';
		document.body.style.overflow = '';
		lightboxVideo.innerHTML = '';
		lightboxVideo.style.display = 'none';
	}

	function showItem(index) {
		if (index < 0) index = totalItems - 1;
		if (index >= totalItems) index = 0;
		currentIndex = index;

		var item = wpbsGalleryItems[index];
		if (!item) return;

		if (item.type === 'image') {
			lightboxImg.src = item.full || item.large || '';
			lightboxImg.style.display = 'block';
			lightboxVideo.innerHTML = '';
			lightboxVideo.style.display = 'none';
		} else if (item.type === 'video') {
			lightboxImg.style.display = 'none';
			var videoUrl = item.url;
			var embedHtml = '';
			// YouTube
			if (videoUrl.match(/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/) || videoUrl.match(/youtu\.be\/([a-zA-Z0-9_-]+)/)) {
				var vid = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/)[1];
				embedHtml = '<iframe src="https://www.youtube.com/embed/' + vid + '?autoplay=1" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
			}
			// Vimeo
			else if (videoUrl.match(/vimeo\.com\/(\d+)/)) {
				var vid = videoUrl.match(/vimeo\.com\/(\d+)/)[1];
				embedHtml = '<iframe src="https://player.vimeo.com/video/' + vid + '?autoplay=1" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
			}
			// Direct video
			else {
				embedHtml = '<video src="' + videoUrl + '" controls autoplay style="max-width:100%;max-height:80vh;"></video>';
			}
			lightboxVideo.innerHTML = embedHtml;
			lightboxVideo.style.display = 'flex';
		}

		lightboxCurrent.textContent = index + 1;
	}

	function nextItem() { showItem(currentIndex + 1); }
	function prevItem() { showItem(currentIndex - 1); }

	// Event listeners
	document.querySelectorAll('[data-wpbs-open-lightbox]').forEach(function(el) {
		el.addEventListener('click', function(e) {
			e.preventDefault();
			var idx = parseInt(el.getAttribute('data-index') || '0', 10);
			openLightbox(idx);
		});
	});

	// Lightbox trigger on main image area (but not on nav buttons)
	var mainArea = document.querySelector('[data-wpbs-lightbox-trigger]');
	if (mainArea) {
		mainArea.addEventListener('click', function(e) {
			// Don't open lightbox if clicking on nav buttons or view button
			if (e.target.closest('.wpbs-gallery__nav') || e.target.closest('.wpbs-gallery__view-btn')) {
				return;
			}
			e.preventDefault();
			var mainLink = mainArea.querySelector('.wpbs-gallery__main-link');
			var idx = mainLink ? parseInt(mainLink.getAttribute('data-index') || '0', 10) : 0;
			openLightbox(idx);
		});
	}

	lightbox.querySelector('.wpbs-lightbox__close')?.addEventListener('click', closeLightbox);
	lightbox.querySelector('.wpbs-lightbox__overlay')?.addEventListener('click', closeLightbox);
	lightbox.querySelector('.wpbs-lightbox__nav--next')?.addEventListener('click', nextItem);
	lightbox.querySelector('.wpbs-lightbox__nav--prev')?.addEventListener('click', prevItem);

	// Keyboard navigation
	document.addEventListener('keydown', function(e) {
		if (lightbox.style.display !== 'flex') return;
		if (e.key === 'Escape') closeLightbox();
		if (e.key === 'ArrowRight') nextItem();
		if (e.key === 'ArrowLeft') prevItem();
	});

	// Gallery thumbnail clicks (update main image and open lightbox on second click)
	var lastClickedIndex = -1;
	document.querySelectorAll('.wpbs-gallery__thumb').forEach(function(thumb) {
		thumb.addEventListener('click', function() {
			var idx = parseInt(thumb.getAttribute('data-index') || '0', 10);
			var type = thumb.getAttribute('data-type') || 'image';

			// Update all thumbs active state
			document.querySelectorAll('.wpbs-gallery__thumb').forEach(function(t) { t.classList.remove('is-active'); });
			thumb.classList.add('is-active');

			// Update main image
			var mainImg = document.getElementById('wpbs-main-img');
			if (mainImg && type === 'image') {
				mainImg.src = thumb.getAttribute('data-large') || '';
			}

			// Double-click or video opens lightbox
			if (type === 'video' || idx === lastClickedIndex) {
				openLightbox(idx);
			}
			lastClickedIndex = idx;
		});
	});
});
</script>

<?php endwhile;

get_footer();
