<?php

/**
 * Get all blocks NAME from folder
 *
 * @param string $dir
 * @return array<int, string>
 */
function che_get_all_dir( string $dir ): array {
	return array_map( function ( string $dir_path ) {
		return basename( $dir_path );
	}, che_get_all_dir_path( $dir ) );
}

/**
 * Get all blocks PATH from folder
 *
 * @param string $dir
 * @return array<int, string>
 */
function che_get_all_dir_path( string $dir ): array {
	$glob = glob( $dir . '/*', GLOB_ONLYDIR );

	return is_array( $glob ) ? $glob : [];
}

/**
 * Add header and footer logos to the Customizer.
 *
 * @param \WP_Customize_Manager $wp_customize
 * @return void
 */
function che_customize_register( \WP_Customize_Manager $wp_customize ): void {
	// Add Header Logo Setting.
	$wp_customize->add_setting( 'header_logo', [
		'default'   => '',
		'transport' => 'refresh',
	] );

	// Add Header Logo Control.
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'header_logo', [
		'label'       => __( 'Additional Header Logo', 'general' ),
		'section'     => 'title_tagline', // Section: Site Identity
		'settings'    => 'header_logo',
		'description' => __( 'Upload a logo specifically for the header.', 'general' ),
		'priority'    => 8,
	] ) );

	// Add Footer Logo Setting.
	$wp_customize->add_setting( 'footer_logo', [
		'default'   => '',
		'transport' => 'refresh',
	] );

	// Add Footer Logo Control.
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'footer_logo', [
		'label'       => __( 'Footer Logo', 'general' ),
		'section'     => 'title_tagline', // Section: Site Identity
		'settings'    => 'footer_logo',
		'description' => __( 'Upload a logo specifically for the footer.', 'general' ),
		'priority'    => 8,
	] ) );
}
add_action( 'customize_register', 'che_customize_register' );