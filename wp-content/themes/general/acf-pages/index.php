<?php
/**
 * @see https://www.advancedcustomfields.com/resources/whats-new-with-acf-blocks-in-acf-6/
 */

/**
 * Add custom template pages
 */
add_filter( 'theme_templates', function ( $post_templates, $wp_theme, $post, $post_type ) {

	if($post_type == 'page'){
		$pages = che_get_all_dir_path( THEME_ACF_PAGES );

		foreach ($pages as $page){

			$template = $page . '/template.php';

			$template_name = 'Template Name';
			$template_content = file_get_contents( $template );
			if ( false === $template_content || ! preg_match( '|' . $template_name . ':(.*)$|mi', $template_content, $header ) ) {
				continue;
			}

			$post_templates[ 'acf-pages/' . basename( $page ) . '/template.php' ] = _cleanup_header_comment( $header[1] );
		}
	}

	return $post_templates;

}, 10, 4 );
