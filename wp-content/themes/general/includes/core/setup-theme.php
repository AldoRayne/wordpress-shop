<?php

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 *
 * @return void
 */
function che_general_setup(): void {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Start-theme, use a find and replace
	 * to change 'general' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'general', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	] );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * By default, you can align blocks to the left, right or center. This adds two additional
	 * options – wide and full – to the editor block controls. To utilize them on the site
	 * frontend you need to add corresponding styles for these new generated classes.
	 *
	 * [ .alignleft | .aligncenter | .alignright | .alignwide | .alignfull ]
	 *
	 * @link https://www.billerickson.net/full-and-wide-alignment-in-gutenberg/
	 */
	add_theme_support( 'align-wide' );

	/**
	 * When editing paragraph text, a user can select different sizes in the settings panel.
	 * This lets you define what sizes should be available, adds a CSS class like
	 * .has-{size-slug}-font-size.
	 */
	add_theme_support( 'editor-font-sizes', che_general_config( 'editor_font_sizes' ) );

	/**
	 * And disable settings any custom font size too.
	 */
	add_theme_support( 'disable-custom-font-sizes' );

	/**
	 * Define a few brand colors that make a custom color palette. This adds a CSS class like
	 * .has-{color-slug}-background-color and .has-{color-slug}-color.
	 */
	add_theme_support( 'editor-color-palette', che_get_theme_colors() );

	/**
	 * And disable color picker.
	 */
	add_theme_support( 'disable-custom-colors' );

	/**
	 * Configure intermediate image sizes.
	 */
	$images_sizes = che_general_config( 'image_sizes' );
	if ( $images_sizes && is_array( $images_sizes ) ) {
		foreach ( $images_sizes as $item ) {
			if ( ! empty( $item['width'] ) && ! empty( $item['height'] ) ) {
				add_image_size( $item['name'], (int) $item['width'], (int) $item['height'], ( $item['crop'] ?: false ) );
			}
		}
	}

	/**
	 * Registering menus
	 */
	register_nav_menus( che_general_config( 'register_nav_menus' ) );
}

add_action( 'after_setup_theme', 'che_general_setup' );

/**
 * Adjusts WordPress image quality.
 */
add_filter( 'jpeg_quality', static function () {
	return che_general_config( 'jpeg_quality' );
} );

/**
 * Custom Excerpts for posts and pages.
 *
 * @package CHE
 * @param int $length
 * @return int
 */
add_filter( 'excerpt_length', function ( $length ) {
	$new_length = che_general_config( 'excerpt_length' );
	return is_int( $new_length ) ? $new_length : $length;
}, 999 );

/**
 * Custom Excerpt more for posts and pages
 *
 * @param string $more
 *
 * @return string
 */
add_filter( 'excerpt_more', function ( $more ) {
	$new_more = che_general_config( 'excerpt_more' );
	return is_string( $new_more ) ? $new_more : $more;
} );

/**
 * Disable the emoji support.
 *
 * @return void
 */
function che_general_disable_emojis(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', 'che_general_disable_emojis_tinymce' );
	add_filter( 'wp_resource_hints', 'che_general_disable_emojis_remove_dns_prefetch', 10, 2 );
}

add_action( 'init', 'che_general_disable_emojis' );

/**
 * Filter function used to remove the TinyMCE emoji plugin.
 *
 * @param array<int, string> $plugins
 *
 * @return array<int, string> Difference between the two arrays
 */
function che_general_disable_emojis_tinymce( $plugins ): array {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, [ 'wpemoji' ] );
	} else {
		return [];
	}
}

/**
 * Remove emoji CDN hostname prefetch from resource hints.
 *
 * @param array<int, string>  $urls          URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 *
 * @return array<int, string> Difference between the two arrays.
 */
function che_general_disable_emojis_remove_dns_prefetch( array $urls, string $relation_type ): array {
	if ( 'dns-prefetch' === $relation_type ) {
		/** This filter is documented in wp-includes/formatting.php */
		$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
		if ( is_string( $emoji_svg_url ) ) {
			$urls = array_diff( $urls, [ $emoji_svg_url ] );
		}
	}

	return $urls;
}

/**
 * Removes the width and height attributes of <img> tags for SVG
 *
 * Without this filter, the width and height are set to "1" since
 * WordPress core can't seem to figure out an SVG file's dimensions.
 *
 * For SVG:s, returns an array with file url, width and height set
 * to null, and false for 'is_intermediate'.
 *
 * @wp-hook image_downsize
 *
 * @param bool|array<int, mixed> $out Value to be filtered
 * @param int   $id  Attachment ID for image.
 *
 * @return bool|array<int, mixed> False if not in admin or not SVG. Array otherwise.
 */
