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
			<h1><?php echo esc_html(str_replace('Archives: ', '', get_the_archive_title())); ?></h1>
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

			// Count images
			$gallery_ids = get_post_meta($post_id, 'wpbs_gallery_attachment_ids', true);
			$photo_count = is_array($gallery_ids) ? count($gallery_ids) : 0;
			if (has_post_thumbnail()) $photo_count = max(1, $photo_count);

			// Price formatting
			$price_display = '';
			$monthly = '';
			if ($price) {
				$price_num = (float)preg_replace('/[^0-9.]/', '', $price);
				$price_display = '$' . number_format($price_num);
				if ($price_num > 5000) {
					$monthly = '$' . number_format(round($price_num * 0.009), 0) . '/mo*';
				}
			}
			?>
			<article class="wpbs-card">
				<a href="<?php the_permalink(); ?>">
					<div class="wpbs-card__media">
						<?php if (has_post_thumbnail()) : the_post_thumbnail('medium_large'); endif; ?>
						<?php if ($is_sold) : ?>
						<div class="wpbs-card__badge"><span class="wpbs-badge wpbs-badge--sold">Sold</span></div>
						<?php elseif ($condition && strtolower($condition) === 'new') : ?>
						<div class="wpbs-card__badge"><span class="wpbs-badge wpbs-badge--new">New</span></div>
						<?php endif; ?>
						<?php if ($photo_count > 0) : ?>
						<div class="wpbs-card__photo-count">
							<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
							<?php echo $photo_count; ?>
						</div>
						<?php endif; ?>
					</div>
					<div class="wpbs-card__body">
						<?php if ($price_display) : ?>
						<div class="wpbs-card__price"><?php echo esc_html($price_display); ?></div>
						<?php if ($monthly) : ?>
						<div class="wpbs-card__monthly"><?php echo esc_html($monthly); ?></div>
						<?php endif; ?>
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
				<div class="wpbs-card__actions" style="padding:0 14px 14px;">
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
