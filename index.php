<?
get_header();

$portfolio = new WP_Query([
	'post_type' => 'portfolio'
]);
$display_more = wp_count_posts('portfolio')->publish > get_option('posts_per_page') ? '' : 'style="display:none;"';
?>

<main class="main container">
	<div id="portfolio-list" class="portfolio-list">
		<?
		if ($portfolio->have_posts()) {
			while ($portfolio->have_posts()) : $portfolio->the_post();
				get_template_part('template-parts/content');
			endwhile;
		} else {
			get_template_part('template-parts/content', 'none');
		}
		
		wp_reset_postdata();
		?>		
	</div>

	<div id="more-post" class="more-post"<? echo $display_more ?>><? _e('More works') ?></div>
</main>

<? get_footer() ?>