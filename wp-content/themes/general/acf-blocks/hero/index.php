<?php
/**
 * Block template: Hero Slider.
 *
 * @var array<string, mixed> $block      The block settings and attributes.
 * @var string               $content    The block inner HTML (empty).
 * @var bool                 $is_preview True during AJAX preview.
 * @var int|string           $post_id    The post ID this block is saved to.
 */

if ( che_show_preview_image( $block ) ) {
	return;
}

$slides = get_field( 'slides' );
if ( empty( $slides ) || ! is_array( $slides ) ) {
	return;
}

$id = 'block-hero-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$classes = 'hero';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>" data-anim="hero">
	<div class="container">
		<div class="hero__slider swiper js-hero-slider">
			<div class="swiper-wrapper">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<?php
					$is_first = 0 === $index;
					$heading  = $is_first ? 'h1' : 'h2';
					?>
					<div class="hero__slide swiper-slide">
						<?php if ( ! empty( $slide['image'] ) ) : ?>
							<picture class="hero__img" data-anim-hero="media">
								<?php
								echo wp_get_attachment_image(
									$slide['image'],
									'full',
									false,
									$is_first
										? array( 'data-anim-hero' => 'img', 'loading' => 'eager', 'fetchpriority' => 'high' )
										: array( 'loading' => 'lazy' )
								);
								?>
							</picture>
						<?php endif; ?>
						<div class="hero__content"<?php echo $is_first ? ' data-anim-hero="content"' : ''; ?>>
							<?php if ( ! empty( $slide['title'] ) ) : ?>
								<<?php echo $heading; ?> class="hero__title h1-title"<?php echo $is_first ? ' data-anim-hero="title"' : ''; ?>><?php echo esc_html( $slide['title'] ); ?></<?php echo $heading; ?>>
							<?php endif; ?>
							<?php if ( ! empty( $slide['subtitle'] ) ) : ?>
								<p class="hero__subtitle text-lead"<?php echo $is_first ? ' data-anim-hero="text"' : ''; ?>><?php echo esc_html( $slide['subtitle'] ); ?></p>
							<?php endif; ?>
							<?php
							$button_attr = array( 'class' => 'hero__btn btn btn_light' );
							if ( $is_first ) {
								$button_attr['data-anim-hero'] = 'text';
							}
							echo che_display_acf_link( $slide['button'] ?? false, $button_attr ) ?: ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the helper.
							?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="hero__pagination slider-dots slider-dots_light js-hero-pagination"></div>
		</div>
	</div>
</section>
