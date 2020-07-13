<?
class lp_install_theme {
	function __construct() {
		add_action('after_setup_theme', [$this, 'theme_setup']);
		add_action('admin_menu', [$this, 'remove_menu_items_admin']);
		add_action('wp_before_admin_bar_render', [$this, 'admin_bar_links']);
		add_action('wp_enqueue_scripts', [$this, 'portfolio_scripts']);
	}
	
	function theme_setup() {
		add_theme_support('html5', [ 'script', 'style' ]);
		add_theme_support('post-thumbnails');
		add_theme_support('custom-logo', [
			'width' => 700,
			'height' => 250,
			'flex-width' => true,
			'flex-height' => true
		]);
		
		set_post_thumbnail_size(300, 150, false);
	}

	function admin_bar_links() {
		global $wp_admin_bar;

		$wp_admin_bar->remove_menu('wp-logo');
		$wp_admin_bar->remove_menu('about');
		$wp_admin_bar->remove_menu('wporg');
		$wp_admin_bar->remove_menu('documentation');
		$wp_admin_bar->remove_menu('support-forums');
		$wp_admin_bar->remove_menu('feedback');
		$wp_admin_bar->remove_menu('themes');
		$wp_admin_bar->remove_menu('customize');
		$wp_admin_bar->remove_menu('updates');
		$wp_admin_bar->remove_menu('comments');
		$wp_admin_bar->remove_menu('new-content');
		$wp_admin_bar->remove_menu('search');
		$wp_admin_bar->remove_menu('my-account');

		$wp_admin_bar->add_menu([
			'id'    => 'new-work',
			'title' => __('Add new work'),
			'href'  => admin_url('post-new.php?post_type=portfolio')
		]);

		// Styles button
		echo '<style>
			#wp-admin-bar-new-work > .ab-item:before {
				content: "\f132";
				top: 4px;
			}
		</style>';
	}
	
	function remove_menu_items_admin(){
		remove_menu_page('edit.php');
		remove_menu_page('edit.php?post_type=page');
		remove_menu_page('edit-comments.php');
		remove_menu_page('users.php');

		remove_submenu_page('tools.php', 'site-health.php');
		remove_submenu_page('options-general.php', 'options-writing.php');
		remove_submenu_page('options-general.php', 'options-discussion.php');
		remove_submenu_page('options-general.php', 'options-permalink.php');
		remove_submenu_page('options-general.php', 'options-privacy.php');
	}

	function portfolio_scripts() {
		wp_enqueue_style('style', get_stylesheet_uri());
		wp_enqueue_style('dashicons');

		wp_enqueue_script('jquery', true);
		wp_enqueue_script('lp-commons', get_template_directory_uri() . '/src/js/common.js', ['jquery'], null, true);
		wp_localize_script('lp-commons', 'ajax_data', [
			'url' => admin_url('/admin-ajax.php'),
			'loading_img' => get_template_directory_uri() . '/src/img/loading.gif',
			'post_count' => wp_count_posts('portfolio')->publish,
			'current_page' => get_query_var('paged') ? get_query_var('paged') : 1,
			'error_ajax' => 'Error ajax request.'
		]);
	}
}

class lp_clean_master {
	function __construct() {
		$this->delete_codes();
		add_action('wp_print_styles', [$this, 'deregister_scripts'], 100);
		add_action('wp_default_scripts', [$this, 'remove_jquery_migrate']);
		add_filter('style_loader_src', [$this, 'remove_version_scripts'], 101);
		add_filter('script_loader_src', [$this, 'remove_version_scripts'], 102);
	}
	
