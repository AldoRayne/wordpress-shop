<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package CHE
 */

get_header();

/**
 * Defining custom posts per page
 */
global $wp_query;
$wp_query->set( 'posts_per_page', 9 );
$wp_query->query( $wp_query->query_vars );
?>

<?php
while ( have_posts() ) : the_post();
	the_content();
endwhile;

get_template_part( 'template-parts/navigation/pagination' ); ?>


<?php get_footer();
