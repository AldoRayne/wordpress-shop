<?php
/**
 * Template part for displaying the main menu on the main section.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package CHE
 */
?>

<?php
wp_nav_menu(
    [
        'menu'           => 'main',
        'theme_location' => 'main',
        'container'      => false,
        'menu_id'        => false,
        'menu_class'     => 'navbar-nav ml-auto',
        'depth'          => 2,
        'fallback_cb'    => 'bs4Navwalker::fallback',
        'walker'         => new bs4Navwalker(),
    ]
);
?>
