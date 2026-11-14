<?php
/**
 * Main menu markup tweaks so wp_nav_menu() matches the header prototype.
 *
 * @package CHE
 */

/**
 * Whether the menu being rendered is the header's main menu.
 *
 * @param \stdClass|mixed $args wp_nav_menu() arguments.
 */
function che_is_main_menu( $args ): bool {
	return is_object( $args ) && isset( $args->theme_location ) && 'main' === $args->theme_location;
}

/**
 * Top-level and footer links get the caps style; parent links get the mobile submenu toggle hook.
 *
 * @param array<string, mixed> $atts  Link attributes.
 * @param \WP_Post             $item  Menu item.
 * @param \stdClass            $args  wp_nav_menu() arguments.
 * @param int                  $depth Item depth.
 *
 * @return array<string, mixed>
 */
function che_main_menu_link_attributes( $atts, $item, $args, $depth ) {
	if ( is_object( $args ) && isset( $args->theme_location ) && in_array( $args->theme_location, array( 'footer', 'legal' ), true ) ) {
		$atts['class'] = trim( ( $atts['class'] ?? '' ) . ' text-caps' );

		return $atts;
	}

	if ( ! che_is_main_menu( $args ) || 0 !== $depth ) {
		return $atts;
	}

	$classes = array( 'text-caps' );

	if ( in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$classes[]             = 'js-submenu-toggle';
		$atts['aria-expanded'] = 'false';
	}

	$atts['class'] = trim( ( $atts['class'] ?? '' ) . ' ' . implode( ' ', $classes ) );

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'che_main_menu_link_attributes', 10, 4 );

/**
 * Parent items show the chevron icon after the title.
 *
 * @param string    $title Item title.
 * @param \WP_Post  $item  Menu item.
 * @param \stdClass $args  wp_nav_menu() arguments.
 * @param int       $depth Item depth.
 */
function che_main_menu_item_title( $title, $item, $args, $depth ): string {
	if ( ! che_is_main_menu( $args ) || 0 !== $depth || ! in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		return $title;
	}

	return $title . '<svg class="icon"><use href="' . esc_url( THEME_ASSETS_URL . '/icons/sprite.svg#chevron-down' ) . '"></use></svg>';
}
add_filter( 'nav_menu_item_title', 'che_main_menu_item_title', 10, 4 );
