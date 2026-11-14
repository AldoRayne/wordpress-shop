<?php
/**
 * Block template: Banners.
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

$id = 'block-banners-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$classes = 'banners';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
	<div class="container banners__inner" data-anim="curtain">
		<?php foreach ( $items as $item ) : ?>
			<div class="banners__item" data-anim-item>
				<?php if ( ! empty( $item['image'] ) ) : ?>
					<picture class="banners__img" data-anim-media>
						<?php echo wp_get_attachment_image( $item['image'], 'full', false, array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
					</picture>
				<?php endif; ?>
				<div class="banners__content" data-anim-text>
					<?php if ( ! empty( $item['title'] ) ) : ?>
						<h2 class="banners__title h1-title"><?php echo esc_html( $item['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $item['text'] ) ) : ?>
						<p class="banners__text text-lead"><?php echo esc_html( $item['text'] ); ?></p>
					<?php endif; ?>
					<?php echo che_display_acf_link( $item['button'] ?? false, array( 'class' => 'banners__btn btn btn_light' ) ) ?: ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