	function delete_codes() {
		add_filter('rest_enabled', '__return_false');
		add_filter('the_generator', '__return_empty_string');			
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('admin_print_styles', 'print_emoji_styles');
		remove_filter('the_content_feed', 'wp_staticize_emoji');
		remove_filter('comment_text_rss', 'wp_staticize_emoji');
		remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
		remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
		remove_action('wp_head', 'rest_output_link_wp_head', 10, 0);
		remove_action('template_redirect', 'rest_output_link_header', 11, 0);
		remove_action('auth_cookie_malformed', 'rest_cookie_collect_status');
		remove_action('auth_cookie_expired', 'rest_cookie_collect_status');
		remove_action('auth_cookie_bad_username', 'rest_cookie_collect_status');
		remove_action('auth_cookie_bad_hash', 'rest_cookie_collect_status');
		remove_action('auth_cookie_valid', 'rest_cookie_collect_status');
		remove_filter('rest_authentication_errors', 'rest_cookie_check_errors', 100);
		remove_action('init', 'rest_api_init');
		remove_action('rest_api_init', 'rest_api_default_filters', 10, 1);
		remove_action('parse_request', 'rest_api_loaded');
		remove_action('rest_api_init', 'wp_oembed_register_route');
		remove_filter('rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4);
		remove_action('wp_head', 'wp_oembed_add_discovery_links');
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
		remove_action('template_redirect', 'wp_shortlink_header', 11, 0);
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0 );
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
		remove_action('wp_head', 'index_rel_link');
		remove_action('wp_head', 'start_post_rel_link', 10, 0);
		remove_action('wp_head', 'parent_post_rel_link', 10, 0);
		remove_action('wp_head', 'feed_links_extra', 3);
		remove_action('wp_head', 'feed_links', 2);
		remove_action('wp_head', 'wp_resource_hints', 2);
	}
	
	function deregister_scripts() {
		wp_dequeue_style('wp-block-library');
		wp_deregister_script('wp-embed');
	}

	function remove_jquery_migrate($scripts) {
		if (!is_admin() && isset($scripts->registered['jquery'])) {
			$script = $scripts->registered['jquery'];

			if ($script->deps) {
				$script->deps = array_diff($script->deps, ['jquery-migrate']);
			}
		}
	}
	
	function remove_version_scripts($src) {
		if (strpos($src, 'ver=')) {
			$src = remove_query_arg('ver', $src);
		}
		
		return $src;
	}
}

class lp_register_portfolio_posts {
	function __construct() {
		add_action('init', [$this, 'new_post_type']);
		add_filter('manage_edit-portfolio_columns', [$this, 'add_posts_columns'], 10, 1);
		add_action('manage_posts_custom_column', [$this, 'posts_columns_inner'], 3, 1);
		add_filter('manage_edit-portfolio-platform_columns', [$this, 'add_platform_columns']);
		add_filter('manage_portfolio-platform_custom_column', [$this, 'platform_columns_inner'], 10, 3);
	}
	
	function new_post_type(){
		register_post_type('portfolio', [
			'labels' => [
				'name' => __('Portolfio'),
				'singular_name' => __('Work'),
				'add_new' => __('Add work'),
				'add_new_item' => __('Add new work'),
				'edit_item' => __('Edit'),
				'new_item' => __('New work'),
				'view_item' => __('View'),
				'search_items' => __('Find works'),
				'not_found' => __('Nothing found.'),
				'not_found_in_trash' => __('Nothing found in basket.'),
				'parent_item_colon' => '',
				'menu_name' => __('Portfolio')
			],
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 4,
			'menu_icon' => 'dashicons-images-alt2',
			'supports' => ['title', 'thumbnail']
		]);

		register_taxonomy('portfolio-category', 'portfolio', [
			'hierarchical' => true, 
			'label' => __('Category')
		]);

		register_taxonomy('portfolio-platform', 'portfolio', [
			'hierarchical' => true, 
			'label' => __('Platform')
		]);
	}
	
	function add_posts_columns($columns){
		$columns = [
			'cb' => '<input type="checkbox" />',
			'title' => __('Title'),
			'portfolio-category' => __('Category'),
			'portfolio-platform' => __('Platform'),			
			'link' => __('Link')
		];
		
		return $columns;
	}
	
	function posts_columns_inner($column) {
		global $post;
		
		switch ($column) {
			case 'portfolio-category':
				$category = get_the_terms($post->ID, 'portfolio-category');
				
				if ($category) {
					echo join(', ', 
						array_map(
							function($el) {
								return $el->name;
							},
							$category
						)
					);
				} else {
					echo '—';
				}
			break;

			case 'portfolio-platform':
				$platform = get_the_terms($post->ID, 'portfolio-platform');
				
				if ($platform) {
					echo join(', ', 
						array_map(
							function($el) {
								return $el->name;
							},
							$platform
						)
					);
				} else {
					echo '—';
				}
			break;				

			case 'link':
				$link = get_post_meta($post->ID, 'portfolio-link', true);

				echo $link ? $link : '—';
			break;
		}
	}

