<?php
/**
 * Site header: top bar, logo, main menu and icons (frontend/components/header/_header.php).
 *
 * @package CHE
 */

$phone          = get_field( 'phone_number', 'option' );
$help_text      = get_field( 'header_help_text', 'option' );
$promo_text     = get_field( 'header_promo_text', 'option' );
$promo_link     = get_field( 'header_promo_link', 'option' );
$delivery_text  = get_field( 'header_delivery_text', 'option' );
$has_top_bar    = $phone || $help_text || $promo_text || $delivery_text;
$sprite_url     = THEME_ASSETS_URL . '/icons/sprite.svg';
?>
<!-- header -->
<header class="header">
	<?php if ( $has_top_bar ) : ?>
		<div class="header__top">
			<div class="container header__top-inner">
				<p class="header__top-text text-sm">
					<?php echo esc_html( $help_text ?: '' ); ?>
					<?php if ( $phone ) : ?>
						<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
					<?php endif; ?>
				</p>
				<p class="header__top-text text-sm">
					<?php echo esc_html( $promo_text ?: '' ); ?>
					<?php echo che_display_acf_link( $promo_link, array( 'class' => 'header__top-link' ) ) ?: ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</p>
				<p class="header__top-text text-sm"><?php echo esc_html( $delivery_text ?: '' ); ?></p>
			</div>
		</div>
	<?php endif; ?>
	<div class="container header__inner">
		<a class="header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span>Basic</span>Store<span>.</span></a>
		<div class="header__right">
			<nav class="header__nav js-header-nav">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'main',
						'container'      => false,
						'menu_class'     => 'header__menu',
						'menu_id'        => '',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
			<div class="header__icons">
				<a class="header__icon" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'general' ); ?>"><svg class="icon"><use href="<?php echo esc_url( $sprite_url . '#search' ); ?>"></use></svg></a>
				<a class="header__icon" href="#" aria-label="<?php esc_attr_e( 'Account', 'general' ); ?>"><svg class="icon"><use href="<?php echo esc_url( $sprite_url . '#user' ); ?>"></use></svg></a>
				<a class="header__icon" href="#" aria-label="<?php esc_attr_e( 'Wishlist', 'general' ); ?>"><svg class="icon"><use href="<?php echo esc_url( $sprite_url . '#heart' ); ?>"></use></svg></a>
				<a class="header__icon" href="#" aria-label="<?php esc_attr_e( 'Cart', 'general' ); ?>"><svg class="icon"><use href="<?php echo esc_url( $sprite_url . '#cart' ); ?>"></use></svg></a>
			</div>
			<button class="header__burger js-hamburger" type="button" aria-label="<?php esc_attr_e( 'Menu', 'general' ); ?>">
				<span></span>
				<span></span>
				<span></span>
			</button>
		</div>
	</div>
</header>
<!-- end header -->
