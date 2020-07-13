<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><? echo get_bloginfo('title') . ' | ' . get_bloginfo('description') ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<? wp_head(); ?>
</head>

<body>
	<header class="header container">
		<div id="menu-trigger" class="menu-trigger"><span class="dashicons dashicons-menu"></span></div>
		<hgroup class="logo">
			<?
			if (has_custom_logo()) {
				echo the_custom_logo();
			} else {
				echo '<h1 class="title">' . get_bloginfo('name') . '</h1>';
				echo '<h2 class="sub">' . get_bloginfo('description') . '</h2>';
			}
			?>
		</hgroup>
		<nav class="main-menu container">
			<div id="menu-close" class="menu-close"><span class="dashicons dashicons-no-alt"></span></div>
			<ul class="menu">
				<li class="menu-item current" id="-1"><? _e('All works') ?></li>
				<?
				$cats = get_terms([
					'taxonomy' => 'portfolio-category',
					'hide_empty' => false
				]);
				
				if (!empty($cats)) {
					foreach ($cats as $cat) {
						echo '<li class="menu-item" id="' . $cat->term_id . '">' . $cat->name . '</li>';
					}
				}
				?>
			</ul>
		</nav>
	</header>