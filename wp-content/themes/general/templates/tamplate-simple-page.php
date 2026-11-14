<?php
/**
 *
 * Template Name: Simple Page
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package CHE
 */

get_header();

while ( have_posts() ) : the_post(); ?>
	<section class="simple-page">
		<div class="container">
			<div class="simple-page__title">
				<h1 class="h1-title">
					<?php the_title(); ?>
				</h1>
			</div>
			<div class="simple-page__content">
				<?php the_content(); ?>
			</div>
		</div>
	</section>
<?php endwhile; // End of the loop

get_footer();
