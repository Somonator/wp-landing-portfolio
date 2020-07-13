<?
$link = get_post_meta($post->ID, 'portfolio-link', true);
$categories = get_the_terms($post->ID, 'portfolio-category');
$platforms = get_the_terms($post->ID, 'portfolio-platform');

if ($categories) {
	$categories_list = array_column($categories, 'name');
	$categories_list = implode(', ', $categories_list);
}

if ($platforms) {
	$platform = $platforms[0];
	$platform_icon = get_term_meta($platform->term_id, 'platform-icon', true);
}
?>

<article id="<?php the_id() ?>" <? post_class('post') ?>>
	<div class="thumbnail<? echo $link ? ' hover' : ''; ?>">
		<? if (get_the_post_thumbnail()) {
			the_post_thumbnail('full');
		} else {
			?> <img src="<? echo get_template_directory_uri() ?>/src/img/logo-placeholder.png" alt=""> <?
		}
		
		if ($link) { ?>
			<a href="<? echo $link ?>" target="_blank" class="on-hover">
				<div class="inner">
					<span class="dashicons dashicons-admin-links"></span>
				</div>
			</a>
		<? } ?>
	</div>

	<div class="content">
		<? the_title('<h2 class="title" title="' . the_title('', '', false) . '">', '</h2>');

		if ($categories) { ?>
			<div class="category"><? _e('Public in') ?> <span><? echo $categories_list ?></span></div>
		<? }
		
		if ($platforms) { ?>
			<div class="platform">
				<? if ($platform_icon) { ?>
					<img src="<? echo $platform_icon ?>" class="icon" alt="">
				<? } ?>

				<span class="name"><? echo $platform->name ?></span>
			</div>
		<? } ?>
	</div>
	
	<? if (current_user_can('administrator')) {
		echo edit_post_link(__('[edit]'), '', '', $post->ID, 'edit');
	} ?>
</article>