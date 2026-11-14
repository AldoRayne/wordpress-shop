<?php
$categories = [
	['name' => 'Air pods', 'count' => '(6 items)', 'img' => 'category-1.jpg'],
	['name' => 'Smart watches', 'count' => '(12 items)', 'img' => 'category-2.jpg'],
	['name' => 'Laptops', 'count' => '(3 items)', 'img' => 'category-3.jpg'],
	['name' => 'Mobile phones', 'count' => '(9 items)', 'img' => 'category-4.jpg'],
];
?>
<section class="categories">
	<div class="container">
		<h2 class="categories__title h2-title" data-anim="title">Our Categories</h2>
		<div class="categories__slider swiper js-categories-slider" data-anim="cards">
			<div class="swiper-wrapper">
				<?php // TODO: the design shows 4 categories with three pagination dots, the list repeats to fill three pages ?>
				<?php for ($page = 0; $page < 3; $page++) : ?>
					<?php foreach ($categories as $category) : ?>
						<div class="categories__slide swiper-slide">
							<a class="categories__item" href="shop.php" data-anim-item>
								<span class="categories__img" data-anim-media="clip">
									<img src="../assets/img/<?= $category['img'] ?>" alt="<?= $category['name'] ?>" width="360" height="223" loading="lazy">
								</span>
								<span class="categories__name h3-title"><?= $category['name'] ?></span>
								<span class="categories__count"><?= $category['count'] ?></span>
							</a>
						</div>
					<?php endforeach; ?>
				<?php endfor; ?>
			</div>
		</div>
		<div class="categories__pagination slider-dots js-categories-pagination"></div>
	</div>
</section>
