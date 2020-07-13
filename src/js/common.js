jQuery(document).ready(function($) {
	let $menu = $('.main-menu'),
		$m_menu_trigger = $('#menu-trigger'),
		$m_menu_close = $menu.find('#menu-close'),
		$menu_items = $menu.find('.menu-item'),
		$portfolio = $('#portfolio-list'),
		$more_posts = $('#more-post'),
		loading_html = $('<div class="load-animation"><img src="' + ajax_data.loading_img + '" alt=""></div>'),
		tax_id;

	/* Fixex menu on the desktop */
	if ($(window).width() > 1000) {
		let m_offset = $menu.offset();
		
		$(document).scroll(function() {
			if ($(window).scrollTop() > m_offset.top) {
				$menu.addClass('fixed');
			} else {
				$menu.removeClass('fixed');
			}
		});
	}
		
	/* Menu on mobile */
	$($m_menu_trigger).add($m_menu_close).click(function(event) {
		event.preventDefault();

		$('body').toggleClass('overflow-hidden overlay');				
		$menu.toggleClass('open');
	});

	/* Category ajax requests */
	$menu_items.click(function(event) {
		event.preventDefault();

		let menu_item = $(this);

		if (!$portfolio.hasClass('loading')) {
			$.ajax({
				type:'POST',
				url: ajax_data.url, 
				data: {
					'action': 'load_posts_terms',
					'tax_id': menu_item.attr('id'),
				},
				beforeSend: function() {
					$more_posts.remove();
					$portfolio.addClass('loading');
					$portfolio.html(loading_html);

					if ($menu.hasClass('open')) {
						$m_menu_close.click();
					}					
				},
				success: function(data) {
					$portfolio.html(data);
					$portfolio.removeClass('loading');

					menu_item.addClass('current').siblings().removeClass('current');

					tax_id = menu_item.attr('id');
					ajax_data.current_page = 1;

					if ($portfolio.find('.post').length > 0 && $portfolio.find('.post').length < ajax_data.post_count) {
						$portfolio.after($more_posts);
						$more_posts.show();
					} else {
						$more_posts.remove();
					}
				},
				error: function () {
					alert(ajax_data.error_ajax);
				}
			});
		}
	});

	/* Works ajax requests */
	$(document).delegate('#more-post', 'click', function(event) {
		event.preventDefault();

		let post_count_now = $portfolio.find('.post').length;
		
		if (post_count_now < ajax_data.post_count && !$portfolio.hasClass('loading')) {
			$.ajax({
				type:'POST',
				url: ajax_data.url, 
				data: {
					'action': 'load_more',
					'tax_id' : tax_id,
					'page' : ajax_data.current_page
				}, 
				beforeSend: function() {
					$portfolio.addClass('loading');			
					$portfolio.append(loading_html);
				},
				success: function(data) {
					$portfolio.append(data);
					$portfolio.removeClass('loading');
					$portfolio.find('.load-animation').remove();
					ajax_data.current_page++;

					if ($portfolio.find('.post').length  == ajax_data.post_count) {
						$more_posts.remove();
					}
				},
				error: function () {
					alert(ajax_data.error_ajax);
				}
			}); 
		}	
	});	
});