<?php
/**
 * Brands index template (plugin)
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$terms = get_terms(array('taxonomy' => 'brand', 'hide_empty' => true));
?>
<div class="wpbs-wrap" style="max-width:1100px;margin:28px auto;padding:0 16px;">
	<h1>Brands</h1>
	<?php if (is_wp_error($terms) || empty($terms)) : ?>
		<p>No brands found.</p>
	<?php else: ?>
		<ul class="wpbs-brand-index" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;list-style:none;padding:0;margin:12px 0;">
			<?php foreach ($terms as $t) :
				$link = get_term_link($t);
				if (is_wp_error($link)) continue;
			?>
				<li style="background:#fff;border:1px solid #e6e6e6;padding:12px;border-radius:6px;text-align:center;">
					<a href="<?php echo esc_url($link); ?>" style="color:#0b5fff;text-decoration:none;font-weight:600;display:block;margin-bottom:6px;"><?php echo esc_html($t->name); ?></a>
					<span style="color:#666;font-size:13px;"><?php echo intval($t->count); ?> boats</span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>

<?php get_footer();
