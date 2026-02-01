<?php
/**
 * Archive template for WPBS boats – BoatTrader.com exact match
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$settings = function_exists('WPBS_Utils') ? WPBS_Utils::get_settings() : array();
$cols = isset($settings['style_grid_columns']) ? (int)$settings['style_grid_columns'] : 3;
$cols = max(1, min(6, $cols));
$total = $GLOBALS['wp_query']->found_posts ?? 0;

?><div class="wpbs-wrap">
	<!-- Header Bar -->
	<div class="wpbs-archive-header">
		<div>
			<h1><?php echo(str_replace('Archives: ', '', get_the_archive_title(''))); ?></h1>
		</div>
		<div class="wpbs-archive-count"><?php echo number_format($total); ?> boats</div>
		<div class="wpbs-archive-sort">
			<span>Sort:</span>
			<select onchange="location.href=this.value">
				<option value="?orderby=date" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] === 'date'); ?>>Recommended</option>
				<option value="?orderby=price_low" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] === 'price_low'); ?>>Price: Low to High</option>
				<option value="?orderby=price_high" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] === 'price_high'); ?>>Price: High to Low</option>
				<option value="?orderby=year" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] === 'year'); ?>>Year: Newest</option>
			</select>
		</div>
	</div>

	<!-- Grid -->
	<div class="wpbs-grid" style="--wpbs-grid-columns:<?php echo esc_attr((string)$cols); ?>">
		<?php if (have_posts()) : while (have_posts()) : the_post();
			$post_id   = get_the_ID();
			$price     = get_post_meta($post_id, 'wpbs_price', true);
			$year      = get_post_meta($post_id, 'wpbs_model_year', true);
			$make      = get_post_meta($post_id, 'wpbs_make', true);
			$model     = get_post_meta($post_id, 'wpbs_model', true);
			$location  = get_post_meta($post_id, 'wpbs_location', true);
			$status    = get_post_meta($post_id, 'wpbs_sales_status', true);
			$condition = get_post_meta($post_id, 'wpbs_condition', true);
			$is_sold   = $status && strtolower((string)$status) !== 'active';

			// Build gallery from attachment IDs (up to 4 images)
			$gallery_ids = get_post_meta($post_id, 'wpbs_gallery_attachment_ids', true);
			if (!is_array($gallery_ids)) {
				$gallery_ids = array();
			}
			$featured_id = (int)get_post_thumbnail_id($post_id);
			if ($featured_id) {
				array_unshift($gallery_ids, $featured_id);
			}
			$gallery_ids  = array_values(array_unique(array_filter(array_map('intval', $gallery_ids))));
			$slider_images = array_slice($gallery_ids, 0, 4);
			$total_images = count($gallery_ids);

			// Price formatting
			$price_display = '';
			if ($price) {
				$price_num = (float)preg_replace('/[^0-9.]/', '', $price);
				$price_display = '$' . number_format($price_num);
			}
			?>
			<article class="wpbs-card">
				<div class="wpbs-card__media wpbs-card-slider" data-wpbs-card-slider>
					<a href="<?php the_permalink(); ?>" class="wpbs-card-slider__link">
						<?php if (!empty($slider_images)) : ?>
							<?php foreach ($slider_images as $idx => $img_id) :
								$img_url = wp_get_attachment_image_url($img_id, 'medium_large');
								if (!$img_url) continue;
							?>
							<div class="wpbs-card-slider__slide<?php echo $idx === 0 ? ' is-active' : ''; ?>">
								<img src="<?php echo esc_url($img_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
							</div>
							<?php endforeach; ?>
						<?php elseif (has_post_thumbnail()) : ?>
							<div class="wpbs-card-slider__slide is-active"><?php the_post_thumbnail('medium_large'); ?></div>
						<?php endif; ?>
					</a>
					<?php if (count($slider_images) > 1) : ?>
					<button type="button" class="wpbs-card-slider__nav wpbs-card-slider__nav--prev" aria-label="Previous">
						<svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
					</button>
					<button type="button" class="wpbs-card-slider__nav wpbs-card-slider__nav--next" aria-label="Next">
						<svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
					</button>
					<div class="wpbs-card-slider__dots">
						<?php foreach ($slider_images as $idx => $img_id) : ?>
						<span class="wpbs-card-slider__dot<?php echo $idx === 0 ? ' is-active' : ''; ?>"></span>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
					<?php if ($is_sold) : ?>
					<div class="wpbs-card__badge"><span class="wpbs-badge wpbs-badge--sold">Sold</span></div>
					<?php elseif ($condition && strtolower($condition) === 'new') : ?>
					<div class="wpbs-card__badge"><span class="wpbs-badge wpbs-badge--new">New</span></div>
					<?php endif; ?>
					<?php if ($total_images > 0) : ?>
					<div class="wpbs-card__photo-count">
						<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
						<?php echo $total_images; ?>
					</div>
					<?php endif; ?>
				</div>
				<a href="<?php the_permalink(); ?>" class="wpbs-card__body-link">
					<div class="wpbs-card__body">
						<?php if ($price_display) : ?>
						<div class="wpbs-card__price"><?php echo esc_html($price_display); ?></div>
						<?php else : ?>
						<div class="wpbs-card__price">Contact for Price</div>
						<?php endif; ?>
						<h2 class="wpbs-card__title"><?php the_title(); ?></h2>
						<?php if ($location) : ?>
						<div class="wpbs-card__location">
							<svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
							<?php echo esc_html($location); ?>
						</div>
						<?php endif; ?>
					</div>
				</a>
				<div class="wpbs-card__actions">
					<a href="<?php the_permalink(); ?>#contact" class="wpbs-card__btn wpbs-card__btn--primary">Contact Seller</a>
				</div>
			</article>
		<?php endwhile; else : ?>
			<div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background:#fff; border-radius:8px;">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="#ccc" style="margin-bottom:12px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
				<p style="font-size: 16px; color: #666; margin:0 0 16px;">No boats found.</p>
				<a href="<?php echo esc_url(home_url('/')); ?>" class="wpbs-btn wpbs-btn--primary" style="display:inline-flex;">Back to Home</a>
			</div>
		<?php endif; ?>
	</div>

	<?php if (function_exists('the_posts_pagination')) : ?>
	<nav style="margin-top: 24px; text-align: center;">
		<?php the_posts_pagination(array(
			'mid_size'  => 2,
			'prev_text' => '← Previous',
			'next_text' => 'Next →',
		)); ?>
	</nav>
	<?php endif; ?>
</div>
<?php

get_footer();
