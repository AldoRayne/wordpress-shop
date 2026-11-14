<?php
$reviews = [
	['name' => 'Emma Chamberlin', 'text' => '“Tempus oncu enim pellen tesque este pretium in neque, elit morbi sagittis lorem habi mattis Pellen tesque pretium feugiat vel morbi suspen dise sagittis lorem habi.”'],
	['name' => 'Thomas John', 'text' => '“Tempus oncu enim pellen tesque este pretium in neque, elit morbi sagittis lorem habi mattis Pellen tesque pretium feugiat vel morbi suspen dise sagittis lorem habi.”'],
	['name' => 'Kevin Bryan', 'text' => '“Tempus oncu enim pellen tesque este pretium in neque, elit morbi sagittis lorem habi mattis Pellen tesque pretium feugiat vel morbi suspen dise sagittis lorem habi.”'],
];
?>
<section class="reviews">
	<div class="container">
		<h2 class="reviews__title h2-title">Our customer’s review</h2>
		<div class="reviews__slider swiper js-reviews-slider">
			<div class="swiper-wrapper">
				<?php // TODO: the design shows 3 reviews with three pagination dots, the list repeats to fill three pages ?>
				<?php for ($page = 0; $page < 3; $page++) : ?>
					<?php foreach ($reviews as $review) : ?>
						<div class="reviews__slide swiper-slide">
							<div class="reviews__card">
								<p class="reviews__text"><?= $review['text'] ?></p>
								<div class="reviews__author">
									<div class="reviews__stars" aria-label="5 out of 5">
										<?php for ($star = 0; $star < 5; $star++) : ?>
											<svg class="icon"><use href="../assets/icons/sprite.svg#star"></use></svg>
										<?php endfor; ?>
									</div>
									<p class="reviews__name"><?= $review['name'] ?></p>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endfor; ?>
			</div>
		</div>
		<div class="reviews__pagination slider-dots js-reviews-pagination"></div>
	</div>
</section>
