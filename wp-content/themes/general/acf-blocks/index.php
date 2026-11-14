<?php
/**
 * @see https://www.advancedcustomfields.com/resources/whats-new-with-acf-blocks-in-acf-6/
 */

/**
 * Register custom block category
 */
add_filter( 'block_categories_all', function ( $categories ) {
	$block_categories = array(
		array(
			'slug'  => 'section',
			'title' => __( 'Section', 'general' ),
		),
		array(
			'slug'  => 'themes',
			'title' => __( 'Themes', 'general' ),
		)
	);

	return array_merge( $categories, $block_categories );
}, 10, 2 );

/**
 * Register block by folder THEME_ACF_BLOCKS
 */
add_action( 'init', function () {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}

	$blocks = che_get_all_dir( THEME_ACF_BLOCKS );

	if ( $blocks ) {
		foreach ( $blocks as $block ) {
			$block_config_path = THEME_ACF_BLOCKS . '/' . $block . '/block.json';
			if ( file_exists( $block_config_path ) ) {
				register_block_type( $block_config_path );
			}
		}
	}
} );

/**
 * Show preview image in admin when hover on add block
 *
 * @param array<string, mixed> $block
 *
 * @return bool
 */
function che_show_preview_image( array $block ): bool {
	$preview_image = get_field( 'preview' );

	if ( ! $preview_image ) {
		return false;
	}

	$filename          = 'preview.jpg';
	$dirname           = basename( $block['path'] );
	$preview_file_path = trailingslashit( $block['path'] ) . $filename;

	if ( ! is_file( $preview_file_path ) ) {
		return false;
	}

	$preview_image_url = get_theme_file_uri( "/acf-blocks/{$dirname}/{$filename}" );

	echo '<img src="' . esc_url( $preview_image_url ) . '" style="max-width:100%">';

	return true;
}

/**
 * Register script for blocks from folder THEME_ACF_BLOCKS
 *
 * @return void
 */
function che_register_script_for_acf_blocks(): void {
	$blocks = che_get_all_dir( THEME_ACF_BLOCKS );

	if ( $blocks ) {
		foreach ( $blocks as $block_dir ) {

			$block_config_path = THEME_ACF_BLOCKS . '/' . $block_dir . '/block.json';

			if ( file_exists( $block_config_path ) ) {

				$string = file_get_contents( $block_config_path );
				if ( false === $string ) {
					continue;
				}
				/** @var \stdClass|null $block_data */
				$block_data  = json_decode( $string );

				if ( $block_data && isset( $block_data->name ) && has_block( $block_data->name ) ) {

					if ( ! empty( $block_data->custom_scripts ) && is_array( $block_data->custom_scripts ) ) {
						foreach ( $block_data->custom_scripts as $custom_scripts ) {
							if ( file_exists( THEME_ASSETS_DIR . $custom_scripts ) ) {
								$handle = 'script-' . md5( $custom_scripts );
								$version = property_exists($block_data, 'version') ? $block_data->version : false;
								wp_enqueue_script( $handle, THEME_ASSETS_URL . $custom_scripts, [], $version, array(
									'in_footer' => true,
									'strategy'  => 'defer',
								) );
							}
						}
					}

					if ( ! empty( $block_data->custom_styles ) && is_array( $block_data->custom_styles ) ) {
						foreach ( $block_data->custom_styles as $custom_styles ) {
							if ( file_exists( THEME_ASSETS_DIR . $custom_styles ) ) {
								$creationTime = filemtime(THEME_ASSETS_DIR . $custom_styles);
								$handle = 'style-' . md5($custom_styles);
								wp_enqueue_style($handle, THEME_ASSETS_URL . $custom_styles, [], $creationTime !== false ? (string) $creationTime : false);
							}
						}
					}

				}
			}
		}
	}

}
add_action( 'wp_enqueue_scripts', 'che_register_script_for_acf_blocks' );
