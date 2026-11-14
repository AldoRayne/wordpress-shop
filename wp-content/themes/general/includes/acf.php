<?php
/**
 * ACF Pro Integration.
 *
 * Customise built-in ACF Pro functionality, orchestrate blocks, fields, groups
 * and options pages.
 *
 * @since   1.0.0
 * @package CHE\ACF
 */

/**
 * HTML Escaping in ACF Blocks
 */
add_filter( 'wp_kses_allowed_html', function ( $tags, $context ) {
	if ( $context === 'acf' ) {
		$tags['svg']  = array(
			'xmlns'       => true,
			'fill'        => true,
			'viewbox'     => true,
			'role'        => true,
			'aria-hidden' => true,
			'focusable'   => true,
			'width'       => true,
			'height'      => true,
			'class'       => true,
			'style'       => true,
		);
		$tags['path'] = array(
			'd'            => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		);
	}

	return $tags;
}, 10, 2 );

/**
 * Adds ACF Pro license key
 *
 * Triggered on license key on theme activation
 *
 * @return void
 */
function che_general_auto_set_license_keys(): void {
	if ( ! get_option( 'acf_pro_license' ) ) {
		$save = [
			'key' => che_general_config( 'acf_key' ),
			'url' => home_url(),
		];

		$save = maybe_serialize( $save );
		$save = base64_encode( $save );

		update_option( 'acf_pro_license', $save );
	}
}

add_action( 'after_switch_theme', 'che_general_auto_set_license_keys' );

/**
 * Configure Google Maps API.
 *
 * @link https://www.advancedcustomfields.com/resources/acf-fields-google_map-api/
 * @link https://developers.google.com/maps/documentation/javascript/get-api-key
 *
 * @return array
 */
add_filter( 'acf/fields/google_map/api', function ( $args ) {
	$args['key'] = get_field( 'google_maps_api_key', 'options' );

	return $args;
} );


/**
 * Allow only in local development
 *
 * @return bool
 */
add_filter( 'acf/settings/show_admin', function () {
	$environment_type = wp_get_environment_type();
	if ( $environment_type == 'local' ) {
		return true;
	}

	return false;
} );


/**
 * Filter allowed blocks to include only specific categories and exclude specific blocks.
 *
 * @return array<int, string> List of allowed block types.
 */
function che_filter_allowed_blocks(): array {
	// Get all registered blocks in WordPress.
	$all_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

	// Initialize an array to store filtered blocks.
	$filtered_blocks = [];

	// Categories to include in the editor.
	$include_categories = [
		'text',
		'media',
		'design',
		'widgets',
		'sections'
	];

	// Specific blocks to exclude, as marked in the images.
	$exclude_blocks = [
		'core/pullquote',
		'core/freeform',
		'core/archives',
		'core/calendar',
		'core/categories',
		'core/latest-comments',
		'core/latest-posts',
		'core/page-list',
		'core/rss',
		'core/tag-cloud',
		'core/preformatted',
		'core/verse',
	];

	// Loop through each block and filter by categories and exclusions.
	foreach ( $all_blocks as $block_name => $block_data ) {
		if ( isset( $block_data->category ) && in_array( $block_data->category, $include_categories ) && // Check if category is allowed
		     ! in_array( $block_name, $exclude_blocks ) // Check if block is not in the exclusion list
		) {
			$filtered_blocks[] = $block_name;
		}
	}

	// Return the filtered list of block types.
	return $filtered_blocks;
}

add_filter( 'allowed_block_types_all', function ( $allowed_block_types, $block_editor_context ) {
	return che_filter_allowed_blocks();
}, 10, 2 );


/**
 * Fix files popup
 *
 * @param array<string, mixed> $preload_paths
 * @return array<string, mixed>
 */
