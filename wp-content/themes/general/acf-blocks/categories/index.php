<?php
/**
 * Block template: Categories Slider.
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

$id = 'block-categories-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$classes = 'categories';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $title ) : ?>
			<h2 class="categories__title h2-title" data-anim="title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<div class="categories__slider swiper js-categories-slider" data-anim="cards">
			<div class="swiper-wrapper">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$link = ! empty( $item['link']['url'] ) ? $item['link']['url'] : '';
					$tag  = $link ? 'a' : 'div';
					?>
					<div class="categories__slide swiper-slide">
						<<?php echo $tag; ?> class="categories__item"<?php echo $link ? ' href="' . esc_url( $link ) . '"' : ''; ?> data-anim-item>
							<?php if ( ! empty( $item['image'] ) ) : ?>
								<span class="categories__img" data-anim-media="clip">
									<?php echo wp_get_attachment_image( $item['image'], 'full', false, array( 'loading' => 'lazy' ) ); ?>
								</span>
							<?php endif; ?>
							<?php if ( ! empty( $item['name'] ) ) : ?>
								<span class="categories__name h3-title"><?php echo esc_html( $item['name'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $item['count'] ) ) : ?>
								<span class="categories__count"><?php echo esc_html( $item['count'] ); ?></span>
							<?php endif; ?>
						</<?php echo $tag; ?>>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="categories__pagination slider-dots js-categories-pagination"></div>
	</div>
</section>
