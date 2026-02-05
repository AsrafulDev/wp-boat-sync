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
$max_pages = $GLOBALS['wp_query']->max_num_pages ?? 1;

// Get filter options
$filter_options = WPBS_Plugin::get_filter_options();

// Get current filter values from URL
$f_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$f_builder = isset($_GET['builder']) ? sanitize_text_field($_GET['builder']) : '';
$f_location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';
$f_length_min = isset($_GET['length_min']) ? (float)$_GET['length_min'] : '';
$f_length_max = isset($_GET['length_max']) ? (float)$_GET['length_max'] : '';
$f_year_min = isset($_GET['year_min']) ? (int)$_GET['year_min'] : '';
$f_year_max = isset($_GET['year_max']) ? (int)$_GET['year_max'] : '';
$f_price_min = isset($_GET['price_min']) ? (float)$_GET['price_min'] : '';
$f_price_max = isset($_GET['price_max']) ? (float)$_GET['price_max'] : '';
$f_condition_new = isset($_GET['condition_new']) && $_GET['condition_new'] === '1';
$f_condition_used = isset($_GET['condition_used']) && $_GET['condition_used'] === '1';
$f_featured = isset($_GET['featured']) && $_GET['featured'] === '1';
$f_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date';

// Default both conditions checked if neither specified
if (!$f_condition_new && !$f_condition_used) {
	$f_condition_new = true;
	$f_condition_used = true;
}

// If viewing a brand taxonomy archive, preselect the builder filter and mark brand page
$brand_page = false;
if (is_tax('brand')) {
	$term = get_queried_object();
	if ($term && !empty($term->name)) {
		if ($f_builder === '') {
			$f_builder = (string)$term->name;
		}
		$brand_page = true;
	}
}

// Get range limits from database
$len_min = max(0, (int)($filter_options['lengths']['min'] ?? 0));
$len_max = max($len_min + 10, (int)($filter_options['lengths']['max'] ?? 200));
$yr_min = max(1900, (int)($filter_options['years']['min'] ?? 1990));
$yr_max = max($yr_min + 1, (int)($filter_options['years']['max'] ?? date('Y')));
$pr_min = max(0, (int)($filter_options['prices']['min'] ?? 0));
$pr_max = max($pr_min + 1000, (int)($filter_options['prices']['max'] ?? 5000000));

// Current slider values
$cur_length_min = $f_length_min !== '' ? (int)$f_length_min : $len_min;
$cur_length_max = $f_length_max !== '' ? (int)$f_length_max : $len_max;
$cur_year_min = $f_year_min !== '' ? (int)$f_year_min : $yr_min;
$cur_year_max = $f_year_max !== '' ? (int)$f_year_max : $yr_max;
$cur_price_min = $f_price_min !== '' ? (int)$f_price_min : $pr_min;
$cur_price_max = $f_price_max !== '' ? (int)$f_price_max : $pr_max;

// Price format helper
function wpbs_format_price_short($price) {
	$price = (float)$price;
	if ($price >= 1000000) {
		return '$' . number_format($price / 1000000, 1) . 'M';
	} elseif ($price >= 1000) {
		return '$' . number_format($price / 1000, 0) . 'K';
	}
	return '$' . number_format($price);
}

