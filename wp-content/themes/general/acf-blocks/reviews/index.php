<?php
/**
 * Block template: Customer Reviews.
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

$title = get_field( 'title' );

$id = 'block-reviews-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$classes = 'reviews';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $title ) : ?>
			<h2 class="reviews__title h2-title" data-anim="title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<div class="reviews__slider swiper js-reviews-slider" data-anim="cards">
			<div class="swiper-wrapper">
				<?php foreach ( $items as $item ) : ?>
					<?php $rating = max( 0, min( 5, (int) ( $item['rating'] ?? 5 ) ) ); ?>
					<div class="reviews__slide swiper-slide">
						<div class="reviews__card" data-anim-item>
							<?php if ( ! empty( $item['text'] ) ) : ?>
								<p class="reviews__text"><?php echo wp_kses( nl2br( esc_html( $item['text'] ) ), array( 'br' => array() ) ); ?></p>
							<?php endif; ?>
							<div class="reviews__author">
								<?php if ( $rating ) : ?>
									<div class="reviews__stars" aria-label="<?php echo esc_attr( sprintf( '%d out of 5', $rating ) ); ?>">
										<?php for ( $star = 0; $star < $rating; $star++ ) : ?>
											<svg class="icon" data-anim-pop><use href="<?php echo esc_url( THEME_ASSETS_URL . '/icons/sprite.svg#star' ); ?>"></use></svg>
										<?php endfor; ?>
									</div>
								<?php endif; ?>
								<?php if ( ! empty( $item['name'] ) ) : ?>
									<p class="reviews__name"><?php echo esc_html( $item['name'] ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="reviews__pagination slider-dots js-reviews-pagination"></div>
	</div>
</section>