function che_general_fix_svg_size_attributes( $out, int $id ) {
	$image_url = wp_get_attachment_url( $id );
	if ( false === $image_url ) {
		return $out;
	}
	$file_ext  = pathinfo( $image_url, PATHINFO_EXTENSION );

	if ( is_admin() || 'svg' !== $file_ext ) {
		return $out;
	}

	return [
		$image_url,
		null,
		null,
		false,
	];
}

add_filter( 'image_downsize', 'che_general_fix_svg_size_attributes', 10, 2 );

/**
 * Allow to upload and recognize SVG files as valid file type.
 *
 * @param array $mimes
 *
 * @return array
 */
add_filter( 'upload_mimes', static function ( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
} );

/**
 * Remove previous and next links from head and SQL queries used to generate them.
 */
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

/**
 * Add scripts after open tag <body> from admin panel
 */
add_action( 'wp_body_open', function () {
	if ( function_exists( 'get_field' ) ) {
		echo get_field( 'scripts_body', 'options' );
	}
} );

/**
 * Add scripts before close tag </head> from admin panel
 */
add_action( 'wp_head', function () {
	if ( function_exists( 'get_field' ) ) {
		echo get_field( 'scripts_head', 'options' );
	}
} );

/**
 * Add scripts before close </body> from admin panel
 */
add_action( 'wp_footer', function () {
	if ( function_exists( 'get_field' ) ) {
		echo get_field( 'scripts_footer', 'options' );
	}
} );

/**
 * Ensure post-type meta boxes are checked by default in the nav-menus.php screen.
 */
function che_set_metaboxes_checked_by_default(): void {
	// Ensure this runs only on the 'nav-menus' screen
	if ( ( $screen = get_current_screen() ) && $screen->id === 'nav-menus' ) {

		// Get the current user ID and define the option keys
		$user_id         = get_current_user_id();
		$user_option_key = 'custom_metaboxhidden_nav_menus_' . $user_id;

		// Check if the user already modified the metabox visibility
		if ( get_user_option( $user_option_key ) ) {
			return; // Exit early to prevent unnecessary updates
		}

		// Get all public post types
		$post_types = get_post_types( [ 'public' => true ], 'names' );

		// Retrieve hidden metaboxes for the nav-menus screen or set to an empty array
		$hidden = get_user_option( 'metaboxhidden_nav-menus' ) ?? [];

		if ( ! is_array( $hidden ) ) {
			$hidden = [];
		}

		// Ensure post type metaboxes are not hidden
		$updated_hidden = array_filter( $hidden, function ( $meta_box_id ) use ( $post_types ) {
			foreach ( $post_types as $post_type ) {
				$post_type_name = $post_type instanceof \WP_Post_Type ? $post_type->name : $post_type;
				if ( $meta_box_id === 'add-post-type-' . sanitize_key( $post_type_name ) ) {
					return false; // Exclude from the new hidden array
				}
			}

			return true; // Keep other hidden items intact
		} );

		// Update the user's hidden metaboxes if changes were made
		if ( $updated_hidden !== $hidden ) {
			update_user_option( $user_id, 'metaboxhidden_nav-menus', $updated_hidden, true );
		}

		// Mark the metabox state as modified for this user to avoid repeated updates
		update_user_option( $user_id, $user_option_key, true );
	}
}

// Hook the function to the 'current_screen' action
add_action( 'current_screen', 'che_set_metaboxes_checked_by_default' );

/**
 * Retrieve the theme colors from scss file.
 *
 * This function returns an array of colors used in the theme.
 *
 * @return array<int, array<string, string|null>> An associative array of theme colors.
 */