function che_acf_filter_rest_api_preload_paths( array $preload_paths ): array {
	global $post;
	$rest_path    = rest_get_route_for_post( $post );
	$remove_paths = array(
		add_query_arg( 'context', 'edit', $rest_path ),
		sprintf( '%s/autosaves?context=edit', $rest_path ),
	);

	return array_filter( $preload_paths, function ( $url ) use ( $remove_paths ) {
		return ! in_array( $url, $remove_paths, true );
	} );
}

add_filter( 'block_editor_rest_api_preload_paths', 'che_acf_filter_rest_api_preload_paths', 10, 1 );

/**
 * Load json file from folder block
 */
add_filter( 'acf/settings/load_json', function ( $paths ) {
	$blocks  = che_get_all_dir_path( THEME_ACF_BLOCKS );
	$options = che_get_all_dir_path( THEME_ACF_OPTION );
	$pages   = che_get_all_dir_path( THEME_ACF_PAGES );

	return array_merge( $paths, $blocks, $options, $pages );
} );

/**
 * Get json path by location
 *
 * @param array<int, array<int, array<string, mixed>>> $location
 */
function che_get_acf_json_path_by_location( array $location ) : string {
	if ( empty( $location ) ) {
		return '';
	}

	foreach ( $location as $item ) {
		foreach ( $item as $rules ) {
			switch ( $rules['param'] ) {
				case 'block':
					$registered_blocks = WP_Block_Type_Registry::get_instance()->get_registered( $rules['value'] );

					if ( $registered_blocks && isset( $registered_blocks->path ) && is_dir( $registered_blocks->path ) ) {
						return $registered_blocks->path;
					}

					break;

				case 'page_template':
					$arr = explode( '/', $rules['value'] );

					if ( count( $arr ) === 3 && $arr[0] === 'acf-pages' && $arr[2] === 'template.php' && is_dir( THEME_ACF_PAGES . '/' . $arr[1] ) ) {
						return THEME_ACF_PAGES . '/' . $arr[1];
					}

					break;

				case 'options_page':
					return THEME_ACF_OPTION . '/' . $rules['value'];
			}
		}
	}

	return '';
}

/**
 * Set paths on manual json saving
 */
add_filter( 'acf/json/save_paths', function ( $paths, $post ) {
	if ( empty( $post['location'] ) ) {
		return $paths;
	}

	$new_path = che_get_acf_json_path_by_location( $post['location'] );

	return $new_path ? array( $new_path ) : $paths;
}, 99, 2 );

/**
 * Save json in folder block
 * Remove file *.json on change location
 * Remove file on trash ACF group
 *
 * @param string $path
 * @return string
 */
function che_save_json_in_folder_block( $path ) {

	if ( ! empty( $_POST['post_name'] ) ) {
		$files     = acf_get_local_json_files();
		$post_name = sanitize_text_field( $_POST['post_name'] );

		if ( ! empty( $files[ $post_name ] ) && file_exists( $files[ $post_name ] ) ) {
			unlink( $files[ $post_name ] );
		}
	}

	if ( ! empty( $_GET['post'] ) && ! empty( $_GET['action'] ) && $_GET['action'] == 'trash' ) {
		$post_id   = (int) $_GET['post'];
		$post_name = get_post_meta( $post_id, '_wp_desired_post_slug', true );

		che_general_search_and_delete_file( THEME_ACF_BLOCKS . '/', $post_name . '.json' );
		che_general_search_and_delete_file( THEME_ACF_PAGES . '/', $post_name . '.json' );
		che_general_search_and_delete_file( THEME_ACF_OPTION . '/', $post_name . '.json' );
		che_general_search_and_delete_file( THEME_DIR . '/acf-json/', $post_name . '.json' );
	}

	if ( isset( $_POST['acf_field_group']['location'] ) && is_array( $_POST['acf_field_group']['location'] ) ) {
		$path = che_get_acf_json_path_by_location( $_POST['acf_field_group']['location'] ) ?: $path;
	}

	if ( isset( $_POST['acf_ui_options_page'] ) ) {
		$dir_path = THEME_ACF_OPTION . '/' . $_POST['acf_ui_options_page']['menu_slug'];

		if ( ! is_dir( $dir_path ) ) {
			if ( ! wp_mkdir_p( $dir_path ) ) {
				error_log( "Directory $dir_path not created" );
			} else {
				$index_file    = $dir_path . '/index.php';
				$index_content = "<?php\n// Silence is golden.\n";
				file_put_contents( $index_file, $index_content );
			}
		}
	}

	return $path;
}

