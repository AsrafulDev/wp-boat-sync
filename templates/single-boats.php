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
	$deadrise = get_post_meta($post_id, 'wpbs_deadrise', true);
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

	$video_urls_raw = get_post_meta($post_id, 'wpbs_embedded_video_urls', true);
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
		$vclean = preg_replace('/\|.*$/', '', trim($vurl));
		if ($vclean === '') continue;
		$gallery_items[] = array(
			'type' => 'video',
			'url' => $vclean,
			'thumb' => '', // Video thumbnail placeholder
		);
	}

	// Price formatting
	$price_display = '';
	$price_num = 0;
	if ($price) {
		$price_num = (float)preg_replace('/[^0-9.]/', '', $price);
		$price_display = '$' . number_format($price_num);
	} elseif ($is_sold) {
		$price_display = 'Sold';
	} else {
		$price_display = 'Contact for Price';
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
						$vclean = preg_replace('/\|.*$/', '', trim($vurl));
						if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $vclean, $m) || preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $vclean, $m)) {
							$video_thumb = 'https://img.youtube.com/vi/' . $m[1] . '/mqdefault.jpg';
						} elseif (preg_match('/vimeo\.com\/(\d+)/', $vclean, $m)) {
							$video_thumb = ''; // Vimeo requires API call
						}
					?>
					<button type="button" class="wpbs-gallery__thumb wpbs-gallery__thumb--video" data-index="<?php echo $video_index; ?>" data-type="video" data-video-url="<?php echo esc_url($vclean); ?>">
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

			<!-- Quick Specs Icons Row (8 items) -->
			<div class="wpbs-quick-specs">
				<!-- 1. Engine -->
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m347.35,178.61c-.15,0-.3,0-.45-.02h-165.87c-2.76,0-5-2.24-5-5v-47.05c0-15.55,12.66-28.21,28.22-28.21h119.89c15.56,0,28.21,12.66,28.21,28.21v47.07c0,1.43-.61,2.79-1.68,3.74-.92.82-2.1,1.26-3.32,1.26Zm-161.32-10.02h156.32v-42.05c0-10.04-8.17-18.21-18.21-18.21h-119.89c-10.05,0-18.22,8.17-18.22,18.21v42.05Z"/><path fill="#104f79" d="m318.65,244.53h-108.92c-13.29,0-24.87-9-28.17-21.88l-5.37-20.94c-.1-.41-.16-.82-.16-1.24v-.38c0-2.76,2.24-5,5-5h165.81c.08,0,.15,0,.23,0,1.37-.05,2.72.44,3.71,1.38.99.94,1.57,2.25,1.57,3.61v.39c0,.42-.05.83-.16,1.24l-5.36,20.94c-3.3,12.88-14.89,21.88-28.18,21.88Z"/><path fill="#104f79" d="m233.76,413.24c-14.88,0-26.98-12.1-26.98-26.98v-145.31c0-2.76,2.24-5,5-5h86.82c1.57,0,3.05.74,4,2,.94,1.26,1.24,2.88.81,4.39l-43.73,151.41c-3.31,11.48-13.97,19.49-25.92,19.49Z"/></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-spec__label">Engine</div>
						<div class="wpbs-quick-spec__value"><?php echo esc_html($engine ?: ($engine_make && $engine_model ? $engine_make . ' ' . $engine_model : '—')); ?></div>
					</div>
				</div>
				<!-- 2. Total Power -->
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m258,306.66c-27.11,0-49.17-22.06-49.17-49.17s22.06-49.17,49.17-49.17,49.17,22.06,49.17,49.17-22.06,49.17-49.17,49.17Zm0-88.33c-21.6,0-39.17,17.57-39.17,39.17s17.57,39.17,39.17,39.17,39.17-17.57,39.17-39.17-17.57-39.17-39.17-39.17Z"/><path fill="#104f79" d="m220.41,239.31c-1.49,0-2.95-.66-3.94-1.92-34.8-44.45-28.12-75.46-21.05-89.99,10.22-20.98,33.39-34.02,60.47-34.02,20.55,0,38,5.08,50.48,14.69,11.42,8.8,17.97,21.17,17.97,33.94,0,20.28-11.74,26.97-21.16,32.35-9.41,5.37-16.84,9.61-16.12,25.88.12,2.76-2.01,5.09-4.77,5.22-2.73.09-5.09-2.01-5.22-4.77-.99-22.38,11.29-29.38,21.16-35.01,9.01-5.14,16.12-9.19,16.12-23.66,0-25.36-29.41-38.63-58.46-38.63-23.22,0-42.95,10.88-51.48,28.4-6.05,12.43-11.52,39.28,19.93,79.45,1.7,2.17,1.32,5.32-.85,7.02-.91.72-2,1.06-3.08,1.06Z"/><path fill="#104f79" d="m258,277.2c-10.87,0-19.71-8.84-19.71-19.71s8.84-19.71,19.71-19.71,19.71,8.84,19.71,19.71-8.84,19.71-19.71,19.71Zm0-29.41c-5.35,0-9.71,4.35-9.71,9.71s4.35,9.71,9.71,9.71,9.71-4.35,9.71-9.71-4.35-9.71-9.71-9.71Z"/></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-spec__label">Total Power</div>
						<div class="wpbs-quick-spec__value"><?php echo esc_html($total_power ?: '—'); ?></div>
					</div>
				</div>
				<!-- 3. Engine Hours -->
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="19.5" fill="none" stroke="#e0e0e0"/><path fill="#104f79" d="M31.72 15.33c-.3-.71-.68-1.4-1.11-2.04-.43-.64-.92-1.23-1.47-1.78-.54-.54-1.14-1.03-1.78-1.47-.64-.43-1.35-.8-2.04-1.11C23.86 8.32 22.28 8 20.67 8s-3.19.32-4.67.94c-.71.3-1.4.68-2.04 1.11-.64.43-1.23.92-1.78 1.47-.55.54-1.04 1.14-1.47 1.78-.43.64-.8 1.35-1.11 2.04C8.99 16.81 8.67 18.38 8.67 20s.32 3.19.94 4.67c.3.71.68 1.4 1.11 2.04.43.64.92 1.23 1.47 1.78.54.54 1.14 1.03 1.78 1.47.64.43 1.35.8 2.04 1.11 1.48.62 3.05.94 4.67.94s3.19-.32 4.67-.94c.71-.3 1.4-.68 2.04-1.11.64-.43 1.23-.92 1.78-1.47.54-.54 1.03-1.14 1.47-1.78.43-.64.8-1.35 1.11-2.04.62-1.48.94-3.05.94-4.67s-.32-3.19-.94-4.67Zm-.71 8.96c-.57 1.34-1.38 2.54-2.41 3.58s-2.24 1.86-3.58 2.41c-1.38.58-2.86.88-4.37.88s-2.99-.3-4.37-.88c-1.34-.57-2.54-1.38-3.58-2.41s-1.86-2.24-2.41-3.58c-.58-1.38-.88-2.86-.88-4.37s.3-2.99.88-4.37c.57-1.34 1.38-2.54 2.41-3.58s2.24-1.86 3.58-2.41c1.38-.58 2.86-.88 4.37-.88s2.99.3 4.37.88c1.34.57 2.54 1.38 3.58 2.41s1.86 2.24 2.41 3.58c.58 1.38.88 2.86.88 4.37s-.3 2.99-.88 4.37Z"/><path fill="#104f79" d="M23.29 14.08c-.94 1.16-2.98 3.75-3.75 5.38-.3.64-.03 1.41.64 1.71.64.3 1.4.02 1.71-.64.75-1.61 1.47-4.85 1.78-6.31.04-.2-.21-.32-.34-.16l-.04.02Z"/><rect fill="#104f79" x="20.17" y="11.12" width=".99" height="1.19"/><rect fill="#104f79" x="14.04" y="13.37" width=".99" height="1.19" transform="rotate(-45 14.54 13.97)"/><rect fill="#104f79" x="11.79" y="19.51" width="1.19" height=".99"/><rect fill="#104f79" x="14.04" y="25.09" width=".99" height="1.19" transform="rotate(-45 14.54 25.69)"/><rect fill="#104f79" x="25.74" y="25.09" width=".99" height="1.19" transform="rotate(45 26.24 25.69)"/><rect fill="#104f79" x="28.35" y="19.51" width="1.19" height=".99"/><rect fill="#104f79" x="25.74" y="13.37" width=".99" height="1.19" transform="rotate(45 26.24 13.97)"/></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-spec__label">Engine Hours</div>
						<div class="wpbs-quick-spec__value"><?php echo esc_html($engine_hrs ?: '—'); ?></div>
					</div>
				</div>
				<!-- 4. Class -->
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m163.73,294.58c-4.25,1.36-8.44,1.48-12.52.34-5.4-1.51-8.73-4.75-8.74-4.76-.94-.95-2.22-1.49-3.56-1.49h-.23c-1.07,0-2.1.34-2.96.97-7.45,5.47-14.8,7.25-21.84,5.28-5.4-1.51-8.74-4.75-8.75-4.76-.94-.95-2.22-1.49-3.56-1.49h-.22c-1.06,0-2.1.34-2.96.97-7.45,5.47-14.8,7.25-21.84,5.28-5.33-1.49-8.65-4.67-8.77-4.78-1.94-1.95-5.09-1.96-7.05-.03-1.96,1.94-1.98,5.1-.05,7.07,6.1,6.18,22.02,14.14,40.47,2.66,7.5,5.37,21.48,9.86,37.34,0,4.26,3.06,10.63,5.83,18.25,5.83,3.64,0,7.57-.63,11.68-2.17l-4.69-8.92Z"/><path fill="#104f79" d="m372.08,262.35c-.07-.34-.18-.68-.32-1.01-.6-1.39-1.8-2.43-3.26-2.83l-31.79-8.73-6.94-39.29c-.35-1.98-.9-3.89-1.65-5.68-4.17-10.23-14.17-17.18-25.6-17.18h-17.56v-4.44c0-7.43-6.04-13.47-13.47-13.47h-22.83c-7.43,0-13.47,6.04-13.47,13.47v4.44h-17.56c-13.45,0-24.91,9.61-27.25,22.86l-6.69,37.88-.25,1.41-22.31,6.13h-.01l-9.47,2.6c-1.46.4-2.66,1.44-3.26,2.83s-.54,2.98.16,4.32l46.84,89.21c.86,1.64,2.56,2.67,4.42,2.67h120.53c1.86,0,3.56-1.03,4.43-2.67l46.83-89.21c.54-1.02.71-2.19.48-3.31Z"/></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-spec__label">Class</div>
						<div class="wpbs-quick-spec__value"><?php echo esc_html($boat_class ?: ($boat_category ?: '—')); ?></div>
					</div>
				</div>
				<!-- 5. Length -->
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m359.98,396.11h-207.96c-16.54,0-30-13.46-30-30v-110.84c0-16.54,13.46-30,30-30h207.96c16.54,0,30,13.46,30,30v110.84c0,16.54-13.46,30-30,30Zm-207.96-160.84c-11.03,0-20,8.97-20,20v110.84c0,11.03,8.97,20,20,20h207.96c11.03,0,20-8.97,20-20v-110.84c0-11.03-8.97-20-20-20h-207.96Z"/><path fill="#104f79" d="m384.98,164h-257.96c-2.76,0-5-2.24-5-5s2.24-5,5-5h257.96c2.76,0,5,2.24,5,5s-2.24,5-5,5Z"/><path fill="#104f79" d="m165.13,202.12c-1.28,0-2.56-.49-3.54-1.46l-38.11-38.11c-.94-.94-1.46-2.21-1.46-3.54s.53-2.6,1.46-3.54l38.11-38.11c1.95-1.95,5.12-1.95,7.07,0,1.95,1.95,1.95,5.12,0,7.07l-34.58,34.58,34.58,34.58c1.95,1.95,1.95,5.12,0,7.07-.98.98-2.26,1.46-3.54,1.46Z"/><path fill="#104f79" d="m346.87,202.12c-1.28,0-2.56-.49-3.54-1.46-1.95-1.95-1.95-5.12,0-7.07l34.58-34.58-34.58-34.58c-1.95-1.95-1.95-5.12,0-7.07,1.95-1.95,5.12-1.95,7.07,0l38.11,38.11c.94.94,1.46,2.21,1.46,3.54s-.53,2.6-1.46,3.54l-38.11,38.11c-.98.98-2.26,1.46-3.54,1.46Z"/></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-spec__label">Length</div>
						<div class="wpbs-quick-spec__value"><?php echo esc_html($length ?: '—'); ?></div>
					</div>
				</div>
				<!-- 6. Year -->
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m365.67,387.07h-221.18c-12.68,0-23-10.32-23-23v-203.13c0-12.68,10.32-23,23-23h221.18c12.68,0,23,10.32,23,23v203.13c0,12.68-10.32,23-23,23Zm-221.18-239.13c-7.17,0-13,5.83-13,13v203.13c0,7.17,5.83,13,13,13h221.18c7.17,0,13-5.83,13-13v-203.13c0-7.17-5.83-13-13-13h-221.18Z"/><path fill="#104f79" d="m383.67,210.53h-257.18c-2.76,0-5-2.24-5-5s2.24-5,5-5h257.18c2.76,0,5,2.24,5,5s-2.24,5-5,5Z"/><path fill="#104f79" d="m152.96,259.33c-1.06-1.99-.13-4.64,1.99-5.71,1.99-.93,4.64-.13,5.71,1.99l14.73,29.46,14.73-29.46c1.06-2.12,3.58-2.92,5.71-1.99,2.12,1.06,3.05,3.72,1.99,5.71l-18.18,36.36v38.75c0,2.52-1.73,4.25-4.25,4.25s-4.25-1.73-4.25-4.25v-38.75l-18.18-36.36Z"/><path fill="#104f79" d="m314.47,174.57c-2.76,0-5-2.24-5-5v-53.01c0-2.76,2.24-5,5-5s5,2.24,5,5v53.01c0,2.76-2.24,5-5,5Zm-118.78,0c-2.76,0-5-2.24-5-5v-53.01c0-2.76,2.24-5,5-5s5,2.24,5,5v53.01c0,2.76-2.24,5-5,5Z"/></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-spec__label">Year</div>
						<div class="wpbs-quick-spec__value"><?php echo esc_html($year ?: '—'); ?></div>
					</div>
				</div>
				<!-- 7. Model -->
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 512 512"><circle cx="256" cy="256" r="246" fill="none" stroke="#e0e0e0" stroke-width="10"/><path fill="#104f79" d="m430.37,331.25c-23.1,0-34.69,6.55-46.03,12.85-10.58,5.96-20.66,11.51-41.16,11.51-8.65,0-16.21-1.01-22.93-3.11-7.06-2.18-12.51-5.21-18.39-8.48-4.2-2.35-8.48-4.79-13.44-6.8-9.74-4.03-19.99-5.96-32.42-5.96s-22.76,1.93-32.42,5.96c-5.12,2.1-9.41,4.54-13.61,6.89-10.67,5.96-20.66,11.51-41.16,11.51s-30.57-5.54-41.24-11.51c-4.2-2.35-8.57-4.79-13.61-6.89-7.73-3.28-15.62-5.04-24.86-5.71-2.35-.17-4.96-.25-7.48-.25-2.77,0-5.04,2.27-5.04,4.96s2.27,4.96,5.04,4.96c2.35,0,4.62.08,6.8.25,8.06.59,15.03,2.1,21.75,4.96,4.45,1.85,8.31,4.03,12.6,6.38,11.25,6.3,22.93,12.85,46.03,12.85s34.69-6.55,45.94-12.85c4.2-2.35,8.06-4.54,12.6-6.38,8.57-3.61,17.39-5.21,28.64-5.21s19.99,1.6,28.56,5.21c4.62,1.93,8.65,4.2,12.6,6.38,5.96,3.36,12.09,6.8,20.16,9.32,7.73,2.35,16.21,3.53,25.87,3.53,23.1,0,34.77-6.55,46.03-12.85,10.67-5.96,20.66-11.59,41.16-11.59,2.77,0,4.96-2.18,4.96-4.96s-2.18-4.96-4.96-4.96Z"/><path fill="#104f79" d="m372.08,262.35c-.07-.34-.18-.68-.32-1.01-.6-1.39-1.8-2.43-3.26-2.83l-31.79-8.73-6.94-39.29c-.35-1.98-.9-3.89-1.65-5.68-4.17-10.23-14.17-17.18-25.6-17.18h-17.56v-4.44c0-7.43-6.04-13.47-13.47-13.47h-22.83c-7.43,0-13.47,6.04-13.47,13.47v4.44h-17.56c-13.45,0-24.91,9.61-27.25,22.86l-6.69,37.88-.25,1.41-22.31,6.13h-.01l-9.47,2.6c-1.46.4-2.66,1.44-3.26,2.83s-.54,2.98.16,4.32l46.84,89.21c.86,1.64,2.56,2.67,4.42,2.67h120.53c1.86,0,3.56-1.03,4.43-2.67l46.83-89.21c.54-1.02.71-2.19.48-3.31Z"/></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-spec__label">Model</div>
						<div class="wpbs-quick-spec__value"><?php echo esc_html($model ?: ($make ?: '—')); ?></div>
					</div>
				</div>
				<!-- 8. Capacity -->
				<div class="wpbs-quick-spec">
					<svg class="wpbs-quick-spec__icon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="19.5" fill="none" stroke="#e0e0e0"/><path fill="#104f79" d="M18.85 11.93c-1.42.39-2.53 1.56-2.9 3.05-.32 1.3.07 2.77.99 3.77l.39.42-.44.16-.44.15-.34-.34c-.21-.2-.55-.44-.86-.6l-.52-.26.22-.27c1.04-1.26 1-3.04-.09-4.27-.74-.83-1.94-1.21-2.97-.92-1.06.29-2 1.29-2.24 2.36-.08.36-.08 1.12 0 1.48.09.41.38.98.67 1.33l.26.31-.34.14c-1.08.46-1.93 1.5-2.16 2.68-.11.51-.13 2.48-.02 2.67.18.34 2.55 1.21 3.32 1.21.26 0 .45-.21.45-.5 0-.31-.13-.4-.77-.54-.69-.15-1.27-.34-1.76-.57l-.37-.18.02-.96c.02-.82.05-1.03.16-1.32.29-.77.88-1.37 1.63-1.66.32-.12.5-.14 1.67-.16.84-.01 1.45.01 1.71.06.45.08 1 .35 1.29.62l.19.18-.44.45c-.81.83-1.28 1.84-1.4 3.02-.04.33-.06 1.11-.05 1.72.02 1.32-.04 1.21.84 1.63 3.35 1.61 7.07 1.61 10.39 0 .91-.44.85-.32.87-1.63.01-.61-.01-1.39-.05-1.72-.12-1.18-.59-2.19-1.4-3.02l-.44-.45.2-.18c.31-.28.86-.55 1.3-.62.25-.05.87-.06 1.71-.06 1.17.01 1.35.03 1.67.16.75.29 1.34.89 1.63 1.66.11.29.14.5.16 1.33l.02.96-.22.12c-.4.23-1.21.48-1.85.62-.69.15-.82.23-.82.54 0 .29.19.5.48.5.28 0 1.11-.2 1.68-.4.67-.23 1.53-.67 1.61-.81.1-.19.08-2.16-.02-2.67-.24-1.16-1.09-2.22-2.16-2.68l-.34-.14.26-.31c.48-.58.74-1.28.74-2.07-.01-.92-.27-1.57-.9-2.23-.64-.67-1.32-.86-2.21-.86-1.64.01-3.03 1.48-3.03 3.2 0 .74.28 1.5.76 2.09l.22.27-.52.26c-.32.16-.65.4-.86.6l-.34.34-.45-.16-.45-.16.21-.17c.52-.45 1.08-1.45 1.24-2.23.36-1.8-.53-3.67-2.17-4.5-.82-.42-1.8-.53-2.65-.32Zm1.53.93c1.11.24 2.11 1.28 2.34 2.44.21 1.04-.08 2.03-.86 2.81-1.22 1.27-2.98 1.27-4.2 0s-1.22-2.54 0-3.81c.76-.79 1.67-1.07 2.72-.85ZM13.5 13.88c.38.18.89.69 1.08 1.11.15.3.16.42.16.93 0 .52-.02.65-.17.98-.21.42-.7.92-1.08 1.11-.23.11-.39.13-.84.13-.49 0-.64-.02-.94-.15-1.24-.63-1.61-2.24-.84-3.38.21-.29.61-.58 1-.73.34-.13 1.15-.13 1.5 0Zm14.3.03c.45.23.84.64 1.03 1.07.14.3.16.43.16.93 0 .52-.02.65-.17.93-.22.45-.61.86-1.03 1.11-.28.15-.39.17-.88.17-.45 0-.61-.03-.84-.13-.39-.19-.87-.62-1.08-1.04-.15-.3-.17-.43-.17-.93 0-.7.11-1.01.52-1.47.47-.57.85-.72 1.52-.69.37.01.56.04.81.15Zm-5.87 6.21c1.34.29 2.49 1.44 2.83 2.82.13.54.18 2.74.07 2.83-.17.14-1.29.59-1.94.78-1.11.33-1.82.43-3.09.43s-1.98-.1-3.09-.43c-.65-.2-1.77-.65-1.94-.78-.11-.09-.06-2.29.07-2.83.34-1.37 1.48-2.51 2.86-2.82.56-.13 2.69-.13 3.23 0Z"/></svg>
					<div class="wpbs-quick-spec__text">
						<div class="wpbs-quick-spec__label">Capacity</div>
						<div class="wpbs-quick-spec__value"><?php echo esc_html(get_post_meta($post_id, 'wpbs_passenger_capacity', true) ?: '—'); ?></div>
					</div>
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

			<!-- Boat Details Accordion -->
			<div class="wpbs-accordion-details">
				<h2 class="wpbs-accordion-details__title">Boat Details</h2>

				<!-- Description -->
				<details class="wpbs-accordion-item" open>
					<summary class="wpbs-accordion-item__header"><h3>Description</h3></summary>
					<div class="wpbs-accordion-item__content">
						<?php if (get_the_content()) : ?>
							<div class="wpbs-description-text" data-wpbs-expandable>
								<?php the_content(); ?>
							</div>
							<button type="button" class="wpbs-show-more-btn" data-wpbs-toggle-expand>Show More</button>
						<?php else : ?>
							<p style="color:#666;">No description available.</p>
						<?php endif; ?>
					</div>
				</details>

				<!-- Measurements -->
				<details class="wpbs-accordion-item">
					<summary class="wpbs-accordion-item__header"><h3>Measurements</h3></summary>
					<div class="wpbs-accordion-item__content">
						<div class="wpbs-details-grid">
							<?php
							// Dimensions
							$dimensions = array();
							if ($length) $dimensions['Length Overall'] = $length;
							$nominal_length = get_post_meta($post_id, 'wpbs_nominal_length', true);
							if ($nominal_length) $dimensions['Nominal Length'] = $nominal_length;
							$min_draft = get_post_meta($post_id, 'wpbs_min_draft', true);
							if ($min_draft) $dimensions['Min Draft'] = $min_draft;
							if ($beam) $dimensions['Beam'] = $beam;
							$bridge_clearance = get_post_meta($post_id, 'wpbs_bridge_clearance', true);
							if ($bridge_clearance) $dimensions['Bridge Clearance'] = $bridge_clearance;
							$cabin_headroom = get_post_meta($post_id, 'wpbs_cabin_headroom', true);
							if ($cabin_headroom) $dimensions['Cabin Headroom'] = $cabin_headroom;
							if ($dry_weight) $dimensions['Dry Weight'] = $dry_weight;
							$displacement = get_post_meta($post_id, 'wpbs_displacement', true);
							if ($displacement) $dimensions['Displacement'] = $displacement;

							if (!empty($dimensions)) : ?>
							<div class="wpbs-details-cell">
								<h4>Dimensions</h4>
								<div class="wpbs-details-cell__content">
									<?php foreach ($dimensions as $label => $value) : ?>
									<p><span class="wpbs-details-label"><?php echo esc_html($label); ?>:</span><span class="wpbs-details-value"><?php echo esc_html($value); ?></span></p>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>

							<?php
							// Tanks
							$tanks = array();
							if ($water_capacity) $tanks['Fresh Water Tanks'] = $water_capacity;
							if ($fuel_capacity) $tanks['Fuel Tanks'] = $fuel_capacity;

							if (!empty($tanks)) : ?>
							<div class="wpbs-details-cell">
								<h4>Tanks</h4>
								<div class="wpbs-details-cell__content">
									<?php foreach ($tanks as $label => $value) : ?>
									<p><span class="wpbs-details-label"><?php echo esc_html($label); ?>:</span><span class="wpbs-details-value"><?php echo esc_html($value); ?></span></p>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>

							<?php
							// Miscellaneous
							$misc = array();
							if ($cabins) $misc['Cabins'] = $cabins;
							if ($deadrise) $misc['Deadrise At Transom'] = $deadrise;
							if ($heads) $misc['Heads'] = $heads;
							if ($hull_id) $misc['Hull ID'] = $hull_id;

							if (!empty($misc)) : ?>
							<div class="wpbs-details-cell">
								<h4>Miscellaneous</h4>
								<div class="wpbs-details-cell__content">
									<?php foreach ($misc as $label => $value) : ?>
									<p><span class="wpbs-details-label"><?php echo esc_html($label); ?>:</span><span class="wpbs-details-value"><?php echo esc_html($value); ?></span></p>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</details>

				<!-- Propulsion -->
				<details class="wpbs-accordion-item">
					<summary class="wpbs-accordion-item__header"><h3>Propulsion</h3></summary>
					<div class="wpbs-accordion-item__content">
						<div class="wpbs-details-grid">
							<?php
							// Check for engines JSON data
							$engines_json = get_post_meta($post_id, 'wpbs_engines_json', true);
							$engines = array();
							if ($engines_json) {
								$engines = json_decode($engines_json, true);
							}

							if (!empty($engines) && is_array($engines)) :
								$engine_num = 1;
								foreach ($engines as $eng) :
							?>
							<div class="wpbs-details-cell">
								<h4>Engine <?php echo $engine_num; ?></h4>
								<div class="wpbs-details-cell__content">
									<?php if (!empty($eng['Make'])) : ?>
									<p><span class="wpbs-details-label">Engine Make:</span><span class="wpbs-details-value"><?php echo esc_html($eng['Make']); ?></span></p>
									<?php endif; ?>
									<?php if (!empty($eng['Model'])) : ?>
									<p><span class="wpbs-details-label">Engine Model:</span><span class="wpbs-details-value"><?php echo esc_html($eng['Model']); ?></span></p>
									<?php endif; ?>
									<?php if (!empty($eng['Year'])) : ?>
									<p><span class="wpbs-details-label">Engine Year:</span><span class="wpbs-details-value"><?php echo esc_html($eng['Year']); ?></span></p>
									<?php endif; ?>
									<?php if (!empty($eng['EnginePower'])) : ?>
									<p><span class="wpbs-details-label">Total Power:</span><span class="wpbs-details-value"><?php echo esc_html($eng['EnginePower']); ?></span></p>
									<?php endif; ?>
									<?php if (!empty($eng['Type'])) : ?>
									<p><span class="wpbs-details-label">Engine Type:</span><span class="wpbs-details-value"><?php echo esc_html($eng['Type']); ?></span></p>
									<?php endif; ?>
									<?php if (!empty($eng['DriveType'])) : ?>
									<p><span class="wpbs-details-label">Drive Type:</span><span class="wpbs-details-value"><?php echo esc_html($eng['DriveType']); ?></span></p>
									<?php endif; ?>
									<?php if (!empty($eng['Fuel'])) : ?>
									<p><span class="wpbs-details-label">Fuel Type:</span><span class="wpbs-details-value"><?php echo esc_html($eng['Fuel']); ?></span></p>
									<?php endif; ?>
									<?php if (!empty($eng['PropellerType'])) : ?>
									<p><span class="wpbs-details-label">Propeller Type:</span><span class="wpbs-details-value"><?php echo esc_html($eng['PropellerType']); ?></span></p>
									<?php endif; ?>
									<?php if (!empty($eng['PropellerMaterial'])) : ?>
									<p><span class="wpbs-details-label">Propeller Material:</span><span class="wpbs-details-value"><?php echo esc_html($eng['PropellerMaterial']); ?></span></p>
									<?php endif; ?>
								</div>
							</div>
							<?php
								$engine_num++;
								endforeach;
							else :
								// Fallback to single engine meta fields
							?>
							<div class="wpbs-details-cell">
								<h4>Engine</h4>
								<div class="wpbs-details-cell__content">
									<?php if ($num_engines) : ?>
									<p><span class="wpbs-details-label">Number of Engines:</span><span class="wpbs-details-value"><?php echo esc_html($num_engines); ?></span></p>
									<?php endif; ?>
									<?php if ($engine_make) : ?>
									<p><span class="wpbs-details-label">Engine Make:</span><span class="wpbs-details-value"><?php echo esc_html($engine_make); ?></span></p>
									<?php endif; ?>
									<?php if ($engine_model) : ?>
									<p><span class="wpbs-details-label">Engine Model:</span><span class="wpbs-details-value"><?php echo esc_html($engine_model); ?></span></p>
									<?php endif; ?>
									<?php if ($engine_type) : ?>
									<p><span class="wpbs-details-label">Engine Type:</span><span class="wpbs-details-value"><?php echo esc_html($engine_type); ?></span></p>
									<?php endif; ?>
									<?php if ($total_power) : ?>
									<p><span class="wpbs-details-label">Total Power:</span><span class="wpbs-details-value"><?php echo esc_html($total_power); ?></span></p>
									<?php endif; ?>
									<?php if ($engine_hrs) : ?>
									<p><span class="wpbs-details-label">Engine Hours:</span><span class="wpbs-details-value"><?php echo esc_html($engine_hrs); ?></span></p>
									<?php endif; ?>
									<?php if ($fuel_type) : ?>
									<p><span class="wpbs-details-label">Fuel Type:</span><span class="wpbs-details-value"><?php echo esc_html(ucfirst($fuel_type)); ?></span></p>
									<?php endif; ?>
									<?php if ($drive_type) : ?>
									<p><span class="wpbs-details-label">Drive Type:</span><span class="wpbs-details-value"><?php echo esc_html($drive_type); ?></span></p>
									<?php endif; ?>
									<?php if ($propeller) : ?>
									<p><span class="wpbs-details-label">Propeller:</span><span class="wpbs-details-value"><?php echo esc_html($propeller); ?></span></p>
									<?php endif; ?>
								</div>
							</div>
							<?php endif; ?>

							<?php
							// Speed & Performance
							$performance = array();
							if ($cruising_speed) $performance['Cruising Speed'] = $cruising_speed;
							if ($max_speed) $performance['Max Speed'] = $max_speed;
							$range = get_post_meta($post_id, 'wpbs_range', true);
							if ($range) $performance['Range'] = $range;

							if (!empty($performance)) : ?>
							<div class="wpbs-details-cell">
								<h4>Performance</h4>
								<div class="wpbs-details-cell__content">
									<?php foreach ($performance as $label => $value) : ?>
									<p><span class="wpbs-details-label"><?php echo esc_html($label); ?>:</span><span class="wpbs-details-value"><?php echo esc_html($value); ?></span></p>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</details>

				<!-- Features -->
				<details class="wpbs-accordion-item">
					<summary class="wpbs-accordion-item__header"><h3>Features</h3></summary>
					<div class="wpbs-accordion-item__content">
						<div class="wpbs-details-grid">
							<?php
							// General Features
							$features = array();
							if ($condition) $features['Condition'] = $condition;
							if ($boat_category) $features['Category'] = $boat_category;
							if ($boat_class) $features['Class'] = $boat_class;
							if ($hull_type) $features['Hull Material'] = $hull_type;
							$keel_type = get_post_meta($post_id, 'wpbs_keel_type', true);
							if ($keel_type) $features['Keel Type'] = $keel_type;

							if (!empty($features)) : ?>
							<div class="wpbs-details-cell">
								<h4>General</h4>
								<div class="wpbs-details-cell__content">
									<?php foreach ($features as $label => $value) : ?>
									<p><span class="wpbs-details-label"><?php echo esc_html($label); ?>:</span><span class="wpbs-details-value"><?php echo esc_html($value); ?></span></p>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>

							<?php
							// Electronics/Equipment indicators
							$electronics = array();
							$trim_tabs = get_post_meta($post_id, 'wpbs_trim_tabs', true);
							if ($trim_tabs) $electronics['Trim Tabs'] = '✓';
							$windlass = get_post_meta($post_id, 'wpbs_windlass_type', true);
							if ($windlass) $electronics['Windlass'] = $windlass;
							$electrical = get_post_meta($post_id, 'wpbs_electrical_circuit', true);
							if ($electrical) $electronics['Electrical Circuit'] = $electrical;

							if (!empty($electronics)) : ?>
							<div class="wpbs-details-cell">
								<h4>Electronics & Equipment</h4>
								<div class="wpbs-details-cell__content">
									<?php foreach ($electronics as $label => $value) : ?>
									<p><span class="wpbs-details-label"><?php echo esc_html($label); ?>:</span><span class="wpbs-details-value"><?php echo esc_html($value); ?></span></p>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>

							<?php
							// Builder/Designer
							$builder_info = array();
							$builder = get_post_meta($post_id, 'wpbs_builder_name', true);
							$designer = get_post_meta($post_id, 'wpbs_designer_name', true);
							if ($builder) $builder_info['Builder'] = $builder;
							if ($designer) $builder_info['Designer'] = $designer;

							if (!empty($builder_info)) : ?>
							<div class="wpbs-details-cell">
								<h4>Builder & Designer</h4>
								<div class="wpbs-details-cell__content">
									<?php foreach ($builder_info as $label => $value) : ?>
									<p><span class="wpbs-details-label"><?php echo esc_html($label); ?>:</span><span class="wpbs-details-value"><?php echo esc_html($value); ?></span></p>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</details>

				<!-- More Details (Additional Description) -->
				<?php
				$additional_detail = get_post_meta($post_id, 'wpbs_additional_detail_html', true);
				if ($additional_detail) : ?>
				<details class="wpbs-accordion-item">
					<summary class="wpbs-accordion-item__header"><h4>More Details</h4></summary>
					<div class="wpbs-accordion-item__content">
						<div class="wpbs-additional-details">
							<?php echo wp_kses_post($additional_detail); ?>
						</div>
					</div>
				</details>
				<?php endif; ?>

				<!-- Location -->
				<?php if ($location) : ?>
				<details class="wpbs-accordion-item" open>
					<summary class="wpbs-accordion-item__header"><h4>Location</h4></summary>
					<div class="wpbs-accordion-item__content">
						<div class="wpbs-location-info">
							<p><strong><?php echo esc_html($location); ?></strong></p>
							<?php
							$boat_city = get_post_meta($post_id, 'wpbs_boat_city', true);
							$state = get_post_meta($post_id, 'wpbs_state', true);
							$country = get_post_meta($post_id, 'wpbs_boat_country', true);
							?>
							<?php if ($boat_city || $state || $country) : ?>
							<p>
								<?php echo esc_html(implode(', ', array_filter(array($boat_city, $state, $country)))); ?>
							</p>
							<?php endif; ?>
						</div>
					</div>
				</details>
				<?php endif; ?>
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
					<?php
					// Show brand(s) in sidebar (linked to brand archive)
					$brand_terms = get_the_terms($post_id, 'brand');
					if ($brand_terms && !is_wp_error($brand_terms)) :
						$brand_links = array();
						foreach ($brand_terms as $bt) {
							$link = get_term_link($bt);
							if (!is_wp_error($link)) {
								$brand_links[] = '<a href="' . esc_url($link) . '" style="color:#0066cc;text-decoration:none;">' . esc_html($bt->name) . '</a>';
							}
						}
						if (!empty($brand_links)) : ?>
						<div style="font-size:12px;color:#999;margin-top:6px;">Brand: <?php echo wp_kses_post(implode(', ', $brand_links)); ?></div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<div class="wpbs-price-card__body">
					<?php if ($price_display) : ?>
					<div class="wpbs-price-card__price"><?php echo esc_html($price_display); ?></div>
					<?php 
					// Calculate monthly payment estimate from admin settings
					if ($price_num > 0) :
						$loan_settings = WPBS_Utils::get_settings();
						$down_payment_percent = (float)($loan_settings['loan_down_payment'] ?? 20) / 100;
						$annual_rate = (float)($loan_settings['loan_interest_rate'] ?? 7.5) / 100;
						$loan_term_years = (int)($loan_settings['loan_term_years'] ?? 1);
						$loan_term_months = $loan_term_years * 12;
						$loan_amount = $price_num * (1 - $down_payment_percent);
						$monthly_rate = $annual_rate / 12;
						if ($monthly_rate > 0) {
							$monthly_payment = $loan_amount * ($monthly_rate * pow(1 + $monthly_rate, $loan_term_months)) / (pow(1 + $monthly_rate, $loan_term_months) - 1);
						} else {
							$monthly_payment = $loan_amount / $loan_term_months;
						}
					?>
					<div class="wpbs-price-card__monthly" style="font-size:14px;color:#666;margin-top:4px;">
						Est. <strong style="color:#333;">$<?php echo number_format($monthly_payment); ?>/mo</strong>
						<span style="font-size:11px;color:#999;display:block;margin-top:2px;"><?php echo (int)($down_payment_percent * 100); ?>% down, <?php echo number_format($annual_rate * 100, 2); ?>% APR, <?php echo $loan_term_years; ?> yr<?php echo $loan_term_years > 1 ? 's' : ''; ?></span>
					</div>
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

			<!-- Loan Payment Calculator -->
			<?php if ($price_num > 0) : ?>
			<?php echo do_shortcode('[wpbs_loan_calculator post_id="' . $post_id . '"]'); ?>
			<?php endif; ?>
							
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
			if (videoUrl.match(/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/) || videoUrl.match(/youtu\.be\/([a-zA-Z0-9_-]+)/) || videoUrl.match(/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/)) {
				var vid = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]+)/)[1];
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