function che_get_theme_colors(): array {
	$default_colors = array(
		array(
			'name'  => __( 'Primary Blue', 'general' ),
			'slug'  => 'primary-blue',
			'color' => '#008dcd',
		),
		array(
			'name'  => __( 'Dark Blue', 'general' ),
			'slug'  => 'dark-blue',
			'color' => '#00355d',
		),
	);
	$source         = THEME_FRONTEND_DIR . '/src/styles/common/_root.scss';

	if ( ! file_exists( $source ) ) {
		return $default_colors;
	}

	$lines = file( $source, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	if ( $lines === false ) {
		error_log( 'Error reading file: ' . $source );

		return $default_colors;
	}

	$colors = array();

	foreach ( $lines as $line ) {
		if ( str_contains( $line, '--color' ) ) {
			$color = explode( ':', $line );
			if ( count( $color ) < 2 ) {
				continue;
			}

			$slug  = trim( str_replace( '--color-', '', $color[0] ) );
			$name  = ucfirst( trim( str_replace( '-', ' ', $slug ) ) );
			$value = trim( str_replace( ';', '', $color[1] ) );

			$colors[] = [
				'name'  => sanitize_text_field( $name ),
				'slug'  => sanitize_text_field( $slug ),
				'color' => sanitize_hex_color( $value )
			];
		}
	}

	return $colors;
}

/**
 * Add all ACF options pages to the admin toolbar submenu under "Customize".
 *
 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
 */
function che_add_toolbar_options_pages( WP_Admin_Bar $wp_admin_bar ): void {

	// Exit if ACF options pages function does not exist or user is not logged in.
	if ( ! function_exists( 'acf_get_options_pages' ) || ! is_user_logged_in() ) {
		return;
	}

	// Retrieve all registered ACF options pages.
	$options_pages = acf_get_options_pages();

	if ( empty( $options_pages ) || ! is_array( $options_pages ) ) {
		return;
	}

	// Organize options pages into parents and their children.
	$parent_pages = [];
	foreach ( $options_pages as $option_page ) {
		if ( empty( $option_page['menu_slug'] ) || empty( $option_page['menu_title'] ) ) {
			continue;
		}

		if ( empty( $option_page['parent_slug'] ) ) {
			// Parent page.
			$parent_pages[ $option_page['menu_slug'] ] = $option_page;
		} else {
			// Child page.
			$parent_pages[ $option_page['parent_slug'] ]['children'][] = $option_page;
		}
	}

	// Add parent and child pages to the admin toolbar.
	foreach ( $parent_pages as $parent_page ) {
		if ( ! isset( $parent_page['menu_title'], $parent_page['menu_slug'] ) ) {
			continue;
		}

		// Add the parent page.
		$wp_admin_bar->add_node( [
			'id'     => 'che_option_' . sanitize_title( $parent_page['menu_title'] ),
			'title'  => $parent_page['menu_title'],
			'href'   => admin_url( 'admin.php?page=' . $parent_page['menu_slug'] ),
			'parent' => 'customize',
		] );

		// Add child pages (if any).
		if ( ! empty( $parent_page['children'] ) ) {
			foreach ( $parent_page['children'] as $child_page ) {
				if ( ! empty( $child_page['menu_title'] ) && ! empty( $child_page['menu_slug'] ) ) {
					$wp_admin_bar->add_node( [
						'id'     => 'che_option_' . sanitize_title( $child_page['menu_title'] ),
						'title'  => ' - ' . $child_page['menu_title'],
						'href'   => admin_url( 'admin.php?page=' . $child_page['menu_slug'] ),
						'parent' => 'customize',
					] );
				}
			}
		}
	}
}

add_action( 'admin_bar_menu', 'che_add_toolbar_options_pages', 999 );

/**
 * Adding an "Image" column to the list page of posts of all types.
 *
 * @param array<string, string> $columns
 * @param string $post_type
 * @return array<string, string>
 */
function che_add_thumbnail_column_to_posts( array $columns, string $post_type ): array {

	// Checking if it is woocommerce posts list or doesn't support thumbnail.
	if ( in_array( 'thumb', array_keys( $columns ) ) || 'product' === $post_type || ( ! empty( $post_type ) && ! post_type_supports( $post_type, 'thumbnail' ) ) ) {
		return $columns;
	}

	$title_column_index = array_search( 'title', array_keys( $columns ) );
	$before_columns     = array_slice( $columns, 0, $title_column_index !== false ? $title_column_index : null, true );
	$after_columns      = array_slice( $columns, $title_column_index !== false ? $title_column_index : 0, null, true );

	$new_columns = [
		'thumbnail' => '<span class="dashicons dashicons-format-image"></span>',
	];


	return $before_columns + $new_columns + $after_columns;
}

/**
 * @param string $column
 * @param int $post_id
 * @return void
 */
function che_add_thumbnail_column_content_to_posts( string $column, int $post_id ): void {
	if ( $column !== 'thumbnail' ) {
		return;
	}

	$thumbnail_id = get_post_thumbnail_id( $post_id );
	$link_content = wp_get_attachment_image( (int) $thumbnail_id, array( 40, 40 ) );

	if ( ! has_post_thumbnail( $post_id ) ) {
		$link_content = '<span class="dashicons dashicons-format-image"></span>';
	}

	printf( '<a href="%s">%s</a>', esc_url( (string) get_edit_post_link( $post_id ) ), $link_content );
}

add_filter( 'manage_posts_columns', 'che_add_thumbnail_column_to_posts', 99, 2 );
add_action( 'manage_posts_custom_column', 'che_add_thumbnail_column_content_to_posts', 99, 2 );
add_filter( 'manage_page_posts_columns', function ( $columns ) {
	return che_add_thumbnail_column_to_posts( $columns, 'page' );
}, 99, 1 );
add_action( 'manage_page_posts_custom_column', 'che_add_thumbnail_column_content_to_posts', 99, 2 );