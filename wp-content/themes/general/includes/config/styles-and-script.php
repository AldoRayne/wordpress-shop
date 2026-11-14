<?php
/**
 * Enqueue scripts.
 *
 * @param array<string, mixed> $args
 * @return void
 */
function che_wp_enqueue_script( array $args ): void {
	$default_args = array(
		'handle' => '',
		'src'    => '',
		'deps'   => array(),
		'ver'    => CACHE_TIME,
		'vars'   => array(),
		'args'   => array(),
	);

	$args = wp_parse_args( $args, $default_args );

	if ( ! $args['handle'] || ! $args['src'] ) {
		return;
	}

	if ( ! empty( $args['vars'] ) ) {
		// replace '-' with '_' in $value['handle'] (for $object_name attribute in wp_localize_script function)
		$args['handle'] = str_replace( '-', '_', $args['handle'] );
	}

	wp_enqueue_script( $args['handle'], $args['src'], $args['deps'], $args['ver'], $args['args'] );

	if ( ! empty( $args['vars'] ) ) {
		wp_localize_script( $args['handle'], $args['handle'] . '_vars', $args['vars'] );
	}
}

function che_wp_enqueue_scripts_theme(): void {

    /**
     * Register js scripts
     */
	foreach ( che_general_config( 'wp_enqueue_script' ) as $args ) {
		$args = wp_parse_args( $args, [
			'is_editor'  => false,
			'is_website' => false,
		] );

		if ( ( is_admin() && $args['is_editor'] ) || ( ! is_admin() && $args['is_website'] ) ) {
			che_wp_enqueue_script( $args );
		}
	}

    /**
     * Load css styles
     */
	foreach ( che_general_config( 'wp_enqueue_style' ) as $args ) {
		$args = wp_parse_args( $args, array(
			'is_handle'  => '',
			'src'        => '',
			'deps'       => array(),
			'ver'        => CACHE_TIME,
			'media'      => '',
			'is_editor'  => false,
			'is_website' => false,
		) );

		if( ( is_admin() && $args['is_editor'] ) || ( ! is_admin() && $args['is_website'] ) ) {
			wp_enqueue_style( $args['handle'], $args['src'], $args['deps'], $args['ver'], $args['media'] );
		}
	}

}

/**
 * Enqueue scripts and styles.
 */
add_action( 'wp_enqueue_scripts', 'che_wp_enqueue_scripts_theme' );

/**
 * Enqueue scripts and styles in the block editor.
 */
add_action( 'enqueue_block_editor_assets', 'che_wp_enqueue_scripts_theme' );

/**
 * Enqueue scripts and styles in the admin area.
 */
add_action( 'admin_enqueue_scripts', function () {
	/**
	 * Register js scripts
	 */
	foreach ( che_general_config( 'wp_enqueue_script' ) as $value ) {
		if ( ! $value['is_admin'] ) {
			continue;
		}

		che_wp_enqueue_script( $value );
	}

	/**
	 * Load css styles
	 */
	foreach ( che_general_config( 'wp_enqueue_style' ) as $value ) {
		if ( ! $value['is_admin'] ) {
			continue;
		}

		wp_enqueue_style( $value['handle'], $value['src'], $value['deps'], $value['ver'], $value['media'] );
	}
} );

/**
 * Add combine css file in folder cache
 *
 * @return void
 */
