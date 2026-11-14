<?php
/**
 * Post card element (frontend/components/elements/_post-card.php), used inside The Loop.
 */

$post_category = che_general_get_primary_category( (int) get_the_ID(), 'category', false );
$category_name = ! empty( $post_category['primary_category'] ) ? $post_category['primary_category']->name : '';
?>
<a class="post-card" href="<?php the_permalink(); ?>" data-anim-item>
	<?php if ( has_post_thumbnail() ) : ?>
		<span class="post-card__img" data-anim-media="clip-zoom">
			<?php the_post_thumbnail( 'full', array( 'loading' => 'lazy' ) ); ?>
		</span>
	<?php endif; ?>
	<span class="post-card__body">
		<span class="post-card__meta"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?><?php if ( $category_name ) : ?>&nbsp; - &nbsp;<?php echo esc_html( $category_name ); ?><?php endif; ?></span>
		<span class="post-card__title h3-title"><?php the_title(); ?></span>
	</span>
</a>
