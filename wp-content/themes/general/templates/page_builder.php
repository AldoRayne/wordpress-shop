<?php
/**
 *
 * Template Name: Page builder
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package CHE
 */
?>
<?php get_header(); ?>

<?php if ( have_rows( 'page_builder' ) ):
	while ( have_rows( 'page_builder' ) ) : the_row();
		get_template_part( 'template-parts/page-builder/section-' . get_row_layout() );
	endwhile;
endif; ?>

<?php get_footer(); ?>