function che_add_combine_css_file_in_folder_cache(): void {

	if (is_user_logged_in() || empty($_SERVER['REQUEST_URI']) || ! defined('CACHE_TIME')) {
		return;
	}

	$requestUri   = parse_url($_SERVER['REQUEST_URI']);
	if (false === $requestUri || !isset($requestUri['path'])) {
		return;
	}
	$path = $requestUri['path'];
	$skipExtensions = ['css', 'map', 'ico', 'js'];
	$fileExtension = pathinfo($path, PATHINFO_EXTENSION);

	if (in_array($fileExtension, $skipExtensions)) {
		return;
	}

	global $wp_styles;

	sort($wp_styles->queue);

	$queue_json = json_encode($wp_styles->queue);
	$hash_file_name = md5(is_string($queue_json) ? $queue_json : '');
	$cache_dir = THEME_CSS_CAHE_DIR;
	$combined_file_name = $hash_file_name . '.min.css';
	$combined_css_path  = $cache_dir . '/' . $combined_file_name;
	$combined_file_name_map = $hash_file_name . '.map';
	$combined_map_path      = $cache_dir . '/' . $combined_file_name_map;

	if (! is_dir($cache_dir) && ! wp_mkdir_p($cache_dir)) {
		return;
	}

	if (! isset($wp_styles) || ! ( $wp_styles instanceof WP_Styles )) {
		return;
	}

	$mapHandles = [];
	if (file_exists($combined_css_path) && file_exists($combined_map_path)) {
		$map_content = file_get_contents($combined_map_path);
		if (false !== $map_content) {
			$mapHandles = json_decode($map_content, true);
		}
		if (!$mapHandles || !is_array($mapHandles)) {
			return;
		}

		foreach ($wp_styles->queue as $handle) {
			if (!isset($wp_styles->registered[$handle])) {
				continue;
			}

			$style    = $wp_styles->registered[ $handle ];
			$ver = $style->ver;

			if (!empty($mapHandles[$handle]['prev_ver']) && $mapHandles[$handle]['prev_ver'] !== $ver) {
				if (function_exists('rocket_clean_domain')) {
					rocket_clean_domain();
				} else {
					che_clear_theme_css_cache();
				}
				break;
			}
		}
	}

	if (file_exists($combined_css_path)) {
		if (file_exists($combined_map_path)) {
			$handles = array_keys($mapHandles);
		} else {
			$handles = $wp_styles->queue;
		}
		if (! empty($handles) && is_array($handles)) {
			foreach ($handles as $handle) {
				wp_deregister_style((string)$handle);
			}
		}
	} else {
		$combined_css_content = '';
		$handle_combined      = array();
		/** @var array<int, \_WP_Dependency> $styles */
		$styles = array();

		foreach ($wp_styles->queue as $handle) {
			$style = $wp_styles->registered[ $handle ];
			/** @var int $order */
			$order = (is_string($style->src) && strpos($style->src, '/libs/') !== false) ? 0 : 1;
			$style->order = $order;
			$styles[] = $style;
		}

		usort($styles, function ($a, $b) {
			return ($a->order ?? 0) - ($b->order ?? 0);
		});

		foreach ($styles as $style) {
			$css_path = $style->src;
			$handle = $style->handle;
			$css_content = '';

			if ($handle === 'global-styles') {
				if (! empty($style->extra['after']) && is_array($style->extra['after'])) {
					$css_content .= implode("\n", $style->extra['after']);
				}

				if (! empty($style->extra['before']) && is_array($style->extra['before'])) {
					$css_content .= implode("\n", $style->extra['before']);
				}
			} elseif (! empty($css_path) && is_string($css_path) && strpos($css_path, 'http') !== 0) {
				$site_url = site_url('/');
				if (is_string($site_url)) {
					$css_path_rel = preg_replace('#^' . preg_quote($site_url, '#') . '#', '', $css_path);
					if (is_string($css_path_rel)) {
						$css_path_abs = realpath(ABSPATH . $css_path_rel);

						if (false !== $css_path_abs && file_exists($css_path_abs)) {
							$css_content_raw = file_get_contents($css_path_abs);
							if (false !== $css_content_raw) {
								$css_content = $css_content_raw;
							}
						}
					}
				}
			} elseif (! empty($css_path) && is_string($css_path)) {
				$home_url = home_url();
				if (is_string($home_url) && strpos($css_path, $home_url) !== false) {
					$local_path = str_replace($home_url, ABSPATH, $css_path);
					$local_path = str_replace('/', DIRECTORY_SEPARATOR, $local_path);

					if (file_exists($local_path)) {
						$css_content_raw = file_get_contents($local_path);
						if (false !== $css_content_raw) {
							$css_content = $css_content_raw;
						}
					}
				} else {
					$response = wp_remote_get($css_path);
					if (! is_wp_error($response)) {
						$css_content = wp_remote_retrieve_body($response);
					}
				}
			}

			if (! empty($css_content)) {
				$handle_combined[ $handle ]['ver'] = $style->ver;
				$handle_combined[ $handle ]['prev_ver'] = $style->ver;
				$combined_css_content .= che_replace_css_paths($css_content, is_string($css_path) ? $css_path : '');

				wp_deregister_style($handle);
			}
		}

		if (! empty($combined_css_content)) {
			$combined_css_content = preg_replace('/\/\*[\s\S]*?\*\//', '', $combined_css_content);
			file_put_contents($combined_map_path, json_encode($handle_combined));
			file_put_contents($combined_css_path, $combined_css_content);
		}
	}


	if (file_exists($combined_css_path)) {
		$combined_css_content = file_get_contents($combined_css_path);
		echo "<style id='theme-combined-css' type='text/css'>$combined_css_content</style>";
	}
}
add_action('wp_enqueue_scripts', 'che_add_combine_css_file_in_folder_cache', PHP_INT_MAX);

/**
 * Replace path to assets in combined file
 *
 * @param string $css_content
 * @param string $css_path
 * @return string
 */
if ( ! function_exists( 'che_replace_css_paths' ) ) {
	function che_replace_css_paths( string $css_content, string $css_path ): string {

		$css_directory = dirname( $css_path );

		return (string) preg_replace_callback( '/url\(\s*([\'"]?)(.+?)\1\s*\)/', function ( $matches ) use ( $css_directory ) {

			$url = $matches[2];
			if ( strpos( $url, '://' ) !== false || strpos( $url, 'data:' ) === 0 ) {
				return $matches[0];
			}

			$new_url = $css_directory . '/' . $url;

			return 'url("' . $new_url . '")';

		}, $css_content );
	}
}

/**
 * Clear theme css cache by get parameter
 *
 */
if (isset($_GET['clear_theme_css'])) {
    //shutdown is the very last hook, so we can't use wp_redirect here since the headers have already been sent
    add_action('shutdown', function () {
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        } else {
            che_clear_theme_css_cache();
        }
    });
    //therefore we use JS to redirect
    add_action('wp_footer', function () {
        if (isset($_SERVER["REQUEST_URI"])) {
            $url = strtok($_SERVER["REQUEST_URI"], '?');
            $home_url = home_url(is_string($url) ? $url : '');
            if (is_string($home_url)) {
                echo "<script>window.location.href='" . $home_url . "';</script>";
            }
        }
    });
}

/**
 * Clear theme css cache on WP Rocket clear cache
 */
add_action('before_rocket_clean_domain', 'che_clear_theme_css_cache');

/**
 * Clear theme css cache
 *
 * @return void
 */
if (!function_exists('che_clear_theme_css_cache')) {
    function che_clear_theme_css_cache(): void
    {
        $cache_dir = THEME_CSS_CAHE_DIR;

        if (is_dir($cache_dir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cache_dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                /** @var \SplFileInfo $fileinfo */
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $real_path = $fileinfo->getRealPath();
                if (false !== $real_path) {
                    $todo($real_path);
                }
            }
        }
    }
}
