<?php
/**
 * Block template: Best Selling Items.
 *
 * @var array<string, mixed> $block      The block settings and attributes.
 * @var string               $content    The block inner HTML (empty).
 * @var bool                 $is_preview True during AJAX preview.
 * @var int|string           $post_id    The post ID this block is saved to.
 */

if ( che_show_preview_image( $block ) ) {
	return;
}

$products = get_field( 'products' );
if ( empty( $products ) || ! is_array( $products ) ) {
	return;
}

$title  = get_field( 'title' );
$button = get_field( 'button' );

$id = 'block-bestsellers-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$classes = 'bestsellers';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $title ) : ?>
			<h2 class="bestsellers__title h2-title" data-anim="title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<div class="bestsellers__grid" data-anim="cards">
			<?php foreach ( $products as $product ) : ?>
				<?php get_template_part( 'template-parts/partials/product-card', null, $product ); ?>
			<?php endforeach; ?>
		</div>
		<?php if ( ! empty( $button['url'] ) ) : ?>
			<div class="bestsellers__more">
				<?php echo che_display_acf_link( $button, array( 'class' => 'btn' ) ) ?: ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
	</div>
</section>
