<?php
/**
 * Site footer: Instagram strip, company info, menus, contacts and bottom bar
 * (frontend/components/footer/_footer.php).
 *
 * @package CHE
 */

$insta_title   = get_field( 'footer_insta_title', 'option' );
$insta_link    = get_field( 'footer_insta_link', 'option' );
$insta_images  = get_field( 'footer_insta_images', 'option' );
$about         = get_field( 'footer_about', 'option' );
$socials       = get_field( 'footer_socials', 'option' );
$contact_title = get_field( 'footer_contact_title', 'option' );
$email_text    = get_field( 'footer_email_text', 'option' );
$email         = get_field( 'footer_email', 'option' );
$phone_text    = get_field( 'footer_phone_text', 'option' );
$phone         = get_field( 'footer_phone', 'option' );
$copyrights    = get_field( 'copyrights', 'option' );
$sprite_url    = THEME_ASSETS_URL . '/icons/sprite.svg';
$footer_menus  = array( 'footer', 'legal' );
?>
<!-- footer -->
<footer class="footer">
	<div class="container">
		<?php if ( ! empty( $insta_images ) && is_array( $insta_images ) ) : ?>
			<div class="footer__insta">
				<?php if ( $insta_title ) : ?>
					<h2 class="footer__insta-title h2-title" data-anim="title"><?php echo esc_html( $insta_title ); ?></h2>
				<?php endif; ?>
				<div class="footer__insta-grid" data-anim="drift">
					<?php foreach ( $insta_images as $index => $image_id ) : ?>
						<a class="footer__insta-item" href="<?php echo esc_url( $insta_link ?: '#' ); ?>" data-anim-item aria-label="<?php echo esc_attr( sprintf( __( 'Instagram post %d', 'general' ), $index + 1 ) ); ?>"<?php echo $insta_link ? ' target="_blank" rel="noopener"' : ''; ?>>
							<?php echo wp_get_attachment_image( (int) $image_id, 'full', false, array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="footer__main" data-anim="cards">
			<div class="footer__company" data-anim-item>
				<a class="footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span>Basic</span>Store<span>.</span></a>
				<?php if ( $about ) : ?>
					<p class="footer__about"><?php echo wp_kses( nl2br( esc_html( $about ) ), array( 'br' => array() ) ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $socials ) && is_array( $socials ) ) : ?>
					<div class="footer__social">
						<?php foreach ( $socials as $social ) : ?>
							<?php
							if ( empty( $social['url'] ) || empty( $social['network'] ) ) {
								continue;
							}
							?>
							<a class="footer__social-link" href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( ucfirst( $social['network'] ) ); ?>"><svg class="footer__social-icon footer__social-icon_<?php echo esc_attr( $social['network'] ); ?> icon"><use href="<?php echo esc_url( $sprite_url . '#' . $social['network'] ); ?>"></use></svg></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php foreach ( $footer_menus as $location ) : ?>
				<?php
				if ( ! has_nav_menu( $location ) ) {
					continue;
				}
				?>
				<div class="footer__col" data-anim-item>
					<p class="footer__heading h3-title"><?php echo esc_html( wp_specialchars_decode( wp_get_nav_menu_name( $location ), ENT_QUOTES ) ); ?></p>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => $location,
							'container'      => false,
							'menu_class'     => 'footer__links',
							'menu_id'        => '',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</div>
			<?php endforeach; ?>
			<?php if ( $contact_title || $email || $phone ) : ?>
				<div class="footer__col footer__col_contact" data-anim-item>
					<?php if ( $contact_title ) : ?>
						<p class="footer__heading h3-title"><?php echo esc_html( $contact_title ); ?></p>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<p class="footer__contact"><?php echo esc_html( $email_text ?: '' ); ?><br><a href="<?php echo esc_url( 'mailto:' . antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></p>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<p class="footer__contact"><?php echo esc_html( $phone_text ?: '' ); ?><br><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="footer__bottom">
		<div class="container footer__bottom-inner">
			<div class="footer__badges">
				<div class="footer__badge">
					<span><?php esc_html_e( 'We ship with:', 'general' ); ?></span>
					<svg class="footer__badge-icon footer__badge-icon_dhl icon"><use href="<?php echo esc_url( $sprite_url . '#dhl' ); ?>"></use></svg>
					<svg class="footer__badge-icon footer__badge-icon_post icon"><use href="<?php echo esc_url( $sprite_url . '#post' ); ?>"></use></svg>
				</div>
				<div class="footer__badge">
					<span><?php esc_html_e( 'Payment options:', 'general' ); ?></span>
					<svg class="footer__badge-icon footer__badge-icon_visa icon"><use href="<?php echo esc_url( $sprite_url . '#visa' ); ?>"></use></svg>
					<svg class="footer__badge-icon footer__badge-icon_mastercard icon"><use href="<?php echo esc_url( $sprite_url . '#mastercard' ); ?>"></use></svg>
					<svg class="footer__badge-icon footer__badge-icon_paypal icon"><use href="<?php echo esc_url( $sprite_url . '#paypal' ); ?>"></use></svg>
				</div>
			</div>
			<?php if ( $copyrights ) : ?>
				<p class="footer__copy"><?php echo esc_html( str_replace( '@year', gmdate( 'Y' ), $copyrights ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</footer>
<!-- end footer -->