add_filter( 'acf/settings/save_json', 'che_save_json_in_folder_block' );

/**
 * Generate php code by field.json
 *
 * @return void
 */
function che_generate_php_code_by_field_json(): void {
	if ( ! class_exists( 'ACFTC_Core' ) || ! class_exists( 'ACFTC_Group' ) ) {
		return;
	}

	if ( ! function_exists( 'acf_get_local_json_files' ) ) {
		return;
	}

	$che_get_post_by_name = function ( string $name, string $post_type = "post" ): ?\WP_Post {
		$query = new WP_Query( [
			"post_type" => $post_type,
			"name"      => $name
		] );

		$posts = $query->posts;
		return ( ! empty( $posts ) && $posts[0] instanceof \WP_Post ) ? $posts[0] : null;
	};

	$che_get_code = function ( $key, $type ) use ( $che_get_post_by_name ) {

		$get_post = $che_get_post_by_name( $key, 'acf-field-group' );

		if ( ! $get_post ) {
			return false;
		}

		$field_group_post_obj = get_post( $get_post->ID );
		if ( ! $field_group_post_obj || ! $field_group_post_obj instanceof \WP_Post ) {
			return false;
		}

		/** @var class-string $locations_class_name */
		$locations_class_name = ACFTC_Core::$class_prefix . 'Locations';
		if ( ! class_exists( $locations_class_name ) ) {
			return false;
		}
		$locations_ui         = new $locations_class_name( $field_group_post_obj );

		$parent_field_group = new ACFTC_Group( array(
			'field_group_id' => $field_group_post_obj->ID,
		) );
		/** @var object $locations_ui */
		if ( ! method_exists( $locations_ui, 'get_locations_code_html' ) ) {
			return false;
		}
		$data               = $locations_ui->get_locations_code_html( $parent_field_group );

		preg_match_all( '/<code class="language-php">([^<]*)<\/code>/', $data, $matches );

		if ( ! empty( $matches[1] ) ) {
			return $matches[1];
		}

		return false;
	};

	$files = acf_get_local_json_files();

	if ( $files ) {
		foreach ( $files as $key => $value ) {

			$dir_block = THEME_ACF_BLOCKS . '/' . str_replace( 'group_block_', '', $key );
			$dir_page  = THEME_ACF_PAGES . '/' . str_replace( 'group_page_', '', $key );

			$code = false;
			$file = false;

			if ( ! empty( $dir_block ) && is_dir( $dir_block ) && ! file_exists( $dir_block . '/index.php' ) ) {
				$file     = $dir_block . '/index.php';
				$get_code = $che_get_code( $key, 'block' );
				if ( is_array( $get_code ) ) {
					$code     = implode( PHP_EOL, $get_code );

					$code = preg_replace( '/\*\//', '*/ ' . PHP_EOL . 'if(che_show_preview_image( $block )){ return true; }', $code, 1 );
					if ( is_string( $code ) ) {
						$code = str_replace( '@param', '@var', $code );
					}
				}

			} elseif ( ! empty( $dir_page ) && is_dir( $dir_page ) && ! file_exists( $dir_page . '/index.php' ) ) {
				$file     = $dir_page . '/index.php';
				$get_code = $che_get_code( $key, 'page' );
				if ( is_array( $get_code ) ) {
					$code     = implode( PHP_EOL, $get_code );
				}
			}

			if ( $code && $file ) {
				$result = html_entity_decode( (string) $code, ENT_QUOTES );
				file_put_contents( $file, $result );
			}

		}
	}
}

