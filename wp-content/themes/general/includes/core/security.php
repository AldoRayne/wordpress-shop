<?php
/**
 * Improving secure headers
 */
// header( 'X-Frame-Options: SAMEORIGIN' );
// header( 'X-XSS-Protection: 1; mode=block' );
// header( 'X-Content-Type-Options: nosniff' );
// header( 'Strict-Transport-Security:max-age=31536000; includeSubdomains; preload' );

/**
 * Hide WP version strings from scripts and styles
 *
 * @param string $src
 * @return string $src
 * @filter script_loader_src
 * @filter style_loader_src
 */
function che_general_remove_wp_version_strings( $src ): string {
	global $wp_version;
	if(!empty($src)){
		$parse_url = parse_url( $src, PHP_URL_QUERY );
		if(is_string($parse_url) && !empty($parse_url)){
			parse_str( $parse_url, $query );
			if ( ! empty( $query['ver'] ) && $query['ver'] === $wp_version ) {
				$src = (string) remove_query_arg( 'ver', $src );
			}
		}
	}

	return $src;
}

add_filter( 'script_loader_src', 'che_general_remove_wp_version_strings' );
add_filter( 'style_loader_src', 'che_general_remove_wp_version_strings' );

/**
 * Hide WP version strings from generator meta tag
 *
 * @return string
 */
add_filter( 'the_generator', function () : string {
	return '';
} );

/**
 * Hide WP version strings from generator meta tag
 *
 * @return string
 */
add_filter( 'login_errors', function () : string {
	return __( 'Something is wrong!', 'general' );
} );

/**
 * Hides updates messages for a specific version (2.6.11) of the WP Migrate plugin
 */
add_filter( 'site_transient_update_plugins', function ( $transient ) {

	// Plugin slug and version
	$plugin_slug    = 'wp-migrate-db-pro/wp-migrate-db-pro.php';
	$plugin_version = '2.6.11';

	if ( ! isset( $transient->response[ $plugin_slug ] ) || ! isset( $transient->checked[ $plugin_slug ] ) ) {
		return $transient;
	}

	if ( $transient->checked[ $plugin_slug ] == $plugin_version ) {
		unset( $transient->response[ $plugin_slug ] );
	}

	return $transient;
});

/**
 * Hide update notifications in the WP Migrate plugin itself
 */
add_filter('wpmdb_notification_strings', function ($notifications) {

	if (isset($notifications['mdb_update-notice'])) {
		$updateNotice = $notifications['mdb_update-notice']['message'];

		// Check if the message contains the specified version.
		if ( str_contains( $updateNotice, 'currently have 2.6.11' ) ) {
			unset($notifications['mdb_update-notice']);
		}
	}

	return $notifications;
}, 20);



