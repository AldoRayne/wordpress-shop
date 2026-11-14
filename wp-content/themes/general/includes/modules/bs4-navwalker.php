<?php

/**
 * Class Name: bs4Navwalker
 * GitHub URI: https://github.com/dupkey/bs4navwalker
 * Description: A custom WordPress nav walker class for Bootstrap 4 nav menus in a custom theme using the WordPress built in menu manager.
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
class bs4Navwalker extends Walker_Nav_Menu {
	/**
	 * Starts the list before the elements are added.
	 *
	 * @since 3.0.0
	 *
	 * @see   Walker::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param \stdClass $args   An object of wp_nav_menu() arguments.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<div class=\"dropdown-menu\">\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @since 3.0.0
	 *
	 * @see   Walker::end_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param \stdClass $args   An object of wp_nav_menu() arguments.
	 * @return void
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent</div>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @since 3.0.0
	 *
	 * @see   Walker::start_el()
	 *
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param array $args An array of arguments. @see wp_nav_menu()
	 * @param int $id Current item ID.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 */
	/**
	 * Start the element output.
	 *
	 * @since 3.0.0
	 *
	 * @see   Walker::start_el()
	 *
	 * @param string   $output Passed by reference. Used to append additional content.
	 * @param \WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param \stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ): void {
		$indent    = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		$classes   = empty( $item->classes ) ? [] : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;
		/**
		 * Filter the CSS class(es) applied to a menu item's list item element.
		 *
		 * @param array $classes The CSS classes that are applied to the menu item's `<li>` element.
		 * @param \WP_Post $item The current menu item.
		 * @param \stdClass $args An array of {@see wp_nav_menu()} arguments.
		 * @param int $depth Depth of menu item. Used for padding.
		 *
		 * @since 3.0.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 */
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );

		if ( in_array( 'menu-item-has-children', $classes ) ) {
			$class_names .= ' dropdown';
		}
		if ( in_array( 'current-menu-item', $classes ) ) {
			$class_names .= ' active';
		}
		//
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
		// print_r($class_names);
		/**
		 * Filter the ID applied to a menu item's list item element.
		 *
		 * @param string $menu_id The ID that is applied to the menu item's `<li>` element.
		 * @param \WP_Post $item The current menu item.
		 * @param \stdClass $args An array of {@see wp_nav_menu()} arguments.
		 * @param int $depth Depth of menu item. Used for padding.
		 *
		 * @since 3.0.1
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 */
		$id_str = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		if ( ! is_string( $id_str ) ) {
			$id_str = '';
		}
		$id_str = $id_str ? ' id="' . esc_attr( $id_str ) . '"' : '';
		// New
		if ( $depth === 0 ) {
			$output .= $indent . '<li' . $id_str . $class_names . '>';
		}
		//
		// $output .= $indent . '<li' . $id . $class_names .'>';
		$atts           = [];
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']   = ! empty( $item->url ) ? $item->url : '';
		// New
		if ( $depth === 0 ) {
			$atts['class'] = 'nav-link';
		}
		if ( $depth === 0 && in_array( 'menu-item-has-children', $classes ) ) {
			$atts['class']       .= ' dropdown-toggle';
			$atts['data-toggle'] = 'dropdown';
		}
		if ( $depth > 0 ) {
			$manual_class   = array_values( $classes )[0] . ' ' . 'dropdown-item';
			$atts ['class'] = $manual_class;
		}
		$item_classes = empty( $item->classes ) ? [] : (array) $item->classes;
		if ( isset( $atts['class'] ) && in_array( 'current-menu-item', $item_classes ) ) {
			$atts['class'] .= ' active';
		}
		// print_r($item);
		//
		/**
		 * Filter the HTML attributes applied to a menu item's anchor element.
		 *
		 * @since 3.6.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array $atts {
		 *                       The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
		 *
		 * @type string $title Title attribute.
		 * @type string $target Target attribute.
		 * @type string $rel The rel attribute.
		 * @type string $href The href attribute.
		 * }
		 *
		 * @param \WP_Post $item The current menu item.
		 * @param \stdClass $args An array of {@see wp_nav_menu()} arguments.
		 * @param int $depth Depth of menu item. Used for padding.
		 */
		$atts       = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );
		$attributes = '';
		if ( is_array( $atts ) ) {
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value      = ( 'href' === $attr ) ? esc_url( (string) $value ) : esc_attr( (string) $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}
		}
		$item_output = ( $args && isset( $args->before ) ) ? (string) $args->before : '';
		// New
		/*
		if ($depth === 0 && in_array('menu-item-has-children', $classes)) {
			$item_output .= '<a class="nav-link dropdown-toggle"' . $attributes .'data-toggle="dropdown">';
		} elseif ($depth === 0) {
			$item_output .= '<a class="nav-link"' . $attributes .'>';
		} else {
			$item_output .= '<a class="dropdown-item"' . $attributes .'>';
		}
		*/
		//
		$item_output .= '<a' . $attributes . '>';
		/** This filter is documented in wp-includes/post-template.php */
		$link_before = ( $args && isset( $args->link_before ) ) ? (string) $args->link_before : '';
		$link_after  = ( $args && isset( $args->link_after ) ) ? (string) $args->link_after : '';

		$item_output .= $link_before . apply_filters( 'the_title', $item->post_title, $item->ID ) . $link_after;

		/** if toggle menu */
		// if ( strpos( $attributes, 'dropdown-toggle' ) ):
		//     $item_output .= che_general_get_asset( 'svgs/misc/arrow_down.svg' );
		// endif;

		$item_output .= '</a>';
		$item_output .= ( $args && isset( $args->after ) ) ? (string) $args->after : '';
		/**
		 * Filter a menu item's starting output.
		 *
		 * The menu item's starting output only includes `$args->before`, the opening `<a>`,
		 * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
		 * no filter for modifying the opening and closing `<li>` for a menu item.
		 *
		 * @param string $item_output The menu item's starting HTML output.
		 * @param \WP_Post $item Menu item data object.
		 * @param int $depth Depth of menu item. Used for padding.
		 * @param \stdClass $args An array of {@see wp_nav_menu()} arguments.
		 *
		 * @since 3.0.0
		 *
		 */
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @since 3.0.0
	 *
	 * @see   Walker::end_el()
	 *
	 * @param string   $output Passed by reference. Used to append additional content.
	 * @param \WP_Post  $item   Page data object. Not used.
	 * @param int      $depth  Depth of page. Not Used.
	 * @param \stdClass $args   An object of wp_nav_menu() arguments.
	 * @return void
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ): void {
		if ( $depth === 0 ) {
			$output .= "</li>\n";
		}
	}
}
