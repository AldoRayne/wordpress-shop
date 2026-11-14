<section class="hero" data-anim="hero">
	<div class="container">
		<div class="hero__slider swiper js-hero-slider">
			<div class="swiper-wrapper">
				<?php // TODO: the design has one slide image for three pagination dots, slides 2-3 repeat slide 1 ?>
				<?php for ($i = 0; $i < 3; $i++) : ?>
					<div class="hero__slide swiper-slide">
						<picture class="hero__img" data-anim-hero="media">
							<img src="../assets/img/hero-1.jpg" alt="iPhone 15 series" width="1530" height="744" <?= $i ? 'loading="lazy"' : 'data-anim-hero="img"' ?>>
						</picture>
						<div class="hero__content"<?= $i ? '' : ' data-anim-hero="content"' ?>>
							<<?= $i ? 'h2' : 'h1' ?> class="hero__title h1-title"<?= $i ? '' : ' data-anim-hero="title"' ?>>Iphone 15 series</<?= $i ? 'h2' : 'h1' ?>>
							<p class="hero__subtitle text-lead"<?= $i ? '' : ' data-anim-hero="text"' ?>>Limited stocks available. Grab it now!</p>
							<a class="hero__btn btn btn_light" href="shop.php"<?= $i ? '' : ' data-anim-hero="text"' ?>>Shop collection</a>
						</div>
					</div>
				<?php endfor; ?>
			</div>
			<div class="hero__pagination slider-dots slider-dots_light js-hero-pagination"></div>
		</div>
	</div>
</section>