	function add_platform_columns($columns) {
		$column = [
			'icon' => __('Icon')
		];

		array_splice($columns, 1, 0, $column); // Set column before title

		// Styles column
		echo '<style>
			.manage-column.column-0,
			.column-0 {
				width: 60px;
				text-align: center;
			}
			.manage-column.column-0 img {
				margin: 0 auto;
				display: block;
			}
		</style>';
		
		return $columns;
	}
	
	function platform_columns_inner($content, $column_name, $term_id) {
		$platform_icon = get_term_meta($term_id, 'platform-icon', true);
		$content = '—';

		if ($column_name == 'icon' && $platform_icon) {
			$content = '<img src="' . $platform_icon . '" width="30" class="icon" alt="">';
		}

		return $content;
	}
}	

class lp_portofolio_meta_box {
	function __construct() {
		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);		
		add_action('add_meta_boxes', [$this, 'add_meta_box']);		
		add_action('init', [$this, 'terms_add_meta_box']);
		add_action('portfolio-platform_add_form_fields', [$this, 'render_meta_box_platform_icon']);
		add_action('portfolio-platform_edit_form_fields', [$this, 'edit__render_meta_box_platform_icon']);
		add_action('save_post', [$this, 'save_meta_box']);
		add_action('create_portfolio-platform', [$this, 'save_meta_box_terms']);		
		add_action('edit_portfolio-platform',   [$this, 'save_meta_box_terms']);
	}

	function admin_enqueue_scripts() {
		if (get_current_screen()->post_type == 'portfolio' && get_current_screen()->base == 'post') {
			wp_enqueue_style('lp-portfolio-link-field',  get_template_directory_uri() . '/src/css/portfolio-link-field.css');
		} else if (get_current_screen()->taxonomy == 'portfolio-platform') {
			wp_enqueue_media();
			wp_enqueue_style('lp-platform-icon-field',  get_template_directory_uri() . '/src/css/platform-icon-field.css');
			wp_enqueue_script('lp-platform-icon-field', get_template_directory_uri() . '/src/js/platform-icon-field.js', ['jquery'], null, true);
			wp_localize_script('lp-platform-icon-field', 'mf_text', [
				'title_popup' => 'Select icon, max 50x50',
				'btn_select_popup' => 'Select',
				'error_disabled_title' => 'Restricted',
				'error_disabled_text' => 'Image width must not exceed 50px.<br>Image height must not exceed 50px.',
				'btn_choose' => 'Choose',
				'btn_edit' => 'Edit'
			]);
		}
	}

	function add_meta_box($post_type) {
		if ($post_type == 'portfolio') {
			add_meta_box('portfolio-info',	__('Portfolio item'), [$this, 'render_meta_box_link'], $post_type, 'advanced', 'high');
		}
	}

	function terms_add_meta_box() {
		register_meta('term', 'platform-icon', 'sanitize_text_field');
	}

	function render_meta_box_link($post) {
		$value = get_post_meta($post->ID, 'portfolio-link', true);

		echo '<div class="portfolio-link">';
		echo '<label for="portfolio-link">' . __('Link') . '</label>';
		echo '<input type="url" id="portfolio-link" name="portfolio-link" placeholder="' . __('Enter link to the work') . '" value="' . esc_attr($value) . '">';
		echo '</div>';
	}

	function render_meta_box_platform_icon() { 
		?>
		<div id="platform-icon-wrap" class="form-field platform-icon-wrap">
			<label><? _e('Icon'); ?></label>
			<div class="inner">
				<input id="platform-icon" type="hidden" name="platform-icon">
				<div class="image" style="display: none;">
					<img id="platform-icon-img" src="#" alt="">
				</div>
				<div class="buttons">
					<a href="#" id="add-img" class="button"><? _e('Choose') ?></a>
					<a href="#" id="remove-img" class="button" style="display: none;"><? _e('Remove') ?></a>
				</div>
			</div>
		</div>
		<? 
	}

	function edit__render_meta_box_platform_icon($term) {
		$value = get_term_meta($term->term_id, 'platform-icon', true);	
		$value = sanitize_text_field($value);
		$value = esc_attr($value);
		
		?>
		<tr id="platform-icon-wrap" class="form-field platform-icon-wrap">
			<th scope="row"><label><? _e('Icon'); ?></label></th>
			<td class="inner">
				<input id="platform-icon" type="hidden" name="platform-icon" value="<? echo $value ?>">
				<div class="image" style="display: none;">
					<img id="platform-icon-img" src="<? echo $value ? $value : '#' ?>" alt="">
				</div>
				<div class="buttons">
					<a href="#" id="add-img" class="button"><? _e('Choose') ?></a>
					<a href="#" id="remove-img" class="button" style="display: none;"><? _e('Remove') ?></a>
				</div>
			</td>
		</tr>
		<? 
	}

	function save_meta_box($post_id) {
		$link = @ sanitize_text_field($_POST['portfolio-link']);

		update_post_meta($post_id, 'portfolio-link', $link);
	}

	function save_meta_box_terms($term_id) {
		$value = sanitize_text_field($_POST['platform-icon']);

		update_term_meta($term_id, 'platform-icon', $value);
	}
}
	
