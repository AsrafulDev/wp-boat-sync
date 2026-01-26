<?php
/**
 * Single template for WPBS boats.
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

while (have_posts()) : the_post();
	$post_id = get_the_ID();
	$price = get_post_meta($post_id, 'wpbs_price', true);
	$length = get_post_meta($post_id, 'wpbs_length_overall', true);
	$year = get_post_meta($post_id, 'wpbs_model_year', true);
	$engine = get_post_meta($post_id, 'wpbs_engine_summary', true);
	$status = get_post_meta($post_id, 'wpbs_sales_status', true);
	$is_sold = $status && strtolower((string)$status) !== 'active';

	$gallery_ids = get_post_meta($post_id, 'wpbs_gallery_attachment_ids', true);
	if (!is_array($gallery_ids)) {
		$gallery_ids = array();
	}
	$featured_id = (int)get_post_thumbnail_id($post_id);
	if ($featured_id) {
		array_unshift($gallery_ids, $featured_id);
	}
	$gallery_ids = array_values(array_unique(array_filter(array_map('intval', $gallery_ids))));

	?><div class="wpbs-wrap" itemscope itemtype="https://schema.org/Product">
		<div class="wpbs-header">
			<h1 class="wpbs-single__title" itemprop="name"><?php the_title(); ?></h1>
			<?php if ($is_sold) : ?><span class="wpbs-badge wpbs-badge--sold">Sold</span><?php endif; ?>
		</div>

		<div class="wpbs-single">
			<div>
				<?php if (!empty($gallery_ids)) :
					$main_id = (int)$gallery_ids[0];
					$main_large = wp_get_attachment_image_url($main_id, 'large');
					$main_full = wp_get_attachment_image_url($main_id, 'full');
					$main_alt = get_post_meta($main_id, '_wp_attachment_image_alt', true);
					$main_alt = $main_alt ? $main_alt : get_the_title($post_id);
					$main_srcset = wp_get_attachment_image_srcset($main_id, 'large');
					$main_sizes = '(max-width: 900px) 100vw, 700px';
					?>
					<div class="wpbs-product-gallery" data-wpbs-gallery aria-label="Boat photos">
						<a class="wpbs-product-gallery__main" data-wpbs-main-link href="<?php echo esc_url($main_full ? $main_full : $main_large); ?>" target="_blank" rel="noopener">
							<img data-wpbs-main-img class="wpbs-product-gallery__main-img" src="<?php echo esc_url($main_large); ?>"<?php if ($main_srcset) : ?> srcset="<?php echo esc_attr($main_srcset); ?>" sizes="<?php echo esc_attr($main_sizes); ?>"<?php endif; ?> alt="<?php echo esc_attr((string)$main_alt); ?>" loading="eager" decoding="async" />
						</a>
						<div class="wpbs-product-gallery__thumbs" role="list">
							<?php foreach (array_slice($gallery_ids, 0, 18) as $i => $aid) :
								$aid = (int)$aid;
								$thumb = wp_get_attachment_image_url($aid, 'thumbnail');
								$large = wp_get_attachment_image_url($aid, 'large');
								$full = wp_get_attachment_image_url($aid, 'full');
								if (!$thumb || !$large) {
									continue;
								}
								$alt = get_post_meta($aid, '_wp_attachment_image_alt', true);
								$alt = $alt ? $alt : (get_the_title($post_id) . ' photo ' . ($i + 1));
								$srcset = wp_get_attachment_image_srcset($aid, 'large');
								?>
								<button type="button" class="wpbs-thumb<?php echo $i === 0 ? ' is-active' : ''; ?>" data-wpbs-thumb data-large="<?php echo esc_url($large); ?>" data-full="<?php echo esc_url($full ? $full : $large); ?>" data-alt="<?php echo esc_attr((string)$alt); ?>"<?php if ($srcset) : ?> data-srcset="<?php echo esc_attr($srcset); ?>" data-sizes="<?php echo esc_attr($main_sizes); ?>"<?php endif; ?> aria-label="<?php echo esc_attr(sprintf('View photo %d', $i + 1)); ?>" aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>">
									<img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy" decoding="async" />
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php elseif (has_post_thumbnail()) : ?>
					<div class="wpbs-product-gallery">
						<div class="wpbs-product-gallery__main">
							<?php the_post_thumbnail('large'); ?>
						</div>
					</div>
				<?php endif; ?>

				<div style="margin-top:18px;">
					<?php the_content(); ?>
				</div>
			</div>

			<div class="wpbs-specs">
				<?php if ($price) : ?><div class="wpbs-specs__row"><div><strong>Price</strong></div><div><?php echo esc_html($price); ?></div></div><?php endif; ?>
				<?php if ($year) : ?><div class="wpbs-specs__row"><div><strong>Year</strong></div><div><?php echo esc_html((string)$year); ?></div></div><?php endif; ?>
				<?php if ($length) : ?><div class="wpbs-specs__row"><div><strong>Length</strong></div><div><?php echo esc_html((string)$length); ?></div></div><?php endif; ?>
				<?php if ($engine) : ?><div class="wpbs-specs__row"><div><strong>Engine</strong></div><div><?php echo esc_html((string)$engine); ?></div></div><?php endif; ?>
				<div class="wpbs-specs__row"><div><strong>Status</strong></div><div><?php echo $is_sold ? 'Sold' : 'Available'; ?></div></div>
			</div>
		</div>
	</div>
<?php endwhile;

get_footer();
