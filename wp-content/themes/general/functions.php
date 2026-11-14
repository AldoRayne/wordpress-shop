<?php
/**
 * Start-theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package CHE
 */

define( 'THEME_NAME', 'general' );
define( 'THEME_DIR', __DIR__ );
define( 'THEME_ASSETS_DIR', THEME_DIR . '/frontend/assets' );
define( 'THEME_FRONTEND_DIR', THEME_DIR . '/frontend' );
define( 'THEME_ACF_BLOCKS', THEME_DIR . '/acf-blocks' );
define( 'THEME_ACF_PAGES', THEME_DIR . '/acf-pages' );
define( 'THEME_ACF_OPTION', THEME_DIR . '/acf-options' );
define( 'THEME_INCLUDES_DIR', THEME_DIR . '/includes' );
define( 'THEME_MODULES_DIR', THEME_INCLUDES_DIR . '/modules' );
define( 'THEME_CORE_DIR', THEME_INCLUDES_DIR . '/core' );
define( 'THEME_CSS_CAHE_DIR', WP_CONTENT_DIR . '/cache/theme-css');
define( 'THEME_CSS_CAHE_URL', WP_CONTENT_URL . '/cache/theme-css/');
define( 'THEME_CACHING_CSS', true);

define( 'THEME_URL', get_stylesheet_directory_uri() );
define( 'THEME_ASSETS_URL', THEME_URL . '/frontend/assets' );
define( 'CACHE_TIME', ( defined( 'WP_CACHE' ) ? '0.0.1' : date( 'YmdHis' ) ) );

/**
 * Composer autoload
 */
$autoloadFile = THEME_DIR . '/vendor/autoload.php';

if (file_exists($autoloadFile)) {
	require_once $autoloadFile;
}

/**
 * Load theme configs
 */
require_once THEME_INCLUDES_DIR . '/config/config.php';

/**
 * Load ACF settings
 */
require_once THEME_INCLUDES_DIR . '/acf.php';

/**
 * Load global core files.
 */
require_once THEME_CORE_DIR . '/GENERAL_CLI.php';
require_once THEME_CORE_DIR . '/CONTENT_CLI.php';
require_once THEME_CORE_DIR . '/security.php';
require_once THEME_CORE_DIR . '/functions.php';
require_once THEME_CORE_DIR . '/setup-theme.php';
require_once THEME_CORE_DIR . '/WP_Logger.php';

/**
 * Load theme helpers
 */
require_once THEME_INCLUDES_DIR . '/helpers.php';

/**
 * Load style and scripts
 */
require_once THEME_INCLUDES_DIR . '/config/styles-and-script.php';

/**
 * Load global modules.
 */
require_once THEME_MODULES_DIR . '/bs4-navwalker.php';
require_once THEME_MODULES_DIR . '/search.php';
require_once THEME_MODULES_DIR . '/widgets.php';
require_once THEME_MODULES_DIR . '/shortcodes.php';
require_once THEME_MODULES_DIR . '/PreviewACF.php';

/**
 * Load ACF item
 */
require_once THEME_ACF_BLOCKS . '/index.php';
require_once THEME_ACF_PAGES . '/index.php';