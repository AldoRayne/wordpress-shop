<!-- footer -->
<footer class="footer">
	<div class="container">
		<div class="footer__insta">
			<h2 class="footer__insta-title h2-title">Shop our insta</h2>
			<div class="footer__insta-grid">
				<?php for ($i = 1; $i <= 5; $i++) : ?>
					<a class="footer__insta-item" href="#" aria-label="Instagram post <?= $i ?>">
						<img src="../assets/img/insta-<?= $i ?>.jpg" alt="" width="282" height="282" loading="lazy">
					</a>
				<?php endfor; ?>
			</div>
		</div>
		<div class="footer__main">
			<div class="footer__company">
				<a class="footer__logo" href="home.php"><span>Basic</span>Store<span>.</span></a>
				<p class="footer__about">Nisi, purus vitae, ultrices nunc. Sit ac sit suscipit hendrerit. Gravida massa volutpat aenean odio erat nullam fringilla.</p>
				<div class="footer__social">
					<a class="footer__social-link" href="#" aria-label="Facebook"><svg class="footer__social-icon footer__social-icon_facebook icon"><use href="../assets/icons/sprite.svg#facebook"></use></svg></a>
					<a class="footer__social-link" href="#" aria-label="Instagram"><svg class="footer__social-icon footer__social-icon_instagram icon"><use href="../assets/icons/sprite.svg#instagram"></use></svg></a>
					<a class="footer__social-link" href="#" aria-label="Twitter"><svg class="footer__social-icon footer__social-icon_twitter icon"><use href="../assets/icons/sprite.svg#twitter"></use></svg></a>
					<a class="footer__social-link" href="#" aria-label="LinkedIn"><svg class="footer__social-icon footer__social-icon_linkedin icon"><use href="../assets/icons/sprite.svg#linkedin"></use></svg></a>
					<a class="footer__social-link" href="#" aria-label="YouTube"><svg class="footer__social-icon footer__social-icon_youtube icon"><use href="../assets/icons/sprite.svg#youtube"></use></svg></a>
				</div>
			</div>
			<div class="footer__col">
				<p class="footer__heading h3-title">Quick links</p>
				<ul class="footer__links">
					<li><a class="text-caps" href="home.php">Home</a></li>
					<li><a class="text-caps" href="about.php">About</a></li>
					<li><a class="text-caps" href="shop.php">Shop</a></li>
					<li><a class="text-caps" href="blog.php">Blogs</a></li>
					<li><a class="text-caps" href="contact.php">Contact</a></li>
				</ul>
			</div>
			<div class="footer__col">
				<p class="footer__heading h3-title">Help & info</p>
				<ul class="footer__links">
					<li><a class="text-caps" href="#">Track Your Order</a></li>
					<li><a class="text-caps" href="#">Returns policies</a></li>
					<li><a class="text-caps" href="#">Shipping + Delivery</a></li>
					<li><a class="text-caps" href="contact.php">Contact Us</a></li>
					<li><a class="text-caps" href="#">Faqs</a></li>
				</ul>
			</div>
			<div class="footer__col footer__col_contact">
				<p class="footer__heading h3-title">Contact us</p>
				<p class="footer__contact">Do you have any queries or suggestions?<br><a href="mailto:yourinfo@gmail.com">yourinfo@gmail.com</a></p>
				<p class="footer__contact">If you need support? Just give us a call.<br><a href="tel:+5511122233344">+55 111 222 333 44</a></p>
			</div>
		</div>
	</div>
	<div class="footer__bottom">
		<div class="container footer__bottom-inner">
			<div class="footer__badges">
				<div class="footer__badge">
					<span>We ship with:</span>
					<svg class="footer__badge-icon footer__badge-icon_dhl icon"><use href="../assets/icons/sprite.svg#dhl"></use></svg>
					<svg class="footer__badge-icon footer__badge-icon_post icon"><use href="../assets/icons/sprite.svg#post"></use></svg>
				</div>
				<div class="footer__badge">
					<span>Payment options:</span>
					<svg class="footer__badge-icon footer__badge-icon_visa icon"><use href="../assets/icons/sprite.svg#visa"></use></svg>
					<svg class="footer__badge-icon footer__badge-icon_mastercard icon"><use href="../assets/icons/sprite.svg#mastercard"></use></svg>
					<svg class="footer__badge-icon footer__badge-icon_paypal icon"><use href="../assets/icons/sprite.svg#paypal"></use></svg>
				</div>
			</div>
			<p class="footer__copy">© Copyright 2024 BasicStore. Design by TemplatesJungle</p>
		</div>
	</div>
</footer>
<!-- end footer -->
