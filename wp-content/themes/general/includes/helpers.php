<?php

/**
 * Simple return callback generator.
 *
 * Usage:
 * add_filter( 'filter_name', che_general_return( 'Return value' ) );
 * add_shortcode( 'year', che_general_return( date( 'Y' ) ) );
 *
 * @param mixed $return
 * @return \Closure
 */
function che_general_return( mixed $return = null ): \Closure {
	return static function () use ( $return ) {
		return $return;
	};
}

/**
 * The empty() check, but for multiple variables.
 *
 * @param mixed ...$arguments
 *
 * @return bool
 */
function che_general_multi_empty( ...$arguments ): bool {
	foreach ( $arguments as $argument ) {
		if ( empty( $argument ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Convert an array of attributes into HTML string.
 *
 * @param array<string, mixed> $attrs
 *
 * @return string
 */
function che_general_stringify_attrs( array $attrs ): string {
	$html = '';

	$attrs = array_map( 'trim', $attrs );
	$attrs = array_filter( $attrs );

	foreach ( $attrs as $key => $value ) {
		$html .= sprintf( ' %s="%s"', $key, $value );
	}

	return $html;
}

/**
 * Move yoast metabox to the bottom
 */
add_filter( 'wpseo_metabox_prio', che_general_return( 'low' ) );

/**
 * Show custom field in customize
 */
function che_general_config_custom_logo(): void {
	$defaults = [
		'height'      => 200,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
		'header-text' => [],
	];

	add_theme_support( 'custom-logo', $defaults );
}

add_action( 'after_setup_theme', 'che_general_config_custom_logo' );

/**
 * Show custom logo
 */
function che_general_theme_get_custom_logo(): void {
	if ( has_custom_logo() ) {
		echo '<a class="header-logo" href="' . get_site_url() . '" >';
		echo get_field( 'header_logo', 'option' );
		echo "</a>";
	} else {
		echo '<h1>';
		echo '<a href="' . get_site_url() . '">' . get_bloginfo( 'name' ) . '</a>';
		echo '</h1>';
	}
}

/**
 * Upload image resize
 *
 * @param int    $maxDim    : image size
 * @param string $file_name : $_FILES['photo']['tmp_name']
 *
 * @return string|false
 */
function che_general_image_resize( int $maxDim, string $file_name ): string|false {
	$image_info = getimagesize( $file_name );
	if ( false === $image_info ) {
		return false;
	}
	list( $width, $height, $type, $attr ) = $image_info;

	if ( $width > $maxDim || $height > $maxDim ) {
		$target_filename = $file_name;
		$ratio           = $width / $height;

		if ( $ratio > 1 ) {
			$new_width  = $maxDim;
			$new_height = (int)($maxDim / $ratio);
		} else {
			$new_width  = (int)($maxDim * $ratio);
			$new_height = $maxDim;
		}

		$file_content = file_get_contents( $file_name );
		if ( false === $file_content ) {
			return false;
		}
		$src = imagecreatefromstring( $file_content );
		if ( false === $src ) {
			return false;
		}
		if ( $new_width < 1 ) $new_width = 1;
		if ( $new_height < 1 ) $new_height = 1;
		$dst = imagecreatetruecolor( $new_width, $new_height );
		if ( false === $dst ) {
			imagedestroy( $src );
			return false;
		}
		imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
		imagedestroy( $src );
		imagejpeg( $dst, $target_filename ); // adjust format as needed
		imagedestroy( $dst );

		$resized_content = file_get_contents( $file_name );
		return false !== $resized_content ? base64_encode( $resized_content ) : false;
	} else {
		$content = file_get_contents( $file_name );
		return false !== $content ? base64_encode( $content ) : false;
	}
}

/**
 * Get URL of an image set through ACF field, in particular size.
 *
 * @param int|string $input
 * @param string|int[] $size
 * @param string|int|null $object_id
 * @param array<string, mixed> $attr
 *
 * @return string
 */
function che_general_get_image( $input, $size = 'thumbnail', $object_id = null, array $attr = [] ): string {
	$image_id = $input;

	if ( ! is_int( $input ) ):
		$image_id = get_field( (string) $input, $object_id );
	endif;

	return (string) wp_get_attachment_image( (int) $image_id, $size, false, $attr );
}

/**
 * Output URL of an image set through ACF field, in particular size.
 *
 * @param string $input
 * @param string|int[] $size
 * @param string|int|null $object_id
 * @param array<string, mixed> $attr
 *
 * @return void
 */
function che_general_the_image( string $input, $size = 'thumbnail', $object_id = null, array $attr = [] ): void {
	echo che_general_get_image( $input, $size, $object_id, $attr );
}

/**
 * Print the markup for background image, with srcset.
 *
 * @param string $input
 * @param string|int[] $size
 * @param string|int|null $object_id
 *
 * @return string
 */
function che_general_get_background_image( string $input, $size = 'large', $object_id = null ): string {
	$image = che_general_get_image( $input, $size, $object_id );

	if ( ! $image ):
		return '';
	endif;

	if ( ! is_admin() ):
		preg_match( '/data-srcset="(.+?)"/', $image, $matches );

		if ( ! $matches ):
			preg_match( '/src="(.+?)"/', $image, $matches );
		endif;

		if ( ! $matches ):
			return '';
		endif;

		$src = $matches[1];

		return sprintf( 'data-bgset="%s" data-sizes="auto"', $src );
	else:
		$image_id = get_field( $input, $object_id );
		$src      = wp_get_attachment_image_url( (int) $image_id, $size );

		return sprintf( 'style="background-image: url(%s);"', esc_url( (string) $src ) );
	endif;
}

/**
 * Print the markup for background image, with srcset.
 *
 * @param string $input
 * @param string|int[] $size
 * @param string|int|null $object_id
 */
function che_general_the_background_image( string $input, $size = 'large', $object_id = null ): void {
	echo che_general_get_background_image( $input, $size, $object_id );
}

/**
 * Returns converted YT embedding url with nocookie for privacy
 *
 * @param string|null $url
 *
 * @return string|void
 */
function che_general_filter_embed_links( ?string $url = null ) {
	if ( ! $url ):
		return;
	endif;

	return str_replace( 'youtube.com/embed/', 'youtube-nocookie.com/embed/', $url );
}

/**
 * Return assets from the theme library.
 *
 * @param string $file Relative path to file in theme assets directory.
 * @param array<string, mixed>  $atts Additional HTML attributes.
 *
 *
 * @return string|void
 */
function che_general_get_asset( string $file, array $atts = [] ) {
	if ( ! $file || ! file_exists( THEME_ASSETS_DIR . '/' . $file ) ) {
		return;
	}

	$file_type = pathinfo( $file, PATHINFO_EXTENSION );

	if ( $file_type === 'svg' ) {
		$content = file_get_contents( THEME_ASSETS_DIR . '/' . $file );
		return false !== $content ? $content : '';
	}

	if ( in_array( $file_type, [ 'jpg', 'jpeg', 'png', 'gif' ] ) ) {
		if ( isset( $atts['class'] ) && is_string( $atts['class'] ) && ! str_contains( $atts['class'], 'lazyload' ) ) {
			return sprintf( '<img data-src="%s/%s" %s>', THEME_ASSETS_URL, $file, che_general_stringify_attrs( $atts ) );
		}

		return sprintf( '<img src="%s/%s" %s>', THEME_ASSETS_URL, $file, che_general_stringify_attrs( $atts ) );
	}
}

/**
 * Render assets from the theme library.
 *
 * @param string $file Relative path to file in theme assets directory.
 * @param array<string, mixed>  $atts Additional HTML attributes.
 *
 * @return void
 */
function che_general_the_asset( string $file, array $atts = [] ): void {
	echo che_general_get_asset( $file, $atts );
}

/**
 * Formatting bytes string to appropriate format
 *
 * @param int|array<string, mixed> $bytes
 * @param int $precision
 *
 * @return string
 */
function che_general_the_filesize( $bytes, int $precision = 2 ): string {
	$units = [ 'b', 'kb', 'mb', 'gb', 'tb' ];

	if ( is_array( $bytes ) && isset( $bytes['filesize'] ) ):
		$bytes = (int) $bytes['filesize'];
	endif;

	$bytes_int = is_int($bytes) ? $bytes : (int)$bytes;

	$bytes_int = max( $bytes_int, 0 );
	$pow   = floor( ( $bytes_int ? log( $bytes_int ) : 0 ) / log( 1024 ) );
	$pow   = (int) min( $pow, count( $units ) - 1 );

	// Uncomment one of the following alternatives
	$bytes_float = (float) $bytes_int / ( 1 << ( 10 * $pow ) );

	return round( $bytes_float, $precision ) . ' ' . $units[ $pow ];
}

/**
 * Returns the primary category of a post
 *
 * @param int     $post_id
 * @param string  $term
 * @param boolean $return_all_categories
 *
 * @return array<string, mixed>
 */
function che_general_get_primary_category(
	int $post_id, string $term = 'category', bool $return_all_categories = true
): array {
	$return = [];

	// Fallback, if no $post_id is provided
	if ( ! $post_id ):
		$post_id = (int) get_the_ID();
	endif;

	// Show Primary category by Yoast if it is enabled & set
	if ( class_exists( 'WPSEO_Primary_Term' ) ):
		$wpseo_primary_term = new WPSEO_Primary_Term( $term, $post_id );
		$primary_term_id = $wpseo_primary_term->get_primary_term();
		$primary_term       = get_term( (int)$primary_term_id );
		if ( $primary_term instanceof \WP_Term ) {
			$return['primary_category'] = $primary_term;
		}
	endif;

	if ( empty( $return['primary_category'] ) || $return_all_categories ):
		$categories_list = get_the_terms( $post_id, $term );

		if ( empty( $return['primary_category'] ) && ! empty( $categories_list ) && is_array( $categories_list ) ):
			$return['primary_category'] = $categories_list[0];  //get the first category
		endif;

		if ( $return_all_categories ):
			$return['all_categories'] = [];

			if ( ! empty( $categories_list ) && is_array( $categories_list ) ):
				foreach ( $categories_list as $category ):
					if ( $category instanceof \WP_Term ) {
						$return['all_categories'][] = $category->term_id;
					}
				endforeach;
			endif;
		endif;
	endif;

	return $return;
}

/**
 * Wrapping in a tag <pre>$array</pre>
 *
 * @param mixed  $array  array|object data show
 * @param bool $return return ore show
 *
 * @return void|string
 */
if ( ! function_exists( 'pr' ) ) {
	function pr( mixed $array, bool $return = false ): ?string {
		$output = '';
		if ( ! $return ) {
			$output .= '<pre  style="background-color:#333333; padding: 10px; margin: 5px; color: #fff5cb">';
		}

		$output .= print_r( $array, true );

		if ( ! $return ) {
			$output .= '</pre>';
			echo $output;
			return null;
		}

		return $output;
	}
}


/**
 * Search and delete file from folder
 *
 * @param string $dir
 * @param string $filename
 *
 * @return void
 */
if ( ! function_exists( 'che_general_search_and_delete_file' ) ) {
	function che_general_search_and_delete_file( string $dir, string $filename ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) );

		foreach ( $iterator as $file ) {
			/** @var \SplFileInfo $file */
			if ( $file->getFilename() === $filename ) {
				$real_path = $file->getRealPath();
				if ( false !== $real_path ) {
					unlink( $real_path );
				}
				break;
			}
		}
	}
}

/**
 * Add attr custom_srcset in function attachment
 *
 * Example: echo wp_get_attachment_image(207, [300, 300], false, ['custom_srcset' => '250,250;150,150']);
 *
 * @param array<string, mixed>        $attr       Whether to short-circuit the image downsizes.
 * @param \WP_Post          $attachment Attachment ID for image.
 * @param string|int[] $size       Requested image size. Can be any registered image size name, or an array of width and height values in pixels (in that order).
 * @return array<string, mixed>
 */
function che_add_custom_srcset_attr( array $attr, \WP_Post $attachment, $size ): array {

	if ( empty( $attr['custom_srcset'] ) || ! is_string( $attr['custom_srcset'] ) ) {
		return $attr;
	}

	$custom_srcset = array_filter( explode( ';', $attr['custom_srcset'] ) );

	if ( empty( $custom_srcset ) ) {
		return $attr;
	}

	$file             = get_attached_file( $attachment->ID );
	if ( false === $file ) {
		return $attr;
	}
	$meta             = wp_get_attachment_metadata( $attachment->ID );
	if ( ! is_array( $meta ) ) {
		$meta = [];
	}
	$srcset_max_width = 0;

	foreach ( $custom_srcset as $item ) {
		$parts = explode( ',', (string) $item );
		if ( count( $parts ) < 2 ) {
			continue;
		}
		list( $width, $height ) = array_map( 'absint', $parts );

		if ( ! $width || ! $height ) {
			continue;
		}

		$srcset_max_width = max( $srcset_max_width, $width );
		$new_image        = image_make_intermediate_size( $file, $width, $height, true );

		if ( is_array( $new_image ) ) {
			$size_name                   = $width . 'x' . $height;
			$meta['sizes'][ $size_name ] = $new_image;
		}
	}

	wp_update_attachment_metadata( $attachment->ID, $meta );
	$image = wp_get_attachment_image_src( $attachment->ID, $size, false );

	if ( is_array( $image ) ) {
		list( $src, $width, $height ) = $image;
		$image_meta = wp_get_attachment_metadata( $attachment->ID );

		if ( is_array( $image_meta ) ) {
			$size_array = array( absint( $width ), absint( $height ) );
			$srcset     = wp_calculate_image_srcset( $size_array, (string) $src, $image_meta, $attachment->ID );
			$sizes      = $srcset_max_width > (int) $width ? sprintf( '(max-width: %1$dpx) 100vw, %2$dpx', $srcset_max_width, $width ) : sprintf( '(max-width: %1$dpx) 100vw, %1$dpx', $width );

			if ( $srcset && $sizes ) {
				$attr['srcset'] = $srcset;
				$attr['sizes']  = $sizes;
			}
		}
	}

	unset( $attr['custom_srcset'] );

	return $attr;
}

add_filter( 'wp_get_attachment_image_attributes', 'che_add_custom_srcset_attr', 10, 3 );

/**
 * Create crop image size on fly
 *
 * @param bool|array<int, mixed>   $downsize Whether to short-circuit the image downsize.
 * @param int          $id       Attachment ID for image.
 * @param string|int[] $size     Requested image size. Can be any registered image size name, or an array of width and height values in pixels (in that order).
 * @return bool|array<int, mixed>
 */
function che_create_crop_image_size_on_fly( $downsize, int $id, $size ) {
	if ( is_array( $size ) && count( $size ) == 2 ) {

		$meta      = wp_get_attachment_metadata( $id );
		if ( ! is_array( $meta ) ) {
			$meta = [];
		}
		$size_name = $size[0] . 'x' . $size[1];

		if ( ! isset( $meta['sizes'][ $size_name ] ) ) {

			if ( get_post_mime_type( $id ) && in_array( get_post_mime_type( $id ), array(
					'image/jpeg',
					'image/png'
				) ) ) {

				$file      = get_attached_file( $id );
				if ( false === $file ) {
					return false;
				}
				$new_image = image_make_intermediate_size( $file, (int) $size[0], (int) $size[1], true );

				if ( is_array( $new_image ) ) {

					$meta['sizes'][ $size_name ] = $new_image;
					wp_update_attachment_metadata( $id, $meta );
					$uploads  = wp_upload_dir();
					$new_path = $uploads['url'] . '/' . $new_image['file'];

					if ( is_plugin_active( 'imagify/imagify.php' ) && class_exists( 'Imagify\Optimization\File' ) ) {
						$Imagify = new \Imagify\Optimization\File( $uploads['path'] . '/' . $new_image['file'] );
						$Imagify->optimize( [
							'backup'  => false,
							'convert' => 'webp',
						] );
					}

					return array( $new_path, $size[0], $size[1], true );
				}

			}

		}

	}

	return false;
}

add_filter( 'image_downsize', 'che_create_crop_image_size_on_fly', 10, 3 );

/**
 * Display ACF link with custom attributes.
 *
 * @param array<string, mixed>|false $acf_link     ACF link data array.
 * @param array<string, mixed> $attr         Additional attribute/attributes for the link.
 * @param bool  $has_esc_html Add esc_html.
 * @return string|false
 */
function che_display_acf_link( $acf_link, array $attr = [], bool $has_esc_html = true ) {

	if ( ! $acf_link || empty( $acf_link['url'] ) || ! is_array( $acf_link ) ) {
		return false;
	}

	$attr_params = '';
	if ( $attr ) {
		foreach ( $attr as $key => $value ) {
			$attr_params .= ' ' . $key . '="' . esc_attr( (string) $value ) . '"';
		}
	}

	/* Link target */
	$attr_target = ! empty( $acf_link['target'] ) ? 'target="' . esc_attr( (string) $acf_link['target'] ) . '"' : '';

	/* Link title */
	$link_title = ( $has_esc_html && ! empty( $acf_link['title'] ) ) ? esc_html( (string) $acf_link['title'] ) : ( ! empty( $acf_link['title'] ) ? (string) $acf_link['title'] : '' );

	return "<a href='" . esc_url( (string) $acf_link['url'] ) . "' {$attr_params} {$attr_target}>" . $link_title . "</a>";

}

/**
 * Get the logo HTML based on type.
 *
 * @param string $type The type of logo: 'custom_logo', 'header_logo', 'footer_logo'.
 * @param string|int[] $size The size of the image. Default is 'full'.
 * @param array<string, mixed>  $attr Additional attributes for the image tag.
 * @return string|false Returns the HTML for the logo or false if not set.
 */
function che_get_logo( string $type = 'custom_logo', $size = 'full', array $attr = [] ) {
	// Check if the logo is set for the given type.
	$logo_id = get_theme_mod( $type );

	if ( $logo_id ) {
		// Get the logo HTML.
		$logo_html = wp_get_attachment_image( (int) $logo_id, $size, false, $attr );
		return $logo_html;
	}

	// Return false if no logo is set.
	return false;
}