?><div class="wpbs-wrap wpbs-filter-layout--top" data-wpbs-filter-container data-posts-per-page="<?php echo esc_attr((string)get_option('posts_per_page', 12)); ?>" data-columns="<?php echo esc_attr((string)$cols); ?>">
	
	<!-- Filter Bar -->
	<div class="wpbs-filter-bar">
		<div class="wpbs-filter-bar__row">
			<div class="wpbs-filter-bar__field">
				<label>Category</label>
				<select name="category" data-wpbs-filter="category">
					<option value="">Any Categories</option>
					<?php foreach ($filter_options['categories'] as $cat) : ?>
					<option value="<?php echo esc_attr($cat); ?>" <?php selected($f_category, $cat); ?>><?php echo esc_html($cat); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="wpbs-filter-bar__field">
				<label>Builder</label>
				<select name="builder" data-wpbs-filter="builder" onchange="(function(){var b=document.querySelector('[data-wpbs-filter-submit]'); if(b) b.click();})();">
					<option value="">Any Builder</option>
					<?php foreach ($filter_options['builders'] as $builder) : ?>
					<option value="<?php echo esc_attr($builder); ?>" <?php selected($f_builder, $builder); ?>><?php echo esc_html($builder); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="wpbs-filter-bar__field">
				<label>Location</label>
				<select name="location" data-wpbs-filter="location">
					<option value="">Any Location</option>
					<?php foreach ($filter_options['locations'] as $loc) : ?>
					<option value="<?php echo esc_attr($loc); ?>" <?php selected($f_location, $loc); ?>><?php echo esc_html($loc); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="wpbs-filter-bar__field wpbs-filter-bar__field--action">
				<button type="button" class="wpbs-filter-bar__search" data-wpbs-filter-submit>Search</button>
			</div>
		</div>

		<!-- Range Sliders Row -->
		<div class="wpbs-filter-bar__row wpbs-filter-bar__row--sliders">
			<!-- Length Range Slider -->
			<div class="wpbs-filter-bar__field wpbs-filter-bar__field--slider">
				<label>Length (ft)</label>
				<div class="wpbs-range" data-wpbs-range="length">
					<div class="wpbs-range__values">
						<span class="wpbs-range__value--min"><?php echo esc_html($cur_length_min); ?> ft</span>
						<span class="wpbs-range__value--max"><?php echo esc_html($cur_length_max); ?> ft</span>
					</div>
					<div class="wpbs-range__slider">
						<div class="wpbs-range__track-bg"></div>
						<div class="wpbs-range__track"></div>
						<input type="range" class="wpbs-range__input wpbs-range__input--min" min="<?php echo esc_attr($len_min); ?>" max="<?php echo esc_attr($len_max); ?>" value="<?php echo esc_attr($cur_length_min); ?>" step="1">
						<input type="range" class="wpbs-range__input wpbs-range__input--max" min="<?php echo esc_attr($len_min); ?>" max="<?php echo esc_attr($len_max); ?>" value="<?php echo esc_attr($cur_length_max); ?>" step="1">
					</div>
					<input type="hidden" name="length_min" data-wpbs-filter="length_min" data-wpbs-range-min value="<?php echo esc_attr($f_length_min); ?>">
					<input type="hidden" name="length_max" data-wpbs-filter="length_max" data-wpbs-range-max value="<?php echo esc_attr($f_length_max); ?>">
				</div>
			</div>

			<!-- Year Range Slider -->
			<div class="wpbs-filter-bar__field wpbs-filter-bar__field--slider">
				<label>Year</label>
				<div class="wpbs-range" data-wpbs-range="year">
					<div class="wpbs-range__values">
						<span class="wpbs-range__value--min"><?php echo esc_html($cur_year_min); ?></span>
						<span class="wpbs-range__value--max"><?php echo esc_html($cur_year_max); ?></span>
					</div>
					<div class="wpbs-range__slider">
						<div class="wpbs-range__track-bg"></div>
						<div class="wpbs-range__track"></div>
						<input type="range" class="wpbs-range__input wpbs-range__input--min" min="<?php echo esc_attr($yr_min); ?>" max="<?php echo esc_attr($yr_max); ?>" value="<?php echo esc_attr($cur_year_min); ?>" step="1">
						<input type="range" class="wpbs-range__input wpbs-range__input--max" min="<?php echo esc_attr($yr_min); ?>" max="<?php echo esc_attr($yr_max); ?>" value="<?php echo esc_attr($cur_year_max); ?>" step="1">
					</div>
					<input type="hidden" name="year_min" data-wpbs-filter="year_min" data-wpbs-range-min value="<?php echo esc_attr($f_year_min); ?>">
					<input type="hidden" name="year_max" data-wpbs-filter="year_max" data-wpbs-range-max value="<?php echo esc_attr($f_year_max); ?>">
				</div>
			</div>

			<!-- Price Range Slider -->
			<div class="wpbs-filter-bar__field wpbs-filter-bar__field--slider">
				<label>Price</label>
				<div class="wpbs-range" data-wpbs-range="price">
					<div class="wpbs-range__values">
						<span class="wpbs-range__value--min"><?php echo wpbs_format_price_short($cur_price_min); ?></span>
						<span class="wpbs-range__value--max"><?php echo wpbs_format_price_short($cur_price_max); ?></span>
					</div>
					<div class="wpbs-range__slider">
						<div class="wpbs-range__track-bg"></div>
						<div class="wpbs-range__track"></div>
						<input type="range" class="wpbs-range__input wpbs-range__input--min" min="<?php echo esc_attr($pr_min); ?>" max="<?php echo esc_attr($pr_max); ?>" value="<?php echo esc_attr($cur_price_min); ?>" step="5000">
						<input type="range" class="wpbs-range__input wpbs-range__input--max" min="<?php echo esc_attr($pr_min); ?>" max="<?php echo esc_attr($pr_max); ?>" value="<?php echo esc_attr($cur_price_max); ?>" step="5000">
					</div>
					<input type="hidden" name="price_min" data-wpbs-filter="price_min" data-wpbs-range-min value="<?php echo esc_attr($f_price_min); ?>">
					<input type="hidden" name="price_max" data-wpbs-filter="price_max" data-wpbs-range-max value="<?php echo esc_attr($f_price_max); ?>">
				</div>
			</div>
		</div>

		<!-- Checkboxes Row -->
		<div class="wpbs-filter-bar__row wpbs-filter-bar__row--secondary">
			<div class="wpbs-filter-bar__checkboxes">
				<label class="wpbs-filter-bar__checkbox">
					<input type="checkbox" name="condition_new" data-wpbs-filter="condition_new" value="1" <?php checked($f_condition_new); ?>>
					<span>New</span>
				</label>
				<label class="wpbs-filter-bar__checkbox">
					<input type="checkbox" name="condition_used" data-wpbs-filter="condition_used" value="1" <?php checked($f_condition_used); ?>>
					<span>Used</span>
				</label>
				<label class="wpbs-filter-bar__checkbox">
					<input type="checkbox" name="featured" data-wpbs-filter="featured" value="1" <?php checked($f_featured); ?>>
					<span>Featured Listings</span>
				</label>
			</div>
			<button type="button" class="wpbs-filter-bar__clear" data-wpbs-filter-clear>Clear Filters</button>
		</div>
	</div>

	<!-- Filter Content -->
	<div class="wpbs-filter-content">

	<!-- Header Bar -->
	<div class="wpbs-archive-header">
		<div>
			<?php
			if (is_tax('brand')) {
				$term = get_queried_object();
				if ($term && !empty($term->name)) {
					echo '<h1>Brand: ' . esc_html($term->name) . '</h1>';
				} else {
					echo '<h1>' . esc_html(str_replace('Archives: ', '', get_the_archive_title(''))) . '</h1>';
				}
			} else {
				echo '<h1>' . esc_html(str_replace('Archives: ', '', get_the_archive_title(''))) . '</h1>';
			}
			?>
		</div>
		<div class="wpbs-archive-count" data-wpbs-total-count><?php echo number_format($total); ?> boats</div>
		<div class="wpbs-archive-sort">
			<span>Sort:</span>
			<select data-wpbs-filter="orderby">
				<option value="date" <?php selected($f_orderby, 'date'); ?>>Recommended</option>
				<option value="price_low" <?php selected($f_orderby, 'price_low'); ?>>Price: Low to High</option>
				<option value="price_high" <?php selected($f_orderby, 'price_high'); ?>>Price: High to Low</option>
				<option value="year" <?php selected($f_orderby, 'year'); ?>>Year: Newest</option>
			</select>
		</div>
	</div>

	<!-- Loading Overlay -->
	<div class="wpbs-filter-loading" data-wpbs-filter-loading style="display:none;">
		<div class="wpbs-filter-loading__spinner"></div>
		<span>Loading boats...</span>
	</div>

	<!-- Grid -->
	<div class="wpbs-grid" data-wpbs-grid style="--wpbs-grid-columns:<?php echo esc_attr((string)$cols); ?>">
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
			<div class="wpbs-no-results" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background:#fff; border-radius:8px;">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="#ccc" style="margin-bottom:12px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
				<p style="font-size: 16px; color: #666; margin:0 0 16px;">No boats found.</p>
				<a href="<?php echo esc_url(home_url('/')); ?>" class="wpbs-btn wpbs-btn--primary" style="display:inline-flex;">Back to Home</a>
			</div>
		<?php endif; ?>
	</div>

	<!-- AJAX Pagination -->
	<nav class="wpbs-pagination" data-wpbs-pagination data-max-pages="<?php echo esc_attr((string)$max_pages); ?>" data-current-page="1" style="margin-top: 24px; text-align: center;">
		<?php if ($max_pages > 1) : ?>
		<button type="button" class="wpbs-pagination__btn wpbs-pagination__btn--prev" data-wpbs-page="prev" disabled>← Previous</button>
		<span class="wpbs-pagination__info">Page 1 of <?php echo esc_html((string)$max_pages); ?> (<?php echo number_format($total); ?> boats)</span>
		<button type="button" class="wpbs-pagination__btn wpbs-pagination__btn--next" data-wpbs-page="next" <?php echo $max_pages <= 1 ? 'disabled' : ''; ?>>Next →</button>
		<?php endif; ?>
	</nav>

	</div><!-- End filter-content -->
</div><!-- End wpbs-wrap -->
<?php

get_footer();
