<?php
/**
 * Archive template for WPBS boats.
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$settings = function_exists('WPBS_Utils') ? WPBS_Utils::get_settings() : array();
$cols = isset($settings['style_grid_columns']) ? (int)$settings['style_grid_columns'] : 3;
$cols = max(1, min(6, $cols));

?><div class="wpbs-wrap">
	<div class="wpbs-header">
		<h1><?php echo esc_html(get_the_archive_title()); ?></h1>
	</div>

	<div class="wpbs-grid" style="--wpbs-grid-columns:<?php echo esc_attr((string)$cols); ?>">
		<?php if (have_posts()) : while (have_posts()) : the_post();
			$post_id = get_the_ID();
			$price = get_post_meta($post_id, 'wpbs_price', true);
			$year = get_post_meta($post_id, 'wpbs_model_year', true);
			$make = get_post_meta($post_id, 'wpbs_make', true);
			$model = get_post_meta($post_id, 'wpbs_model', true);
			$status = get_post_meta($post_id, 'wpbs_sales_status', true);
			$is_sold = $status && strtolower((string)$status) !== 'active';
			?>
			<article class="wpbs-card" itemscope itemtype="https://schema.org/Product">
				<a href="<?php the_permalink(); ?>">
					<div class="wpbs-card__media">
						<?php if (has_post_thumbnail()) : the_post_thumbnail('medium_large'); endif; ?>
					</div>
					<div class="wpbs-card__body">
						<h2 class="wpbs-card__title" itemprop="name"><?php the_title(); ?></h2>
						<div class="wpbs-card__meta"><?php echo esc_html(trim($year . ' ' . $make . ' ' . $model)); ?></div>
						<?php if ($price) : ?><div class="wpbs-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer"><span itemprop="priceCurrency" content="USD"></span><span itemprop="price"><?php echo esc_html($price); ?></span></div><?php endif; ?>
						<?php if ($is_sold) : ?><div style="margin-top:10px;"><span class="wpbs-badge wpbs-badge--sold">Sold</span></div><?php endif; ?>
					</div>
				</a>
			</article>
		<?php endwhile; else : ?>
			<p>No boats found.</p>
		<?php endif; ?>
	</div>
</div>
<?php

get_footer();