add_action( 'admin_init', 'che_generate_php_code_by_field_json' );

/**
 * Filters the $field settings array after being loaded
 *
 * The ACF field must be named `theme_icons` and has types:
 *  - 'checkbox'
 *  - 'select'
 *  - 'radio'
 *
 * @param array<string, mixed> $field The field array containing all settings
 *
 * @return array<string, mixed> Modified field array with SVG choices
 */
function che_acf_load_field_theme_icons( array $field ): array {
	if ( ! is_admin() ) {
		return $field;
	}

	$fields_types = [
		'checkbox',
		'select',
		'radio'
	];

	// Check if the field type is one of the allowed types
	if ( ! in_array( $field['type'], $fields_types, true ) ) {
		return $field; // If not, return the field unmodified
	}

	$field['choices'] = [];
	$icons_directory  = THEME_FRONTEND_DIR . '/src/icons/';
	$file_type        = '.svg';

	$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$is_field_group = $current_screen && $current_screen->id === 'acf-field-group';

	$svg_files = glob( $icons_directory . '*' . $file_type );

	if ( $svg_files ) {
		foreach ( $svg_files as $svg_file ) {
			$icon_name = pathinfo( $svg_file, PATHINFO_FILENAME );

			if ( ! $is_field_group ) {
				$svg  = file_get_contents( $svg_file );
				if ( false !== $svg ) {
					$svg  = str_replace( '<svg', '<svg style="max-height: 17px; max-width: 17px; margin-right: 10px; vertical-align: middle;"', $svg );
					$name = "<span>$svg$icon_name$file_type</span>";
				} else {
					$name = "$icon_name$file_type";
				}
			} else {
				$name = "$icon_name$file_type";
			}

			$field['choices'][ $icon_name ] = $name;
		}
	}

	//set the return format and default value
	if ( ! empty( $field['choices'] ) ) {
		$field['default_value'] = array_keys( $field['choices'] )[0];
		$field['return_format'] = 'value';
	}

	return $field;
}

add_filter( 'acf/load_field/name=theme_icons', 'che_acf_load_field_theme_icons' );

/**
 * Replace @year with the current year in ACF output.
 *
 * @param mixed                $value   The value to check.
 * @param int|string           $post_id The post-ID.
 * @param array<string, mixed> $field   The field array.
 *
 * @return mixed The modified value.
 */
function che_replace_year_in_acf_output( mixed $value, int|string $post_id, array $field ): mixed {
	// Check if we are on the frontend and if the value contains @year
	if ( ! is_admin() && is_string( $value ) && str_contains( $value, '@year' ) ) {
		// Replace @year with the current year
		$value = str_replace( '@year', date( 'Y' ), $value );
	}

	return $value;
}

add_filter( 'acf/format_value', 'che_replace_year_in_acf_output', 10, 3 );

/**
 * Disable the ACF escaped HTML error messages
 *
 * @return bool
 */
add_filter( 'acf/admin/prevent_escaped_html_notice', '__return_true' );

/**
 * Filters the display of the post object field in ACF to include a thumbnail before the post title.
 *
 * This filter modifies the result of the post object field in Advanced Custom Fields (ACF) by adding a
 * post thumbnail next to the post title in the dropdown or selection list. If the post has a featured image,
 * it is displayed as a thumbnail (with specified inline styling), followed by the post title.
 *
 * @param string  $text The text representing the post object (usually the post title).
 * @param WP_Post $post The post object being displayed.
 *
 * @return string The modified post title, including the thumbnail (if available) and the post title, wrapped in a <span> element.
 */
add_filter( 'acf/fields/post_object/result', function ( $text, $post ) {
	// Get the post thumbnail for the current post object, styled with specific dimensions and alignment.
	$thumbnail = get_the_post_thumbnail( $post->ID, array(
		17,
		17
	), array( 'style' => 'max-height: 17px; max-width: 17px; margin: -3px 10px 0 0; vertical-align: middle;' ) );

	// If a thumbnail exists, prepend it to the post title text.
	if ( $thumbnail ) {
		$text = $thumbnail . $text;
	}

	// Return the modified post title with the thumbnail, wrapped in a <span> element.
	return '<span>' . $text . '</span>';
}, 10, 2 );

