<?php
/**
 * Block template: Features.
 *
 * @var array<string, mixed> $block      The block settings and attributes.
 * @var string               $content    The block inner HTML (empty).
 * @var bool                 $is_preview True during AJAX preview.
 * @var int|string           $post_id    The post ID this block is saved to.
 */

if ( che_show_preview_image( $block ) ) {
	return;
}

$items = get_field( 'items' );
if ( empty( $items ) || ! is_array( $items ) ) {
	return;
}

$id = 'block-features-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$classes = 'features';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
	<div class="container features__inner" data-anim="cards">
		<?php foreach ( $items as $item ) : ?>
			<div class="features__item" data-anim-item>
				<?php if ( ! empty( $item['icon'] ) ) : ?>
					<svg class="features__icon icon" data-anim-pop><use href="<?php echo esc_url( THEME_ASSETS_URL . '/icons/sprite.svg#' . $item['icon'] ); ?>"></use></svg>
				<?php endif; ?>
				<?php if ( ! empty( $item['title'] ) ) : ?>
					<h3 class="features__title h3-title"><?php echo esc_html( $item['title'] ); ?></h3>
				<?php endif; ?>
				<?php if ( ! empty( $item['text'] ) ) : ?>
					<p class="features__text"><?php echo wp_kses( nl2br( esc_html( $item['text'] ) ), array( 'br' => array() ) ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