class lp_ajax_requests {
	function __construct() {
		add_action('wp_ajax_load_posts_terms', [$this, 'load_posts_terms']);
		add_action('wp_ajax_nopriv_load_posts_terms', [$this, 'load_posts_terms']);				
		
		add_action('wp_ajax_load_more', [$this, 'load_more']);
		add_action('wp_ajax_nopriv_load_more', [$this, 'load_more']);			
	}
	
	function load_posts_terms(){
		if (isset($_POST['tax_id']) && !empty($_POST['tax_id'])) {
			$args = [
				'post_type' => 'portfolio'
			];
			
			if ($_POST['tax_id'] > 0) {
				$tax = ['tax_query' => 
					[
						[
							'taxonomy' => 'portfolio-category',
							'field' => 'id',
							'terms' => $_POST['tax_id']
						]
					]
				];
				
				$args = array_merge($args, $tax);
			}
			
			query_posts($args);
			
			if(have_posts()) {
				if ($_POST['tax_id'] == -1) {
					$post_count = wp_count_posts('portfolio')->publish;						
				} else {
					$post_count = get_term($_POST['tax_id'])->count;
					$post_count = $post_count ? $post_count : 0;
				}

				while(have_posts()) : the_post();
					get_template_part( 'template-parts/content');
				endwhile;

				echo '<script>ajax_data.post_count = ' . $post_count . ';</script>';
			} else {
				get_template_part('template-parts/content', 'none');
			}
			
			die();
		} else {
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		}
	}
	
	function load_more(){
		if (isset($_POST['page']) && !empty($_POST['page'])) {
			$args = [
				'post_type' => 'portfolio',
				'paged' => $_POST['page'] + 1
			];
			
			if (isset($_POST['tax_id']) && !empty($_POST['tax_id']) && $_POST['tax_id'] != -1) {
				$tax = ['tax_query' => 
					[
						[
							'taxonomy' => 'portfolio-category',
							'field' => 'id',
							'terms' => $_POST['tax_id']
						]
					]
				];
				
				$args = array_merge($args, $tax);
			}
			
			query_posts($args);
			
			if(have_posts()) {
				while(have_posts()) : the_post();
					get_template_part( 'template-parts/content');
				endwhile;
			}
			
			die();				
		} else {
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		}
	}
}

/* Init theme elemetns */
new lp_install_theme();

/* Clean code */
new lp_clean_master();

/* Register portfolio like new post type */
new lp_register_portfolio_posts();

/* Add custom metabox to taxonomy platforms and works portfolio */
if (is_admin()) {
	new lp_portofolio_meta_box();
}

/* Ajax requests */
new lp_ajax_requests();
?>