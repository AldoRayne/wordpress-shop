<?php
/**
 * Product card element (frontend/components/elements/_product-card.php).
 *
 * @var array<string, mixed> $args Product row: image, name, price, link.
 */

$name  = $args['name'] ?? '';
$price = $args['price'] ?? '';
$image = $args['image'] ?? 0;
$url   = ! empty( $args['link']['url'] ) ? $args['link']['url'] : '';
$tag   = $url ? 'a' : 'div';
?>
<<?php echo $tag; ?> class="product-card"<?php echo $url ? ' href="' . esc_url( $url ) . '"' : ''; ?> data-anim-item>
	<?php if ( $image ) : ?>
		<span class="product-card__img" data-anim-media="zoom">
			<?php echo wp_get_attachment_image( (int) $image, 'full', false, array( 'loading' => 'lazy' ) ); ?>
		</span>
	<?php endif; ?>
	<span class="product-card__body">
		<?php if ( $name ) : ?>
			<span class="product-card__name h3-title"><?php echo esc_html( $name ); ?></span>
		<?php endif; ?>
		<?php if ( $price ) : ?>
			<span class="product-card__price"><?php echo esc_html( $price ); ?></span>
		<?php endif; ?>
	</span>
</<?php echo $tag; ?>>