/**
 * Adds a preview into field label in Flexible Content fields.
 */
function che_acf_add_flexible_content_preview( string $label, string $name ): string {
	$preview_path = THEME_DIR . "/template-parts/page-builder/preview/$name.jpg";
	$preview_url  = file_exists( $preview_path ) ? THEME_URL . "/template-parts/page-builder/preview/$name.jpg" : '';
	$placeholder  = ! $preview_url ? sprintf( '<span class="block-editor-inserter__preview-content-missing">%s</span>', __( 'No preview available.' ) ) : '';

	return sprintf( '<span class="acf-layout-tooltip" data-preview="%s">%s<span class="acf-layout-preview components-popover__content">%s</span></span>', esc_url( $preview_url ), esc_html( $label ), $placeholder );
}

add_filter( 'acf/prepare_field/type=flexible_content', function ( $field ) {
	foreach ( $field['layouts'] as $key => $layout ) {
		$field['layouts'][ $key ]['label'] = che_acf_add_flexible_content_preview( $layout['label'], $layout['name'] );
	}

	return $field;
} );
add_filter( 'acf/fields/flexible_content/layout_title', function ( $title, $field, $layout ) {
	return ! str_contains( $title, 'acf-layout-tooltip' ) ? che_acf_add_flexible_content_preview( $title, $layout['name'] ) : $title;
}, 10, 3 );

/**
 * Automatically set 'ui' for select, true_false, and configure 'relationship' fields in ACF.
 *
 * This function modifies ACF fields when they are loaded. For 'relationship' fields, it ensures that:
 * - Only published posts are selected.
 * - If more than two post types are selected, the 'Post Type' filter is enabled.
 * - The 'Featured Image' option is enabled.
 *
 * @param array<string, mixed> $field The ACF field settings.
 *
 * @return array<string, mixed> Modified ACF field settings.
 */
function che_auto_configure_acf_fields( array $field ): array {
	// Automatically set 'ui' for 'select' and 'true_false' fields
	if ( in_array( $field['type'], array( 'select', 'true_false' ) ) ) {
		$field['ui'] = 1;
	}

	// Additional configuration for 'relationship' fields
	if ( $field['type'] == 'relationship' ) {
		// Ensure only published posts are selected
		$field['post_status'] = array( 'publish' );

		// Check if filters exist and are an array, if not initialize it
		if ( ! isset( $field['filters'] ) || ! is_array( $field['filters'] ) ) {
			$field['filters'] = array();
		}

		// Check the number of post types selected
		if ( isset( $field['post_type'] ) && is_array( $field['post_type'] ) ) {
			// Enable 'Post Type' filter if more than one post type is selected
			if ( count( $field['post_type'] ) > 1 ) {
				if ( ! in_array( 'post_type', $field['filters'] ) ) {
					$field['filters'][] = 'post_type';
				}
			} else {
				// Remove 'post_type' filter if there is only one post type
				if ( in_array( 'post_type', $field['filters'] ) ) {
					$field['filters'] = array_diff( $field['filters'], array( 'post_type' ) );
				}
			}
		}

		// Ensure 'Featured Image' filter is enabled
		if ( isset( $field['elements'] ) && is_array( $field['elements'] ) ) {
			if ( ! in_array( 'featured_image', $field['elements'] ) ) {
				$field['elements'][] = 'featured_image';
			}
		} else {
			// If 'elements' is not set or not an array, initialize it as an array with 'featured_image'
			$field['elements'] = array( 'featured_image' );
		}
	}

	// Return the modified field settings.
	return $field;
}

// Add the filter to modify ACF fields when they are loaded.
add_filter( 'acf/load_field', 'che_auto_configure_acf_fields' );