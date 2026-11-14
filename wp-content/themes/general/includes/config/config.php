<?php
/**
 * Globals config WordPress
 *
 * @param string $key
 *
 * @return mixed
 */
function che_general_config( string $key ): mixed {
	$config = array(

		/**
		 * Allowlisted WP admins
		 *
		 * Allows seeing all plugins and functionality in the backend
		 *
		 * If a 'vars' subarray is declared for a js script in the $config['wp_enqueue_script'] array,
		 * the handle value must not have '-'(dash) in the value and must follow all js variable declaration rules: '/[a-zA-Z0-9_]+/'
		 */
		'editor_font_sizes'  => array(
			// When editing paragraph text
			array(
				'name'      => __( 'Small', 'general' ),
				'shortName' => __( 'S', 'general' ),
				'size'      => 12,
				'slug'      => 'small',
			),
			array(
				'name'      => __( 'Normal', 'general' ),
				'shortName' => __( 'M', 'general' ),
				'size'      => 16,
				'slug'      => 'normal',
			),
			array(
				'name'      => __( 'Large', 'general' ),
				'shortName' => __( 'L', 'general' ),
				'size'      => 20,
				'slug'      => 'large',
			),
		),
		'register_nav_menus' => array(
			// Register nav menus
			'main'   => __( 'Main Menu', 'general' ),
			'footer' => __( 'Footer Menu', 'general' ),
			'legal'  => __( 'Legal Menu', 'general' ),
		),
		'acf_key'            => 'b3JkZXJfaWQ9MTMwNzYwfHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOC0wNS0wNyAxMzoxNTowMw==',
		'jpeg_quality'       => 100,
		'excerpt_length'     => 40,
		'excerpt_more'       => '...',
		'tgmpa_show'         => true,
		'wp_enqueue_script'  => array(
			// Register js scripts
			array(
				'handle'     => 'che-common',
				'src'        => THEME_ASSETS_URL . '/js/common.min.js',
				'deps'       => array(),
				'ver'        => CACHE_TIME,
				'vars'       => array(
					'ajaxurl'    => esc_url( admin_url( 'admin-ajax.php' ) ),
					'assetsPath' => THEME_URL . '/assets',
					'nonce'      => wp_create_nonce( 'ajax-nonce' ),
				),
				'args'       => array( 'in_footer' => true, 'strategy' => 'defer' ),
				'is_editor'  => true,
				'is_admin'   => false,
				'is_website' => true,
			),
			array(
				'handle'     => 'che-acf-post-preview',
				'src'        => THEME_ASSETS_URL . '/backend/js/acf-post-preview.js',
				'deps'       => array(),
				'ver'        => CACHE_TIME,
				'args'       => array( 'in_footer' => true, 'strategy' => 'defer' ),
				'is_editor'  => false,
				'is_admin'   => true,
				'is_website' => false,
			),
			array(
				'handle'     => 'che-acf-block-preview',
				'src'        => THEME_ASSETS_URL . '/backend/js/acf-block-preview.js',
				'deps'       => array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ),
				'ver'        => CACHE_TIME,
				'vars'       => array(
					'acfBlockPath' => THEME_URL . '/acf-blocks'
				),
				'args'       => array( 'in_footer' => true, 'strategy' => 'defer' ),
				'is_editor'  => true,
				'is_admin'   => false,
				'is_website' => false,
			)
			/*
			array(
				'handle'     => 'che-featured-image',
				'src'        => THEME_ASSETS_URL . '/backend/js/featured-image.js',
				'deps'       => array(),
				'ver'        => CACHE_TIME,
				'vars'       => array(
					'error_message'                => __( 'Please add a featured image before publishing.', 'general' ),
					'required_feature_image_posts' => array(
						'post',
					),
				),
				'args'       => array( 'in_footer' => true, 'strategy' => 'defer' ),
				'is_editor'  => true,
				'is_admin'   => false,
				'is_website' => false,
			),
			*/
		),
		'image_sizes'        => array(
			// Configure intermediate image sizes
			array(
				'name'   => 'thumbnail',
				'width'  => 300,
				'height' => 300,
				'crop'   => true,
			),
		),
		'wp_enqueue_style'   => array(
			// Load css scripts
			array(
				'handle'     => 'che-common',
				'src'        => THEME_ASSETS_URL . '/css/common.min.css',
				'deps'       => false,
				'ver'        => CACHE_TIME,
				'media'      => '',
				'is_editor'  => false,
				'is_admin'   => false,
				'is_website' => true,
			),
			array(
				'handle'     => 'che-style-admin',
				'src'        => THEME_ASSETS_URL . '/css/admin.min.css',
				'deps'       => false,
				'ver'        => CACHE_TIME,
				'media'      => '',
				'is_editor'  => false,
				'is_admin'   => true,
				'is_website' => false,
			),
			array(
				'handle'     => 'che-style-admin-general',
				'src'        => THEME_ASSETS_URL . '/backend/css/admin-general.css',
				'deps'       => false,
				'ver'        => CACHE_TIME,
				'media'      => '',
				'is_admin'   => true,
				'is_editor'  => false,
				'is_website' => false,
			),
		),
	);

	if ( ! array_key_exists( $key, $config ) ) {
		return null;
	}

	$flexible_styles_and_scripts = che_flexible_check_and_enqueue_template_styles();
	if ( ! empty( $flexible_styles_and_scripts ) ) {

		foreach ( $flexible_styles_and_scripts as $type => $styles_scripts ) {
			if ( isset( $config[ $type ] ) && is_array( $config[ $type ] ) && is_array( $styles_scripts ) ) {
				$config[ $type ] = array_merge( $config[ $type ], $styles_scripts );
			}
		}

	}


	return $config[ $key ];
}


/**
 * Check if there are any flexible content sections on the current page
 * and enqueue the required styles and scripts for each section.
 *
 * @return array<string, array<int, array<string, mixed>>>
 */
function che_flexible_check_and_enqueue_template_styles(): array {
	$flexible_styles_scripts = array();

	/* You can add styles and scripts here by any suggestions, for example:

	if ( is_page_template( 'templates/page_builder.php' ) ) {
		if ( have_rows( 'page_builder' ) ) {
			while ( have_rows( 'page_builder' ) ) {
				the_row();

				if ( get_row_layout() == 'hero' ) {
					$flexible_styles_scripts['wp_enqueue_style'][] = array(
						'handle'     => 'section-hero',
						'src'        => THEME_ASSETS_URL . '/css/section-hero.min.css',
						'deps'       => false,
						'ver'        => CACHE_TIME,
						'media'      => '',
						'is_editor'  => false,
						'is_admin'   => false,
						'is_website' => true,
					);

					$flexible_styles_scripts['wp_enqueue_script'][] = array(
						'handle'     => 'section-hero',
						'src'        => THEME_ASSETS_URL . '/js/section-hero.min.js',
						'deps'       => array(),
						'ver'        => CACHE_TIME,
						'vars'       => array(),
						'args'       => array( 'in_footer' => true, 'strategy' => 'defer' ),
						'is_editor'  => false,
						'is_admin'   => false,
						'is_website' => true,
					);
				}
			}
		}
	}
	*/


	return $flexible_styles_scripts;
}