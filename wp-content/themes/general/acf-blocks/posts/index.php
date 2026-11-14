<?php
/**
 * Block template: Latest Posts.
 *
 * @var array<string, mixed> $block      The block settings and attributes.
 * @var string               $content    The block inner HTML (empty).
 * @var bool                 $is_preview True during AJAX preview.
 * @var int|string           $post_id    The post ID this block is saved to.
 */

if ( che_show_preview_image( $block ) ) {
	return;
}

$count = (int) get_field( 'count' );
$query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $count > 0 ? $count : 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $query->have_posts() ) {
	return;
}

$title  = get_field( 'title' );
$button = get_field( 'button' );

$id = 'block-posts-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$classes = 'posts';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $title ) : ?>
			<h2 class="posts__title h2-title" data-anim="title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<div class="posts__grid" data-anim="cards">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/partials/post-card' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<?php if ( ! empty( $button['url'] ) ) : ?>
			<div class="posts__more">
				<?php echo che_display_acf_link( $button, array( 'class' => 'btn' ) ) ?: ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
	</div>
</section>
