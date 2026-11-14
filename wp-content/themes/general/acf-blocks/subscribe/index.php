<?php
/**
 * Block template: Subscribe.
 *
 * @var array<string, mixed> $block      The block settings and attributes.
 * @var string               $content    The block inner HTML (empty).
 * @var bool                 $is_preview True during AJAX preview.
 * @var int|string           $post_id    The post ID this block is saved to.
 */

if ( che_show_preview_image( $block ) ) {
	return;
}

$image       = get_field( 'image' );
$title       = get_field( 'title' );
$text        = get_field( 'text' );
$placeholder = get_field( 'placeholder' );
$button_text = get_field( 'button_text' );

$id = 'block-subscribe-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$classes = 'subscribe';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<div class="subscribe__inner" data-anim="parallax">
			<?php if ( $image ) : ?>
				<picture class="subscribe__bg" data-anim-media>
					<?php echo wp_get_attachment_image( $image, 'full', false, array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
				</picture>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h2 class="subscribe__title h2-title" data-anim-text><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( $text ) : ?>
				<p class="subscribe__text text-lead" data-anim-text><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
			<form class="subscribe__form" action="#" method="post" data-anim-text>
				<input class="subscribe__input" type="email" name="email" placeholder="<?php echo esc_attr( $placeholder ?: '' ); ?>" aria-label="<?php esc_attr_e( 'Email', 'general' ); ?>" required>
				<button class="subscribe__btn btn btn_square" type="submit"><?php echo esc_html( $button_text ?: __( 'Subscribe', 'general' ) ); ?></button>
			</form>
		</div>
	</div>
</section>
