<?php

$icon = $args['icon'] ?? '';

if ( empty( $icon ) ) {
	return;
}

$classes = 'icon_' . esc_attr( $icon );

if ( ! empty( $args['class'] ) ) {
	$classes .= ' ' . esc_attr( $args['class'] );
}
?>

<svg class="icon <?php echo $classes; ?>">
	<use href="<?php echo esc_url( THEME_ASSETS_URL . '/icons/sprite.svg?v=1.0#' . $icon ); ?>"></use>
</svg>